<?php
declare(strict_types=1);
namespace PixelCoda\TextFlow\Service;

use PixelCoda\TextFlow\Domain\Repository\TextFlowPatternRepository;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\SingletonInterface;

/**
 * Service for optimizing text flow with dynamic hyphenation.
 * Implements Liang-Knuth algorithm for hyphenation based on language patterns.
 * Preserves HTML structure and special characters.
 */
class TextFlowService implements SingletonInterface
{
    protected TextFlowPatternRepository $patternRepository;
    protected $logger;
    protected $cacheInstance;
    protected array $patternCache = [];
    protected array $processedWordsCache = [];
    
    // Configuration constants
    protected const MIN_WORD_LENGTH = 5; // Minimum word length for hyphenation
    protected const SOFT_HYPHEN = '&shy;'; // HTML soft hyphen
    protected const MIN_LEFT_CHARS = 2; // Minimum chars left of hyphen
    protected const MIN_RIGHT_CHARS = 2; // Minimum chars right of hyphen
    protected const CACHE_IDENTIFIER = 'tx_textflow_hyphenation'; // Cache identifier

    /**
     * Constructor
     */
    public function __construct(?TextFlowPatternRepository $patternRepository = null)
    {
        $this->patternRepository = $patternRepository ?? GeneralUtility::makeInstance(TextFlowPatternRepository::class);
        $this->logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
        
        // Initialize cache
        try {
            $this->cacheInstance = GeneralUtility::makeInstance(CacheManager::class)->getCache(self::CACHE_IDENTIFIER);
        } catch (\Exception $e) {
            // Cache not available, continue without caching
            $this->logger->warning('Caching not available for TextFlow', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Hyphenates text based on language and content settings.
     * Preserves HTML tags, special characters, and case sensitivity.
     *
     * @param string $content The input text to be hyphenated
     * @param array<string, mixed> $conf Optional content record with settings
     * @return string The hyphenated text
     */
    public function hyphenate(string $content = '', array $conf = []): string
    {
        // Skip if content is empty or hyphenation is disabled
        if (empty($content) || isset($conf['enable']) && empty($conf['enable'])) {
            return $content;
        }

        // Determine language from content record or context
        $language = $this->getCurrentLanguage($conf);
        
        // Load patterns for current language
        $patterns = $this->buildPatterns($language);
        if (empty($patterns)) {
            return $content;
        }

        // Preserve HTML structure if required
        if (!empty($conf['preserveStructure'])) {
            return $this->processContentPreservingStructure($content, $patterns, $language);
        }

        // Simple text processing
        return $this->processContent($content, $patterns, $language);
    }

    /**
     * Process text parts and apply hyphenation while preserving HTML structure
     */
    protected function processContentPreservingStructure(string $content, array $patterns, string $language): string
    {
        // Suppress HTML5 parsing errors
        $previousValue = libxml_use_internal_errors(true);
        
        // Use DOMDocument to preserve HTML structure
        $dom = new \DOMDocument();
        $dom->loadHTML(mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8'), 
                        LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        
        // Process text nodes only
        $xpath = new \DOMXPath($dom);
        $textNodes = $xpath->query('//text()');
        
        if ($textNodes !== false) {
            foreach ($textNodes as $node) {
                if (trim($node->nodeValue) !== '') {
                    $node->nodeValue = $this->processWords($node->nodeValue, $patterns, $language);
                }
            }
        }
        
        // Get only the content from body tag
        $html = $dom->saveHTML();
        
        // Restore previous error handling setting
        libxml_use_internal_errors($previousValue);
        
        // Extract content from body tag if present
        if (preg_match('/<body>(.*?)<\/body>/s', $html, $matches)) {
            return $matches[1];
        }
        
        return $html;
    }

    /**
     * Process plain text content
     */
    protected function processContent(string $content, array $patterns, string $language): string
    {
        return $this->processWords($content, $patterns, $language);
    }

    /**
     * Process text by splitting into words and applying hyphenation
     */
    protected function processWords(string $text, array $patterns, string $language): string
    {
        // Split text into words and non-words (whitespace, punctuation)
        preg_match_all('/(\w+)|([^\w]+)/u', $text, $matches, PREG_SET_ORDER);
        
        $result = '';
        foreach ($matches as $match) {
            if (isset($match[1]) && !empty($match[1])) {
                // Word
                $word = $match[1];
                if (mb_strlen($word) >= self::MIN_WORD_LENGTH) {
                    $result .= $this->hyphenateWord($word, $patterns, $language);
                } else {
                    $result .= $word;
                }
            } else {
                // Non-word
                $result .= $match[0];
            }
        }
        
        return $result;
    }

    /**
     * Apply Liang-Knuth hyphenation algorithm to a single word
     */
    protected function hyphenateWord(string $word, array $patterns, string $language): string
    {
        // Use in-memory cache for repeated words
        $cacheKey = $language . '_' . mb_strtolower($word);
        if (isset($this->processedWordsCache[$cacheKey])) {
            return $this->processedWordsCache[$cacheKey];
        }
        
        // Try to get from persistent cache
        if ($this->cacheInstance && $cachedResult = $this->cacheInstance->get($cacheKey)) {
            $this->processedWordsCache[$cacheKey] = $cachedResult;
            return $cachedResult;
        }
        
        // Word pre-processing
        $lcWord = mb_strtolower($word);
        $paddedWord = '_' . $lcWord . '_'; // Add boundaries for pattern matching
        $chars = preg_split('//u', $paddedWord, -1, PREG_SPLIT_NO_EMPTY);
        $wordLength = count($chars);
        
        // Initialize points array with zeros
        $points = array_fill(0, $wordLength - 1, 0);
        
        // Apply patterns to find hyphenation points
        foreach ($patterns as $pattern) {
            $patternValue = $pattern['pattern'];
            // Skip patterns longer than word to avoid unnecessary processing
            if (mb_strlen($patternValue) > $wordLength) {
                continue;
            }
            
            // Find pattern in word
            for ($i = 0; $i <= $wordLength - mb_strlen($patternValue); $i++) {
                $substr = mb_substr($paddedWord, $i, mb_strlen($patternValue));
                if ($substr === $patternValue) {
                    // Mark hyphenation point
                    $points[$i + 1] = 1; // +1 for the boundary
                }
            }
        }
        
        // Apply constraints (don't hyphenate too close to edges)
        for ($i = 0; $i < self::MIN_LEFT_CHARS; $i++) {
            if (isset($points[$i])) {
                $points[$i] = 0;
            }
        }
        
        for ($i = $wordLength - self::MIN_RIGHT_CHARS - 1; $i < $wordLength - 1; $i++) {
            if (isset($points[$i])) {
                $points[$i] = 0;
            }
        }
        
        // Insert soft hyphens
        $hyphenated = '';
        for ($i = 1; $i < $wordLength - 1; $i++) { // Skip first and last boundary chars
            $hyphenated .= $chars[$i];
            if (isset($points[$i]) && $points[$i] === 1) {
                $hyphenated .= self::SOFT_HYPHEN;
            }
        }
        
        // Cache result
        $this->processedWordsCache[$cacheKey] = $hyphenated;
        if ($this->cacheInstance) {
            $this->cacheInstance->set($cacheKey, $hyphenated, [], 86400); // Cache for 24 hours
        }
        
        return $hyphenated;
    }

    /**
     * Get current language identifier
     */
    protected function getCurrentLanguage(array $contentRecord = []): string
    {
        if (!empty($contentRecord['enable_textflow']) && $contentRecord['enable_textflow'] !== 'all') {
            return $contentRecord['enable_textflow'];
        }

        try {
            $context = GeneralUtility::makeInstance(Context::class);
            $languageAspect = $context->getAspect('language');
            $languageId = $languageAspect->getId();

            $languageMap = [
                0 => 'de',
                1 => 'en',
                2 => 'fr',
                3 => 'es',
                4 => 'it',
                5 => 'nl',
                6 => 'pt',
                7 => 'zh',
                8 => 'ar',
                9 => 'hi'
            ];

            return $languageMap[$languageId] ?? 'de';
        } catch (\Exception $exception) {
            $this->logger->error('Error getting language', ['error' => $exception->getMessage()]);
            return 'de';
        }
    }

    /**
     * Build hyphenation patterns from repository with caching
     */
    protected function buildPatterns(string $language): array
    {
        if (isset($this->patternCache[$language])) {
            return $this->patternCache[$language];
        }
        
        // Try to get from persistent cache
        if ($this->cacheInstance) {
            $cacheIdentifier = 'patterns_' . $language;
            $cachedPatterns = $this->cacheInstance->get($cacheIdentifier);
            if ($cachedPatterns !== false) {
                $this->patternCache[$language] = $cachedPatterns;
                return $cachedPatterns;
            }
        }

        try {
            $patterns = $this->patternRepository->findByLanguage($language);
            if (!empty($patterns)) {
                $this->patternCache[$language] = $patterns;
                
                // Save to persistent cache
                if ($this->cacheInstance) {
                    $this->cacheInstance->set($cacheIdentifier, $patterns, [], 86400); // Cache for 24 hours
                }
                
                return $patterns;
            }

            $this->logger->warning('No patterns found in repository', ['language' => $language]);
            return [];
        } catch (\Exception $exception) {
            $this->logger->error('Error loading patterns', [
                'language' => $language,
                'error' => $exception->getMessage()
            ]);
            return [];
        }
    }
}
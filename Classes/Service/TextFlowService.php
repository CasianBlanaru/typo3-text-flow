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
 * Provides functionality to hyphenate text based on language-specific patterns
 * while preserving HTML structure and special characters.
 */
class TextFlowService implements SingletonInterface
{
    protected TextFlowPatternRepository $patternRepository;
    protected $logger;
    protected array $patternCache = [];
    protected const MIN_WORD_LENGTH = 5;
    protected const SOFT_HYPHEN = "\xC2\xAD"; // UTF-8 soft hyphen character

    // Default to false for production
    protected static $debugMode = false;

    // Debug level: 0 = off, 1 = text markers ("-||-"), 3 = obvious markers ("▼TRENN▼")
    protected static $debugLevel = 1;

    public function __construct(?TextFlowPatternRepository $patternRepository = null)
    {
        $this->patternRepository = $patternRepository ?? GeneralUtility::makeInstance(TextFlowPatternRepository::class);
        $this->logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);

        // Check debug parameter on each instantiation
        self::checkDebugMode();

        // Log debug mode status
        $this->logger->info('TextFlow: Service initialized', [
            'debug_mode' => self::$debugMode ? 'ACTIVE' : 'INACTIVE',
            'debug_level' => self::$debugLevel
        ]);
    }

    /**
     * Checks if debug mode should be activated
     */
    protected static function checkDebugMode(): void
    {
        // Default is always off unless explicitly activated
        self::$debugMode = false;

        // Direct access to GET parameter
        $debugParam = GeneralUtility::_GP('debug_textflow');

        if (!empty($debugParam)) {
            self::$debugMode = true;

            // Check if a specific debug level is requested
            if (is_numeric($debugParam)) {
                self::$debugLevel = (int)$debugParam;
            }
        }
    }

    /**
     * Returns if debug mode is active
     */
    public static function isDebugMode(): bool
    {
        return self::$debugMode;
    }

    /**
     * Returns current debug level
     */
    public static function getDebugLevel(): int
    {
        return self::$debugLevel;
    }

    /**
     * Set debug level manually
     */
    public static function setDebugLevel(int $level): void
    {
        self::$debugLevel = $level;
        if ($level > 0) {
            self::$debugMode = true;
        }
    }

    /**
     * Manually enables debug mode
     */
    public static function enableDebugMode(): void
    {
        self::$debugMode = true;
    }

    /**
     * Manually disables debug mode
     */
    public static function disableDebugMode(): void
    {
        self::$debugMode = false;
        self::$debugLevel = 0;
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
        // Return quickly if content is empty or whitespace
        if (empty($content) || trim($content) === '') {
            return $content;
        }

        // Check if TextFlow is explicitly disabled for this element
        $textflowEnabled = true;
        if (isset($conf['enable_textflow']) && ($conf['enable_textflow'] === 'none' || empty($conf['enable_textflow']))) {
            $textflowEnabled = false;
            // WICHTIG: Bei deaktiviertem TextFlow einfach Original-Inhalt zurückgeben
            return $content;
        }

        // Get language from configuration
        $language = $this->getCurrentLanguage($conf);

        // If TextFlow is disabled for this language, return original content
        if ($language === 'none') {
            return $content;
        }

        // Create local debug flags to avoid affecting global state
        $localDebugMode = self::$debugMode;
        $localDebugLevel = self::$debugLevel;

        // Check debug mode only if explicitly activated in configuration
        if (!empty($conf['debug']) && $conf['debug'] === true) {
            $localDebugMode = true;
        }

        // Clean input from empty paragraphs and special characters
        $content = $this->cleanContent($content);

        // Check again after cleaning
        if (empty(trim($content))) {
            return '';
        }

        // Add debug flags to configuration
        $conf['_localDebugMode'] = $localDebugMode;
        $conf['_localDebugLevel'] = $localDebugLevel;

        // Preserve existing HTML structure
        $result = !empty($conf['preserveStructure'])
            ? $this->processContentPreservingStructure($content, $conf)
            : $this->processContent($content, $conf);

        return $result;
    }

    /**
     * Clean content from unnecessary elements
     */
    protected function cleanContent(string $content): string
    {
        // Remove all HTML comments
        $content = preg_replace('/<!--(.|\s)*?-->/', '', $content);

        // Remove empty paragraphs
        $content = preg_replace('/<p>\s*(&nbsp;|\s)*\s*<\/p>/', '', $content);

        // Clean consecutive empty lines
        $content = preg_replace('/(\r\n|\r|\n){2,}/', "\n", $content);

        // Remove non-breaking spaces at beginning and end
        $content = preg_replace('/^(&nbsp;|\s)+|(&nbsp;|\s)+$/', '', $content);

        return trim($content);
    }

    /**
     * Process text parts and apply hyphenation
     */
    protected function processContentPreservingStructure(string $content, array $conf): string
    {
        // Return immediately if content is empty
        if (empty(trim($content))) {
            return '';
        }

        // Use DOMDocument to preserve HTML structure
        $dom = new \DOMDocument();

        // Use proper UTF-8 handling
        $content = mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8');

        // Suppress warnings for HTML5 elements
        $previousValue = libxml_use_internal_errors(true);

        try {
            $dom->loadHTML($content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

            // Process text nodes only
            $xpath = new \DOMXPath($dom);
            $textNodes = $xpath->query('//text()');

            foreach ($textNodes as $node) {
                if (trim($node->nodeValue) !== '') {
                    $node->nodeValue = $this->applyTextFlow($node->nodeValue, $conf);
                }
            }

            // Get only the body content without html/body tags
            $html = $dom->saveHTML();

            // Clean up the HTML output
            $html = preg_replace('/^<!DOCTYPE.+?>/', '', $html);
            $html = preg_replace('/<html.*?>/', '', $html);
            $html = preg_replace('/<\/html>/', '', $html);
            $html = preg_replace('/<body>/', '', $html);
            $html = preg_replace('/<\/body>/', '', $html);

            // Clean up empty paragraphs
            $html = preg_replace('/<p>\s*(&nbsp;|\s)*\s*<\/p>/', '', $html);

            return trim($html);
        } catch (\Exception $e) {
            // Return original content on error
            $this->logger->error('TextFlow: Error processing HTML content', [
                'error' => $e->getMessage(),
                'content_length' => strlen($content)
            ]);
            return $content;
        } finally {
            // Always reset libxml error handling
            libxml_use_internal_errors($previousValue);
        }
    }

    protected function processContent(string $content, array $conf): string
    {
        return $this->applyTextFlow($content, $conf);
    }

    protected function applyTextFlow(string $text, array $conf): string
    {
        // Split text into words while preserving whitespace
        $words = preg_split('/(\s+)/', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
        $result = [];

        foreach ($words as $index => $word) {
            // Skip whitespace and process only words
            if ($index % 2 === 0 && strlen(trim($word)) >= self::MIN_WORD_LENGTH) {
                $processed = $this->addHyphensToWord($word, $conf);
                $result[] = $processed;
            } else {
                $result[] = $word;
            }
        }

        return implode('', $result);
    }

    /**
     * Adds soft hyphens to a word
     */
    protected function addHyphensToWord(string $word, array $conf): string
    {
        // Skip words with special characters or HTML-like content
        if (preg_match('/[<>&\']/', $word) || empty(trim($word))) {
            return $word;
        }

        $wordLength = mb_strlen($word);
        if ($wordLength <= self::MIN_WORD_LENGTH) {
            return $word;
        }

        // Get local debug state from configuration
        $localDebugMode = $conf['_localDebugMode'] ?? false;
        $localDebugLevel = $conf['_localDebugLevel'] ?? 1;

        $result = '';
        for ($i = 0; $i < $wordLength; $i++) {
            $char = mb_substr($word, $i, 1);
            $result .= $char;

            // Only add soft hyphens between consonants and vowels
            if ($i > 0 && $i < $wordLength - 2 && ($i + 1) % 3 === 0) {
                $nextChar = mb_substr($word, $i + 1, 1);
                $prevChar = mb_substr($word, $i - 1, 1);

                // Only add soft hyphen if surrounding characters are letters
                if (preg_match('/[a-zA-ZäöüÄÖÜß]/', $prevChar) &&
                    preg_match('/[a-zA-ZäöüÄÖÜß]/', $nextChar)) {

                    if ($localDebugMode) {
                        switch ($localDebugLevel) {
                            case 3: // Very obvious marker
                                $result .= "▼TRENN▼";
                                break;
                            case 2: // Redirect to mode 1
                            case 1: // Default text marker
                            default:
                                $result .= "-||-";
                                break;
                        }
                    } else {
                        // Regular soft hyphen in normal mode
                        $result .= self::SOFT_HYPHEN;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Get current language identifier
     */
    protected function getCurrentLanguage(array $contentRecord = []): string
    {
        // If hyphenation is explicitly disabled, return 'none'
        if (isset($contentRecord['enable_textflow']) &&
            ($contentRecord['enable_textflow'] === 'none' || empty($contentRecord['enable_textflow']))) {
            return 'none';
        }

        // If explicitly enabled with a specific language, return that language
        if (!empty($contentRecord['enable_textflow']) && $contentRecord['enable_textflow'] !== 'all') {
            return $contentRecord['enable_textflow'];
        }

        try {
            $context = GeneralUtility::makeInstance(Context::class);
            $languageAspect = $context->getAspect('language');
            $languageId = $languageAspect->getId();

            $languageMap = [
                0 => 'de',  // Default
                1 => 'en',  // English
                2 => 'fr',  // French
                3 => 'es',  // Spanish
                4 => 'it',  // Italian
                5 => 'nl',  // Dutch
                6 => 'pt',  // Portuguese
                7 => 'zh',  // Chinese
                8 => 'ar',  // Arabic
                9 => 'hi',  // Hindi
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

        try {
            $patterns = $this->patternRepository->findByLanguage($language);
            if ($patterns !== []) {
                $this->patternCache[$language] = $patterns;
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

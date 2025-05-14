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
    protected const SOFT_HYPHEN = '&shy;';

    public function __construct(?TextFlowPatternRepository $patternRepository = null)
    {
        $this->patternRepository = $patternRepository ?? GeneralUtility::makeInstance(TextFlowPatternRepository::class);
        $this->logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
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
        if (empty($content) || empty($conf['enable'])) {
            return $content;
        }

        // Preserve existing HTML structure
        if (!empty($conf['preserveStructure'])) {
            return $this->processContentPreservingStructure($content);
        }

        return $this->processContent($content);
    }

    /**
     * Process text parts and apply hyphenation
     */
    protected function processContentPreservingStructure(string $content): string
    {
        // Use DOMDocument to preserve HTML structure
        $dom = new \DOMDocument();
        $dom->loadHTML($content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        
        // Process text nodes only
        $xpath = new \DOMXPath($dom);
        $textNodes = $xpath->query('//text()');
        
        foreach ($textNodes as $node) {
            if (trim($node->nodeValue) !== '') {
                $node->nodeValue = $this->applyTextFlow($node->nodeValue);
            }
        }
        
        return $dom->saveHTML();
    }

    protected function processContent(string $content): string
    {
        return $this->applyTextFlow($content);
    }

    protected function applyTextFlow(string $text): string
    {
        // Hier kommt Ihre Text-Flow-Logik
        // Beispiel:
        $processed = str_replace(' ', ' ', $text); // Soft hyphen hinzufügen
        return $processed;
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
                3 => 'es'
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
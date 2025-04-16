<?php
declare(strict_types=1);
namespace PixelCoda\TextFlow\Service;

use PixelCoda\TextFlow\Domain\Repository\TextFlowPatternRepository;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Cache\CacheManager;

/**
 * Service for optimizing text flow with dynamic hyphenation.
 * Provides functionality to hyphenate text based on language-specific patterns
 * while preserving HTML structure and special characters.
 */
class TextFlowService
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
     * @param string $text The input text to be hyphenated
     * @param array<string, mixed> $contentRecord Optional content record with settings
     * @return string The hyphenated text
     */
    public function hyphenate(string $text, array $contentRecord = []): string
    {
        if ($text === '' || $text === '0') {
            $this->logger->notice('Empty text content provided');
            return '';
        }

        $enableTextFlow = $contentRecord['enable_textflow'] ?? 'all';
        if ($enableTextFlow === 'none') {
            return $text;
        }

        $language = $this->getCurrentLanguage($contentRecord);
        if ($language === '' || $language === '0') {
            $this->logger->warning('No valid language found', ['content' => $contentRecord]);
            return $text;
        }

        $patterns = $this->buildPatterns($language);
        if ($patterns === []) {
            $this->logger->warning('No patterns found for language', ['language' => $language]);
            return $text;
        }

        $this->logger->debug('Processing text with patterns', [
            'language' => $language,
            'patternCount' => count($patterns)
        ]);

        return $this->processText($text, $patterns);
    }

    /**
     * Process text parts and apply hyphenation
     */
    protected function processText(string $text, array $patterns): string
    {
        $parts = preg_split('/((?:<[^>]*>)|(?:\s+)|(?:[^a-zA-ZäöüßÄÖÜ]+))/u', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
        if ($parts === false) {
            $this->logger->error('Error splitting text into parts');
            return $text;
        }

        $result = '';
        foreach ($parts as $part) {
            if ($part === '' || $part === '0') {
                continue;
            }

            if ($this->shouldSkipPart($part)) {
                $result .= $part;
                continue;
            }

            if (mb_strlen($part) < self::MIN_WORD_LENGTH) {
                $result .= $part;
                continue;
            }

            $result .= $this->hyphenateWord($part, $patterns);
        }

        return $result;
    }

    /**
     * Check if part should be skipped (HTML or special chars)
     */
    protected function shouldSkipPart(string $part): bool
    {
        return preg_match('/^<[^>]*>$/', $part) || 
               preg_match('/^\s+$/', $part) || 
               !preg_match('/^[a-zA-ZäöüßÄÖÜ]+$/u', $part);
    }

    /**
     * Hyphenate a single word
     */
    protected function hyphenateWord(string $word, array $patterns): string
    {
        $originalWord = $word;
        $lowerWord = mb_strtolower($word);
        
        $positions = [];
        foreach ($patterns as $pattern) {
            $pattern = (string)$pattern['pattern'];
            $pos = mb_strpos($lowerWord, $pattern);
            if ($pos !== false && $pos > 1 && $pos < mb_strlen($lowerWord) - 2) {
                $positions[] = $pos + mb_strlen($pattern) - 1;
            }
        }

        if ($positions === []) {
            return $originalWord;
        }

        $positions = array_unique($positions);
        sort($positions);

        $result = '';
        $lastPos = 0;
        foreach ($positions as $pos) {
            $result .= mb_substr($originalWord, $lastPos, $pos - $lastPos) . self::SOFT_HYPHEN;
            $lastPos = $pos;
        }

        return $result . mb_substr($originalWord, $lastPos);
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
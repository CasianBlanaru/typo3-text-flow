<?php
declare(strict_types=1);
namespace PixelCoda\TextFlow\Service;

use PixelCoda\TextFlow\Domain\Repository\TextFlowPatternRepository;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Log\LogManager;
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

        $language = $this->getCurrentLanguage($conf);
        $patterns = $this->buildPatterns($language);

        if (empty($patterns)) {
            return $content;
        }

        // Verarbeite nur Text, wenn es sich um ein Text-Feld handelt
        if (!empty($conf['field']) && in_array($conf['field'], ['bodytext', 'header', 'subheader'])) {
            return $this->processText($content, $patterns);
        }

        return $content;
    }

    protected function processText(string $text, array $patterns): string
    {
        // Teile den Text in Wörter auf
        $words = preg_split('/(\s+|[[:punct:]])/u', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
        if ($words === false) {
            return $text;
        }

        $result = '';
        foreach ($words as $word) {
            if (empty($word) || mb_strlen($word) < self::MIN_WORD_LENGTH) {
                $result .= $word;
                continue;
            }

            // Verarbeite nur echte Wörter
            if (preg_match('/^[\p{L}]+$/u', $word)) {
                $result .= $this->hyphenateWord($word, $patterns);
            } else {
                $result .= $word;
            }
        }

        return $result;
    }

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

        if (empty($positions)) {
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
    protected function getCurrentLanguage(array $conf = []): string
    {
        if (!empty($conf['language'])) {
            return $conf['language'];
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
            if (!empty($patterns)) {
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
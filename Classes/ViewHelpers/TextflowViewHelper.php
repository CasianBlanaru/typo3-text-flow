<?php
declare(strict_types=1);
namespace Tpwd\TextFlow\ViewHelpers;

use Tpwd\TextFlow\Service\TextFlowService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Textflow ViewHelper
 *
 * Provides text hyphenation with language-specific patterns
 */
class TextflowViewHelper extends AbstractViewHelper
{
    /**
     * Disable output escaping
     */
    protected $escapeOutput = false;

    /**
     * Initialize arguments
     */
    public function initializeArguments(): void
    {
        $this->registerArgument('text', 'string', 'Text to process', false);
        $this->registerArgument('language', 'string', 'Language to use for hyphenation', false, 'none');
        $this->registerArgument('data', 'array', 'Content element data', false);
    }

    /**
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     * @return string
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ): string {
        $text = $arguments['text'] ?? $renderChildrenClosure();
        if (empty($text)) {
            return '';
        }

        $data = $arguments['data'] ?? [];
        $language = $arguments['language'];
        $textFlowEnabled = false;

        // Determine if TextFlow is enabled for this element
        if (isset($data['enable_textflow'])) {
            if ($data['enable_textflow'] === 'none' || empty($data['enable_textflow'])) {
                // TextFlow is explicitly disabled for this element
                return $text;
            }

            // TextFlow is enabled for this element
            $textFlowEnabled = true;

            // Apply content element language setting if specified
            if ($data['enable_textflow'] !== 'all') {
                $language = $data['enable_textflow'];
            } else {
                $language = 'all'; // Use all languages if specifically selected
            }
        } else if ($language === 'none') {
            // If no content element setting exists and default language is 'none', skip processing
            return $text;
        } else {
            // Language parameter was explicitly provided, so TextFlow is enabled
            $textFlowEnabled = true;
        }

        // Get TextFlow service and determine if debug mode is active
        $textFlowService = GeneralUtility::makeInstance(TextFlowService::class);
        $debugMode = TextFlowService::isDebugMode();

        // If debug mode is active but TextFlow isn't enabled for this element,
        // temporarily disable debug mode for this process
        if ($debugMode && !$textFlowEnabled) {
            // Save current debug state
            $currentDebugLevel = TextFlowService::getDebugLevel();

            // Disable debug mode
            TextFlowService::disableDebugMode();

            // Process without debug
            $result = $textFlowService->hyphenate($text, ['enable_textflow' => $language]);

            // Restore debug state
            if ($currentDebugLevel > 0) {
                TextFlowService::enableDebugMode();
                TextFlowService::setDebugLevel($currentDebugLevel);
            }

            return $result;
        }

        // Normal processing (with debug if enabled globally)
        return $textFlowService->hyphenate($text, ['enable_textflow' => $language]);
    }
}

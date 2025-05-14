<?php
declare(strict_types=1);

namespace PixelCoda\TextFlow\Hooks;

use PixelCoda\TextFlow\Service\TextFlowService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectStdWrapHookInterface;

class ContentObjectRendererHook implements ContentObjectStdWrapHookInterface
{
    /**
     * Hook for stdWrap processing
     */
    public function stdWrapPreProcess(string $content, array $configuration, ContentObjectRenderer $parentObject): string
    {
        if (empty($content)) {
            return $content;
        }

        $textFlowService = GeneralUtility::makeInstance(TextFlowService::class);
        return $textFlowService->hyphenate($content, $parentObject->data);
    }

    /**
     * Hook for stdWrap post-processing
     */
    public function stdWrapProcess(string $content, array $configuration, ContentObjectRenderer $parentObject): string
    {
        // No additional processing needed in the post-process hook
        return $content;
    }

    /**
     * Hook for stdWrap override
     */
    public function stdWrapOverride(string $content, array $configuration, ContentObjectRenderer $parentObject): string
    {
        // No override needed
        return $content;
    }
} 
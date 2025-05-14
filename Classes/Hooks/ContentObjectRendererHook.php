<?php
declare(strict_types=1);

namespace PixelCoda\TextFlow\Hooks;

use PixelCoda\TextFlow\Service\TextFlowService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

class ContentObjectRendererHook
{
    /**
     * Hook for stdWrap processing
     */
    public function processStdWrap(
        string $content,
        array $configuration,
        ContentObjectRenderer $contentObject
    ): string {
        if (empty($content)) {
            return $content;
        }

        $textFlowService = GeneralUtility::makeInstance(TextFlowService::class);
        return $textFlowService->hyphenate($content, $contentObject->data);
    }
} 
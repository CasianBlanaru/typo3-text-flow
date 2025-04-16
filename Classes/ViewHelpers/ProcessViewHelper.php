<?php
declare(strict_types=1);

namespace PixelCoda\TextFlow\ViewHelpers;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;
use PixelCoda\TextFlow\Service\TextFlowService;

class ProcessViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    protected $escapeOutput = false;

    public function initializeArguments(): void
    {
        $this->registerArgument('text', 'string', 'Text to process', false);
        $this->registerArgument('contentObject', 'array', 'Content object data', false, []);
    }

    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ): string {
        $text = $arguments['text'] ?? $renderChildrenClosure();
        if ($text === null) {
            return '';
        }

        $contentObject = $arguments['contentObject'] ?? [];
        $textFlowService = GeneralUtility::makeInstance(TextFlowService::class);

        return $textFlowService->hyphenate((string)$text, $contentObject);
    }
} 
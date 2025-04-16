<?php
declare(strict_types=1);

namespace PixelCoda\TextFlow\ViewHelpers;

use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

class OptimizeViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    /**
     * @var bool
     */
    protected $escapeOutput = false;

    /**
     * Initialize arguments
     */
    public function initializeArguments(): void
    {
        $this->registerArgument('language', 'string', 'Language for text optimization', true);
        $this->registerArgument('text', 'string', 'Text to optimize', false);
        $this->registerArgument('enableJs', 'bool', 'Enable JavaScript optimization', false, false);
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
        $language = $arguments['language'];
        $enableJs = $arguments['enableJs'];

        if ($enableJs) {
            $pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
            $pageRenderer->addJsInlineCode(
                'textflow-optimize',
                'textflow.init();'
            );
        }

        if ($language === 'de') {
            return str_replace(
                [' der ', ' die ', ' das '],
                ['&shy;der ', '&shy;die ', '&shy;das '],
                $text
            );
        }

        return $text;
    }
} 
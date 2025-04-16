<?php
declare(strict_types=1);

namespace PixelCoda\TextFlow\Tests\Unit\ViewHelpers;

use PixelCoda\TextFlow\ViewHelpers\OptimizeViewHelper;
use PixelCoda\TextFlow\Service\TextFlowService;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperInterface;
use TYPO3\CMS\Core\Page\PageRenderer;
use Psr\Log\LoggerInterface;

class OptimizeViewHelperTest extends UnitTestCase
{
    protected OptimizeViewHelper $subject;
    protected TextFlowService $textFlowServiceMock;
    protected LoggerInterface $loggerMock;
    protected RenderingContextInterface $renderingContextMock;
    protected PageRenderer $pageRendererMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->textFlowServiceMock = $this->createMock(TextFlowService::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->renderingContextMock = $this->createMock(RenderingContextInterface::class);
        $this->pageRendererMock = $this->createMock(PageRenderer::class);

        $this->subject = new OptimizeViewHelper($this->textFlowServiceMock, $this->loggerMock, $this->pageRendererMock);
        $this->subject->setRenderingContext($this->renderingContextMock);
    }

    /**
     * @test
     */
    public function implementsViewHelperInterface(): void
    {
        self::assertInstanceOf(ViewHelperInterface::class, $this->subject);
    }

    /**
     * @test
     */
    public function renderOptimizesTextContent(): void
    {
        $inputText = 'Dies ist ein Beispieltext';
        $hyphenatedText = 'Dies ist ein Bei­spiel­text';
        $contentObject = ['bodytext' => $inputText];

        $this->textFlowServiceMock->expects(self::once())
            ->method('hyphenate')
            ->with($inputText, $contentObject)
            ->willReturn($hyphenatedText);

        $this->pageRendererMock->expects(self::once())
            ->method('addJsInlineCode')
            ->with(
                'textflow-optimize',
                self::callback(function($js) {
                    return strpos($js, 'textflow.init') !== false;
                })
            );

        $result = $this->subject->render($inputText, $contentObject, true);

        self::assertEquals($hyphenatedText, $result);
    }

    /**
     * @test
     */
    public function renderHandlesEmptyText(): void
    {
        $inputText = '';
        $contentObject = ['bodytext' => $inputText];

        $this->loggerMock->expects(self::once())
            ->method('warning')
            ->with(
                'TextFlow ViewHelper: Empty text content',
                ['contentObject' => $contentObject]
            );

        $result = $this->subject->render($inputText, $contentObject);

        self::assertEquals('', $result);
    }

    /**
     * @test
     */
    public function renderHandlesNullText(): void
    {
        $inputText = null;
        $contentObject = ['bodytext' => $inputText];

        $this->loggerMock->expects(self::once())
            ->method('warning')
            ->with(
                'TextFlow ViewHelper: Null text content',
                ['contentObject' => $contentObject]
            );

        $result = $this->subject->render($inputText, $contentObject);

        self::assertEquals('', $result);
    }

    /**
     * @test
     */
    public function renderHandlesHtmlContent(): void
    {
        $inputText = '<p>Dies ist ein <strong>Beispieltext</strong> mit HTML</p>';
        $hyphenatedText = '<p>Dies ist ein <strong>Bei­spiel­text</strong> mit HTML</p>';
        $contentObject = ['bodytext' => $inputText];

        $this->textFlowServiceMock->expects(self::once())
            ->method('hyphenate')
            ->with($inputText, $contentObject)
            ->willReturn($hyphenatedText);

        $result = $this->subject->render($inputText, $contentObject);

        self::assertEquals($hyphenatedText, $result);
    }

    /**
     * @test
     */
    public function renderAddsJavaScriptWhenEnabled(): void
    {
        $inputText = 'Dies ist ein Beispieltext';
        $hyphenatedText = 'Dies ist ein Bei­spiel­text';
        $contentObject = ['bodytext' => $inputText];

        $this->textFlowServiceMock->method('hyphenate')
            ->willReturn($hyphenatedText);

        $this->pageRendererMock->expects(self::once())
            ->method('addJsInlineCode')
            ->with(
                'textflow-optimize',
                self::callback(function($js) {
                    return strpos($js, 'textflow.init') !== false;
                })
            );

        $this->subject->render($inputText, $contentObject, true);
    }

    /**
     * @test
     */
    public function renderSkipsJavaScriptWhenDisabled(): void
    {
        $inputText = 'Dies ist ein Beispieltext';
        $hyphenatedText = 'Dies ist ein Bei­spiel­text';
        $contentObject = ['bodytext' => $inputText];

        $this->textFlowServiceMock->method('hyphenate')
            ->willReturn($hyphenatedText);

        $this->pageRendererMock->expects(self::never())
            ->method('addJsInlineCode');

        $this->subject->render($inputText, $contentObject, false);
    }

    /**
     * @test
     */
    public function renderLogsDebugInformation(): void
    {
        $inputText = 'Dies ist ein Beispieltext';
        $hyphenatedText = 'Dies ist ein Bei­spiel­text';
        $contentObject = ['bodytext' => $inputText];

        $this->textFlowServiceMock->method('hyphenate')
            ->willReturn($hyphenatedText);

        $this->loggerMock->expects(self::once())
            ->method('debug')
            ->with(
                'TextFlow ViewHelper: Processing content with optimization',
                [
                    'originalText' => $inputText,
                    'processedText' => $hyphenatedText,
                    'contentObject' => $contentObject,
                    'enableJs' => true
                ]
            );

        $this->subject->render($inputText, $contentObject, true);
    }

    /**
     * @test
     */
    public function initializeArgumentsRegistersExpectedArguments(): void
    {
        $this->subject->initializeArguments();

        $arguments = $this->subject->prepareArguments();

        self::assertCount(3, $arguments);
        self::assertArrayHasKey('text', $arguments);
        self::assertArrayHasKey('contentObject', $arguments);
        self::assertArrayHasKey('enableJs', $arguments);

        self::assertEquals('string', $arguments['text']->getType());
        self::assertEquals('array', $arguments['contentObject']->getType());
        self::assertEquals('boolean', $arguments['enableJs']->getType());
        self::assertFalse($arguments['text']->isRequired());
        self::assertFalse($arguments['contentObject']->isRequired());
        self::assertFalse($arguments['enableJs']->isRequired());
        self::assertTrue($arguments['enableJs']->getDefaultValue());
    }
} 
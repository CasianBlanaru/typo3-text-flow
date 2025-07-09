<?php
declare(strict_types=1);

namespace Tpwd\TextFlow\Tests\Unit\Hooks;

use Tpwd\TextFlow\Hooks\ContentObjectRendererHook;
use Tpwd\TextFlow\Service\TextFlowService;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;
use Psr\Log\LoggerInterface;

class ContentObjectRendererHookTest extends UnitTestCase
{
    protected ContentObjectRendererHook $subject;
    protected TextFlowService $textFlowServiceMock;
    protected LoggerInterface $loggerMock;
    protected ContentObjectRenderer $contentObjectRendererMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->textFlowServiceMock = $this->createMock(TextFlowService::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->contentObjectRendererMock = $this->createMock(ContentObjectRenderer::class);

        $this->subject = new ContentObjectRendererHook($this->textFlowServiceMock, $this->loggerMock);
    }

    /**
     * @test
     */
    public function contentPostProcHyphenatesTextContent(): void
    {
        $inputText = 'Dies ist ein Beispieltext';
        $hyphenatedText = 'Dies ist ein Bei­spiel­text';
        $contentObject = ['bodytext' => $inputText];

        $this->contentObjectRendererMock->data = $contentObject;

        $this->textFlowServiceMock->expects(self::once())
            ->method('hyphenate')
            ->with($inputText, $contentObject)
            ->willReturn($hyphenatedText);

        $params = [
            'pObj' => $this->contentObjectRendererMock,
            'text' => $inputText
        ];

        $result = $this->subject->contentPostProc($params);

        self::assertEquals($hyphenatedText, $result);
    }

    /**
     * @test
     */
    public function contentPostProcHandlesEmptyText(): void
    {
        $inputText = '';
        $contentObject = ['bodytext' => $inputText];

        $this->contentObjectRendererMock->data = $contentObject;

        $this->loggerMock->expects(self::once())
            ->method('warning')
            ->with(
                'TextFlow Hook: Empty text content',
                ['contentObject' => $contentObject]
            );

        $params = [
            'pObj' => $this->contentObjectRendererMock,
            'text' => $inputText
        ];

        $result = $this->subject->contentPostProc($params);

        self::assertEquals($inputText, $result);
    }

    /**
     * @test
     */
    public function contentPostProcHandlesNullText(): void
    {
        $inputText = null;
        $contentObject = ['bodytext' => $inputText];

        $this->contentObjectRendererMock->data = $contentObject;

        $this->loggerMock->expects(self::once())
            ->method('warning')
            ->with(
                'TextFlow Hook: Null text content',
                ['contentObject' => $contentObject]
            );

        $params = [
            'pObj' => $this->contentObjectRendererMock,
            'text' => $inputText
        ];

        $result = $this->subject->contentPostProc($params);

        self::assertEquals('', $result);
    }

    /**
     * @test
     */
    public function contentPostProcHandlesSpecialCharacters(): void
    {
        $inputText = 'Text mit Sonderzeichen: äöüß!?';
        $hyphenatedText = 'Text mit Son­der­zei­chen: äöüß!?';
        $contentObject = ['bodytext' => $inputText];

        $this->contentObjectRendererMock->data = $contentObject;

        $this->textFlowServiceMock->expects(self::once())
            ->method('hyphenate')
            ->with($inputText, $contentObject)
            ->willReturn($hyphenatedText);

        $params = [
            'pObj' => $this->contentObjectRendererMock,
            'text' => $inputText
        ];

        $result = $this->subject->contentPostProc($params);

        self::assertEquals($hyphenatedText, $result);
    }

    /**
     * @test
     */
    public function contentPostProcHandlesHtmlContent(): void
    {
        $inputText = '<p>Dies ist ein <strong>Beispieltext</strong> mit HTML</p>';
        $hyphenatedText = '<p>Dies ist ein <strong>Bei­spiel­text</strong> mit HTML</p>';
        $contentObject = ['bodytext' => $inputText];

        $this->contentObjectRendererMock->data = $contentObject;

        $this->textFlowServiceMock->expects(self::once())
            ->method('hyphenate')
            ->with($inputText, $contentObject)
            ->willReturn($hyphenatedText);

        $params = [
            'pObj' => $this->contentObjectRendererMock,
            'text' => $inputText
        ];

        $result = $this->subject->contentPostProc($params);

        self::assertEquals($hyphenatedText, $result);
    }

    /**
     * @test
     */
    public function contentPostProcLogsDebugInformation(): void
    {
        $inputText = 'Dies ist ein Beispieltext';
        $hyphenatedText = 'Dies ist ein Bei­spiel­text';
        $contentObject = ['bodytext' => $inputText];

        $this->contentObjectRendererMock->data = $contentObject;

        $this->textFlowServiceMock->method('hyphenate')
            ->willReturn($hyphenatedText);

        $this->loggerMock->expects(self::once())
            ->method('debug')
            ->with(
                'TextFlow Hook: Processing content',
                [
                    'originalText' => $inputText,
                    'hyphenatedText' => $hyphenatedText,
                    'contentObject' => $contentObject
                ]
            );

        $params = [
            'pObj' => $this->contentObjectRendererMock,
            'text' => $inputText
        ];

        $this->subject->contentPostProc($params);
    }
} 
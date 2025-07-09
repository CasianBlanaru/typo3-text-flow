<?php
declare(strict_types=1);

namespace Tpwd\TextFlow\Tests\Unit\DataProcessing;

use Tpwd\TextFlow\DataProcessing\TextFlowProcessor;
use Tpwd\TextFlow\Service\TextFlowService;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;
use Psr\Log\LoggerInterface;

class TextFlowProcessorTest extends UnitTestCase
{
    protected TextFlowProcessor $subject;
    protected TextFlowService $textFlowServiceMock;
    protected LoggerInterface $loggerMock;
    protected ContentObjectRenderer $contentObjectRendererMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->textFlowServiceMock = $this->createMock(TextFlowService::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->contentObjectRendererMock = $this->createMock(ContentObjectRenderer::class);

        $this->subject = new TextFlowProcessor($this->textFlowServiceMock, $this->loggerMock);
    }

    /**
     * @test
     */
    public function processHyphenatesTextContent(): void
    {
        $inputText = 'Dies ist ein Beispieltext';
        $hyphenatedText = 'Dies ist ein Bei­spiel­text';
        $contentObject = [
            'data' => [
                'bodytext' => $inputText,
                'header' => 'Test Header'
            ]
        ];

        $this->textFlowServiceMock->expects(self::once())
            ->method('hyphenate')
            ->with($inputText, $contentObject['data'])
            ->willReturn($hyphenatedText);

        $processedData = $this->subject->process(
            $contentObject,
            [],
            [],
            $this->contentObjectRendererMock
        );

        self::assertEquals($hyphenatedText, $processedData['processedText']);
        self::assertEquals($inputText, $processedData['originalText']);
    }

    /**
     * @test
     */
    public function processHandlesEmptyText(): void
    {
        $contentObject = [
            'data' => [
                'bodytext' => '',
                'header' => 'Test Header'
            ]
        ];

        $this->loggerMock->expects(self::once())
            ->method('warning')
            ->with(
                'TextFlow Processor: Empty text content',
                ['contentObject' => $contentObject['data']]
            );

        $processedData = $this->subject->process(
            $contentObject,
            [],
            [],
            $this->contentObjectRendererMock
        );

        self::assertEquals('', $processedData['processedText']);
        self::assertEquals('', $processedData['originalText']);
    }

    /**
     * @test
     */
    public function processHandlesCustomFieldConfiguration(): void
    {
        $inputText = 'Dies ist ein Beispieltext';
        $hyphenatedText = 'Dies ist ein Bei­spiel­text';
        $contentObject = [
            'data' => [
                'custom_field' => $inputText,
                'header' => 'Test Header'
            ]
        ];
        $processorConfiguration = [
            'textField' => 'custom_field'
        ];

        $this->textFlowServiceMock->expects(self::once())
            ->method('hyphenate')
            ->with($inputText, $contentObject['data'])
            ->willReturn($hyphenatedText);

        $processedData = $this->subject->process(
            $contentObject,
            [],
            $processorConfiguration,
            $this->contentObjectRendererMock
        );

        self::assertEquals($hyphenatedText, $processedData['processedText']);
        self::assertEquals($inputText, $processedData['originalText']);
    }

    /**
     * @test
     */
    public function processHandlesMissingField(): void
    {
        $contentObject = [
            'data' => [
                'header' => 'Test Header'
            ]
        ];
        $processorConfiguration = [
            'textField' => 'non_existent_field'
        ];

        $this->loggerMock->expects(self::once())
            ->method('error')
            ->with(
                'TextFlow Processor: Configured text field not found',
                [
                    'field' => 'non_existent_field',
                    'contentObject' => $contentObject['data']
                ]
            );

        $processedData = $this->subject->process(
            $contentObject,
            [],
            $processorConfiguration,
            $this->contentObjectRendererMock
        );

        self::assertEquals('', $processedData['processedText']);
        self::assertEquals('', $processedData['originalText']);
    }

    /**
     * @test
     */
    public function processHandlesHtmlContent(): void
    {
        $inputText = '<p>Dies ist ein <strong>Beispieltext</strong> mit HTML</p>';
        $hyphenatedText = '<p>Dies ist ein <strong>Bei­spiel­text</strong> mit HTML</p>';
        $contentObject = [
            'data' => [
                'bodytext' => $inputText,
                'header' => 'Test Header'
            ]
        ];

        $this->textFlowServiceMock->expects(self::once())
            ->method('hyphenate')
            ->with($inputText, $contentObject['data'])
            ->willReturn($hyphenatedText);

        $processedData = $this->subject->process(
            $contentObject,
            [],
            [],
            $this->contentObjectRendererMock
        );

        self::assertEquals($hyphenatedText, $processedData['processedText']);
        self::assertEquals($inputText, $processedData['originalText']);
    }

    /**
     * @test
     */
    public function processLogsDebugInformation(): void
    {
        $inputText = 'Dies ist ein Beispieltext';
        $hyphenatedText = 'Dies ist ein Bei­spiel­text';
        $contentObject = [
            'data' => [
                'bodytext' => $inputText,
                'header' => 'Test Header'
            ]
        ];

        $this->textFlowServiceMock->method('hyphenate')
            ->willReturn($hyphenatedText);

        $this->loggerMock->expects(self::once())
            ->method('debug')
            ->with(
                'TextFlow Processor: Processing content',
                [
                    'originalText' => $inputText,
                    'processedText' => $hyphenatedText,
                    'contentObject' => $contentObject['data']
                ]
            );

        $this->subject->process(
            $contentObject,
            [],
            [],
            $this->contentObjectRendererMock
        );
    }
} 
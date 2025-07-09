<?php
declare(strict_types=1);

namespace Tpwdag\TextFlow\Tests\Unit\Controller;

use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\ResponseInterface;
use Tpwdag\TextFlow\Controller\TextFlowController;
use Tpwdag\TextFlow\Service\TextFlowService;
use Psr\Log\LoggerInterface;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Extbase\Mvc\Controller\Arguments;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;

class TextFlowControllerTest extends UnitTestCase
{
    protected TextFlowController $subject;
    protected TextFlowService|MockObject $textFlowServiceMock;
    protected LoggerInterface|MockObject $loggerMock;
    protected ViewInterface|MockObject $viewMock;
    protected ConfigurationManager|MockObject $configurationManagerMock;
    protected ContentObjectRenderer|MockObject $contentObjectRendererMock;
    protected Request|MockObject $requestMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->textFlowServiceMock = $this->createMock(TextFlowService::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->viewMock = $this->createMock(ViewInterface::class);
        $this->configurationManagerMock = $this->createMock(ConfigurationManager::class);
        $this->contentObjectRendererMock = $this->createMock(ContentObjectRenderer::class);
        $this->requestMock = $this->createMock(Request::class);

        $this->contentObjectRendererMock->data = [];
        $this->configurationManagerMock->method('getContentObject')
            ->willReturn($this->contentObjectRendererMock);

        $this->subject = new TextFlowController($this->textFlowServiceMock, $this->loggerMock);
        $this->subject->injectConfigurationManager($this->configurationManagerMock);
        $this->subject->injectView($this->viewMock);
    }

    /**
     * @test
     */
    public function showActionHyphenatesTextAndAssignsToView(): void
    {
        $inputText = 'Dies ist ein Beispieltext';
        $hyphenatedText = 'Dies ist ein Bei­spiel­text';
        $contentObject = ['bodytext' => $inputText];

        $this->contentObjectRendererMock->data = $contentObject;

        $this->textFlowServiceMock->expects(self::once())
            ->method('hyphenate')
            ->with($inputText, $contentObject)
            ->willReturn($hyphenatedText);

        $this->viewMock->expects(self::once())
            ->method('assign')
            ->with('hyphenatedText', $hyphenatedText);

        $response = $this->subject->showAction();
        self::assertInstanceOf(ResponseInterface::class, $response);
    }

    /**
     * @test
     */
    public function optimizeActionHandlesPostRequest(): void
    {
        $inputText = 'Dies ist ein Beispieltext';
        $hyphenatedText = 'Dies ist ein Bei­spiel­text';

        $this->requestMock->method('getMethod')
            ->willReturn('POST');
        $this->requestMock->method('hasArgument')
            ->with('text')
            ->willReturn(true);
        $this->requestMock->method('getArgument')
            ->with('text')
            ->willReturn($inputText);

        $this->textFlowServiceMock->expects(self::once())
            ->method('hyphenate')
            ->with($inputText, [])
            ->willReturn($hyphenatedText);

        $this->viewMock->expects(self::exactly(3))
            ->method('assign')
            ->withConsecutive(
                ['optimizedText', $hyphenatedText],
                ['originalText', $inputText],
                ['settings', null]
            );

        $this->subject->setRequest($this->requestMock);
        $response = $this->subject->optimizeAction();
        self::assertInstanceOf(ResponseInterface::class, $response);
    }

    /**
     * @test
     */
    public function optimizeActionHandlesEmptyText(): void
    {
        $this->requestMock->method('getMethod')
            ->willReturn('POST');
        $this->requestMock->method('hasArgument')
            ->with('text')
            ->willReturn(false);

        $this->loggerMock->expects(self::once())
            ->method('warning')
            ->with('TextFlow Plugin: No text content found for optimization');

        $this->viewMock->expects(self::once())
            ->method('assign')
            ->with('error', 'Bitte geben Sie einen Text zur Optimierung ein.');

        $this->subject->setRequest($this->requestMock);
        $response = $this->subject->optimizeAction();
        self::assertInstanceOf(ResponseInterface::class, $response);
    }

    /**
     * @test
     */
    public function listActionAssignsContentObjectData(): void
    {
        $contentObject = [
            'bodytext' => 'Dies ist ein Beispieltext',
            'pid' => 1,
            'uid' => 123
        ];

        $this->contentObjectRendererMock->data = $contentObject;

        $this->viewMock->expects(self::exactly(3))
            ->method('assign')
            ->withConsecutive(
                ['text', $contentObject['bodytext']],
                ['settings', null],
                ['contentObject', $contentObject]
            );

        $this->loggerMock->expects(self::once())
            ->method('debug')
            ->with(
                'TextFlow Plugin: listAction called',
                [
                    'settings' => null,
                    'contentObject' => $contentObject
                ]
            );

        $response = $this->subject->listAction();
        self::assertInstanceOf(ResponseInterface::class, $response);
    }

    /**
     * @test
     */
    public function textFlowActionProcessesContentObject(): void
    {
        $inputText = 'Dies ist ein Beispieltext';
        $hyphenatedText = 'Dies ist ein Bei­spiel­text';
        $contentObject = ['bodytext' => $inputText];

        $this->contentObjectRendererMock->data = $contentObject;

        $this->textFlowServiceMock->expects(self::once())
            ->method('hyphenate')
            ->with($inputText, $contentObject)
            ->willReturn($hyphenatedText);

        $this->viewMock->expects(self::once())
            ->method('assign')
            ->with('text', $hyphenatedText);

        $response = $this->subject->textFlowAction();
        self::assertInstanceOf(ResponseInterface::class, $response);
    }

    /**
     * @test
     */
    public function initializeOptimizeActionRegistersTextArgument(): void
    {
        $arguments = new Arguments();
        $this->subject->_set('arguments', $arguments);

        $this->requestMock->method('getMethod')
            ->willReturn('POST');

        $this->subject->setRequest($this->requestMock);
        $this->subject->initializeOptimizeAction();

        self::assertTrue($arguments->hasArgument('text'));
        self::assertEquals('string', $arguments->getArgument('text')->getDataType());
        self::assertFalse($arguments->getArgument('text')->isRequired());
    }
} 
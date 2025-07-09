<?php
declare(strict_types=1);

namespace Tpwdag\TextFlow\Tests\Unit\Hooks;

use Tpwdag\TextFlow\Hooks\PageLayoutViewDrawItemHook;
use Tpwdag\TextFlow\Service\TextFlowService;
use TYPO3\CMS\Backend\View\PageLayoutView;
use TYPO3\CMS\Backend\View\PageLayoutViewDrawItemHookInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;
use Psr\Log\LoggerInterface;

class PageLayoutViewDrawItemHookTest extends UnitTestCase
{
    protected PageLayoutViewDrawItemHook $subject;
    protected TextFlowService $textFlowServiceMock;
    protected LoggerInterface $loggerMock;
    protected PageLayoutView $pageLayoutViewMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->textFlowServiceMock = $this->createMock(TextFlowService::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->pageLayoutViewMock = $this->createMock(PageLayoutView::class);

        $this->subject = new PageLayoutViewDrawItemHook($this->textFlowServiceMock, $this->loggerMock);
    }

    protected function tearDown(): void
    {
        GeneralUtility::purgeInstances();
        parent::tearDown();
    }

    /**
     * @test
     */
    public function preProcessDrawItemSetsHeaderForTextFlowPlugin(): void
    {
        $row = [
            'header' => 'Test Header',
            'bodytext' => 'Dies ist ein Beispieltext',
            'CType' => 'text_flow_textflow'
        ];
        $drawItem = true;
        $headerContent = '';
        $itemContent = '';

        $this->subject->preProcess(
            $this->pageLayoutViewMock,
            $drawItem,
            $headerContent,
            $itemContent,
            $row
        );

        self::assertStringContainsString('TextFlow Plugin', $headerContent);
        self::assertTrue($drawItem);
    }

    /**
     * @test
     */
    public function preProcessDrawItemHandlesOptimizePlugin(): void
    {
        $row = [
            'header' => 'Optimize Header',
            'bodytext' => 'Dies ist ein Beispieltext',
            'CType' => 'text_flow_optimize'
        ];
        $drawItem = true;
        $headerContent = '';
        $itemContent = '';

        $this->subject->preProcess(
            $this->pageLayoutViewMock,
            $drawItem,
            $headerContent,
            $itemContent,
            $row
        );

        self::assertStringContainsString('TextFlow Optimize', $headerContent);
        self::assertTrue($drawItem);
    }

    /**
     * @test
     */
    public function preProcessDrawItemHandlesProcessPlugin(): void
    {
        $row = [
            'header' => 'Process Header',
            'bodytext' => 'Dies ist ein Beispieltext',
            'CType' => 'text_flow_process'
        ];
        $drawItem = true;
        $headerContent = '';
        $itemContent = '';

        $this->subject->preProcess(
            $this->pageLayoutViewMock,
            $drawItem,
            $headerContent,
            $itemContent,
            $row
        );

        self::assertStringContainsString('TextFlow Process', $headerContent);
        self::assertTrue($drawItem);
    }

    /**
     * @test
     */
    public function preProcessDrawItemIgnoresNonTextFlowPlugins(): void
    {
        $row = [
            'header' => 'Other Header',
            'bodytext' => 'Dies ist ein Beispieltext',
            'CType' => 'text'
        ];
        $drawItem = true;
        $headerContent = 'Original Header';
        $itemContent = 'Original Content';

        $this->subject->preProcess(
            $this->pageLayoutViewMock,
            $drawItem,
            $headerContent,
            $itemContent,
            $row
        );

        self::assertEquals('Original Header', $headerContent);
        self::assertEquals('Original Content', $itemContent);
        self::assertTrue($drawItem);
    }

    /**
     * @test
     */
    public function preProcessDrawItemAddsPreviewContent(): void
    {
        $row = [
            'header' => 'Test Header',
            'bodytext' => 'Dies ist ein Beispieltext',
            'CType' => 'text_flow_textflow'
        ];
        $drawItem = true;
        $headerContent = '';
        $itemContent = '';

        $this->textFlowServiceMock->expects(self::once())
            ->method('hyphenate')
            ->with($row['bodytext'])
            ->willReturn('Dies ist ein Bei­spiel­text');

        $this->subject->preProcess(
            $this->pageLayoutViewMock,
            $drawItem,
            $headerContent,
            $itemContent,
            $row
        );

        self::assertStringContainsString('Dies ist ein Bei­spiel­text', $itemContent);
        self::assertTrue($drawItem);
    }

    /**
     * @test
     */
    public function preProcessDrawItemHandlesEmptyBodytext(): void
    {
        $row = [
            'header' => 'Test Header',
            'bodytext' => '',
            'CType' => 'text_flow_textflow'
        ];
        $drawItem = true;
        $headerContent = '';
        $itemContent = '';

        $this->loggerMock->expects(self::once())
            ->method('warning')
            ->with(
                'TextFlow Plugin: Empty bodytext in content element',
                ['uid' => null, 'pid' => null]
            );

        $this->subject->preProcess(
            $this->pageLayoutViewMock,
            $drawItem,
            $headerContent,
            $itemContent,
            $row
        );

        self::assertStringContainsString('Kein Text vorhanden', $itemContent);
        self::assertTrue($drawItem);
    }
} 
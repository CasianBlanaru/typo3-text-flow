<?php
declare(strict_types=1);

namespace PixelCoda\TextFlow\Tests\Unit\ViewHelpers;

use PHPUnit\Framework\TestCase;
use PixelCoda\TextFlow\Service\TextFlowService;
use PixelCoda\TextFlow\ViewHelpers\ProcessViewHelper;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperInterface;

class ProcessViewHelperTest extends TestCase
{
    protected ProcessViewHelper $viewHelper;
    protected TextFlowService $textFlowService;
    protected RenderingContextInterface $renderingContext;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->textFlowService = $this->createMock(TextFlowService::class);
        GeneralUtility::addInstance(TextFlowService::class, $this->textFlowService);
        
        $this->renderingContext = $this->createMock(RenderingContextInterface::class);
        $this->viewHelper = new ProcessViewHelper();
    }

    protected function tearDown(): void
    {
        GeneralUtility::purgeInstances();
        parent::tearDown();
    }

    /**
     * @test
     */
    public function implementsViewHelperInterface(): void
    {
        $this->assertInstanceOf(ViewHelperInterface::class, $this->viewHelper);
    }

    /**
     * @test
     */
    public function initializeArgumentsRegistersExpectedArguments(): void
    {
        $this->viewHelper->initializeArguments();
        
        $arguments = $this->viewHelper->prepareArguments();
        $this->assertArrayHasKey('text', $arguments);
        $this->assertArrayHasKey('contentObject', $arguments);
        
        $this->assertEquals('string', $arguments['text']->getType());
        $this->assertEquals('array', $arguments['contentObject']->getType());
        
        $this->assertFalse($arguments['text']->isRequired());
        $this->assertFalse($arguments['contentObject']->isRequired());
    }

    /**
     * @test
     */
    public function renderStaticReturnsEmptyStringForNullInput(): void
    {
        $arguments = ['text' => null];
        $renderChildrenClosure = function() { return null; };

        $result = ProcessViewHelper::renderStatic(
            $arguments,
            $renderChildrenClosure,
            $this->renderingContext
        );

        $this->assertSame('', $result);
    }

    /**
     * @test
     */
    public function renderStaticProcessesTextWithService(): void
    {
        $testText = 'Test text';
        $expectedResult = 'Pro­ces­sed text';
        $contentObject = ['type' => 'textmedia'];
        
        $this->textFlowService->expects($this->once())
            ->method('hyphenate')
            ->with($testText, $contentObject)
            ->willReturn($expectedResult);

        GeneralUtility::addInstance(TextFlowService::class, $this->textFlowService);

        $arguments = [
            'text' => $testText,
            'contentObject' => $contentObject
        ];
        $renderChildrenClosure = function() { return null; };

        $result = ProcessViewHelper::renderStatic(
            $arguments,
            $renderChildrenClosure,
            $this->renderingContext
        );

        $this->assertSame($expectedResult, $result);
    }

    /**
     * @test
     */
    public function renderStaticUsesChildrenClosureWhenTextArgumentIsNotProvided(): void
    {
        $childrenText = 'Children text';
        $expectedResult = 'Pro­ces­sed children';
        
        $this->textFlowService->expects($this->once())
            ->method('hyphenate')
            ->with($childrenText, [])
            ->willReturn($expectedResult);

        GeneralUtility::addInstance(TextFlowService::class, $this->textFlowService);

        $arguments = [];
        $renderChildrenClosure = function() use ($childrenText) { 
            return $childrenText; 
        };

        $result = ProcessViewHelper::renderStatic(
            $arguments,
            $renderChildrenClosure,
            $this->renderingContext
        );

        $this->assertSame($expectedResult, $result);
    }

    /**
     * @test
     */
    public function renderStaticHandlesHtmlContent(): void
    {
        $htmlText = '<p>This is a <strong>test</strong> paragraph</p>';
        $expectedResult = '<p>This is a <strong>test</strong> pa­ra­graph</p>';
        
        $this->textFlowService->expects($this->once())
            ->method('hyphenate')
            ->with($htmlText, [])
            ->willReturn($expectedResult);

        GeneralUtility::addInstance(TextFlowService::class, $this->textFlowService);

        $arguments = ['text' => $htmlText];
        $renderChildrenClosure = function() { return null; };

        $result = ProcessViewHelper::renderStatic(
            $arguments,
            $renderChildrenClosure,
            $this->renderingContext
        );

        $this->assertSame($expectedResult, $result);
    }

    /**
     * @test
     */
    public function renderStaticHandlesSpecialCharacters(): void
    {
        $specialText = 'Text mit Umlauten: äöüß';
        $expectedResult = 'Text mit Um­lau­ten: äöüß';
        
        $this->textFlowService->expects($this->once())
            ->method('hyphenate')
            ->with($specialText, [])
            ->willReturn($expectedResult);

        GeneralUtility::addInstance(TextFlowService::class, $this->textFlowService);

        $arguments = ['text' => $specialText];
        $renderChildrenClosure = function() { return null; };

        $result = ProcessViewHelper::renderStatic(
            $arguments,
            $renderChildrenClosure,
            $this->renderingContext
        );

        $this->assertSame($expectedResult, $result);
    }

    /**
     * @test
     */
    public function renderStaticPreservesCaseInOutput(): void
    {
        $mixedCaseText = 'CamelCase TEXT with MixedCase';
        $expectedResult = 'Ca­mel­Case TEXT with Mixed­Case';
        
        $this->textFlowService->expects($this->once())
            ->method('hyphenate')
            ->with($mixedCaseText, [])
            ->willReturn($expectedResult);

        GeneralUtility::addInstance(TextFlowService::class, $this->textFlowService);

        $arguments = ['text' => $mixedCaseText];
        $renderChildrenClosure = function() { return null; };

        $result = ProcessViewHelper::renderStatic(
            $arguments,
            $renderChildrenClosure,
            $this->renderingContext
        );

        $this->assertSame($expectedResult, $result);
    }

    /**
     * @test
     */
    public function renderStaticHandlesEmptyContentObject(): void
    {
        $text = 'Simple text';
        $expectedResult = 'Sim­ple text';
        $emptyContentObject = [];
        
        $this->textFlowService->expects($this->once())
            ->method('hyphenate')
            ->with($text, $emptyContentObject)
            ->willReturn($expectedResult);

        GeneralUtility::addInstance(TextFlowService::class, $this->textFlowService);

        $arguments = [
            'text' => $text,
            'contentObject' => $emptyContentObject
        ];
        $renderChildrenClosure = function() { return null; };

        $result = ProcessViewHelper::renderStatic(
            $arguments,
            $renderChildrenClosure,
            $this->renderingContext
        );

        $this->assertSame($expectedResult, $result);
    }

    /**
     * @test
     */
    public function renderStaticHandlesComplexHtmlWithAttributes(): void
    {
        $complexHtml = '<div class="content"><p style="color: red;">Complex <em data-test="value">HTML</em> content</p></div>';
        $expectedResult = '<div class="content"><p style="color: red;">Com­plex <em data-test="value">HTML</em> con­tent</p></div>';
        
        $this->textFlowService->expects($this->once())
            ->method('hyphenate')
            ->with($complexHtml, [])
            ->willReturn($expectedResult);

        GeneralUtility::addInstance(TextFlowService::class, $this->textFlowService);

        $arguments = ['text' => $complexHtml];
        $renderChildrenClosure = function() { return null; };

        $result = ProcessViewHelper::renderStatic(
            $arguments,
            $renderChildrenClosure,
            $this->renderingContext
        );

        $this->assertSame($expectedResult, $result);
    }

    /**
     * @test
     */
    public function renderStaticHandlesMultilineText(): void
    {
        $multilineText = "First line\nSecond line\rThird line\r\nFourth line";
        $expectedResult = "First line\nSe­cond line\rThird line\r\nFourth line";
        
        $this->textFlowService->expects($this->once())
            ->method('hyphenate')
            ->with($multilineText, [])
            ->willReturn($expectedResult);

        GeneralUtility::addInstance(TextFlowService::class, $this->textFlowService);

        $arguments = ['text' => $multilineText];
        $renderChildrenClosure = function() { return null; };

        $result = ProcessViewHelper::renderStatic(
            $arguments,
            $renderChildrenClosure,
            $this->renderingContext
        );

        $this->assertSame($expectedResult, $result);
    }

    /**
     * @test
     */
    public function renderStaticHandlesContentObjectWithConfiguration(): void
    {
        $text = 'Configuration test';
        $expectedResult = 'Con­fi­gu­ra­tion test';
        $contentObject = [
            'type' => 'textmedia',
            'enable_textflow' => 'de',
            'layout' => 'default'
        ];
        
        $this->textFlowService->expects($this->once())
            ->method('hyphenate')
            ->with($text, $contentObject)
            ->willReturn($expectedResult);

        GeneralUtility::addInstance(TextFlowService::class, $this->textFlowService);

        $arguments = [
            'text' => $text,
            'contentObject' => $contentObject
        ];
        $renderChildrenClosure = function() { return null; };

        $result = ProcessViewHelper::renderStatic(
            $arguments,
            $renderChildrenClosure,
            $this->renderingContext
        );

        $this->assertSame($expectedResult, $result);
    }

    /**
     * @test
     */
    public function renderStaticHandlesZeroAsValidInput(): void
    {
        $text = '0';
        $expectedResult = '0';
        
        $this->textFlowService->expects($this->once())
            ->method('hyphenate')
            ->with($text, [])
            ->willReturn($expectedResult);

        GeneralUtility::addInstance(TextFlowService::class, $this->textFlowService);

        $arguments = ['text' => $text];
        $renderChildrenClosure = function() { return null; };

        $result = ProcessViewHelper::renderStatic(
            $arguments,
            $renderChildrenClosure,
            $this->renderingContext
        );

        $this->assertSame($expectedResult, $result);
    }
} 
<?php
declare(strict_types=1);
namespace PixelCoda\TextFlow\Tests\Unit\Controller;

use PixelCoda\TextFlow\Controller\AjaxController;
use PixelCoda\TextFlow\Service\TextFlowService;
use PixelCoda\TextFlow\Domain\Repository\TextFlowPatternRepository;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;
use TYPO3\CMS\Core\Http\JsonResponse;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Test case for the AjaxController
 */
class AjaxControllerTest extends UnitTestCase
{
    /**
     * @var AjaxController
     */
    protected $controller;
    
    /**
     * @var TextFlowService|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $textFlowServiceMock;
    
    /**
     * @var ServerRequestInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $requestMock;
    
    /**
     * @var StreamInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $streamMock;

    /**
     * Set up
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->textFlowServiceMock = $this->getMockBuilder(TextFlowService::class)
            ->disableOriginalConstructor()
            ->getMock();
            
        $this->streamMock = $this->getMockBuilder(StreamInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
            
        $this->requestMock = $this->getMockBuilder(ServerRequestInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
            
        $this->requestMock->method('getBody')->willReturn($this->streamMock);
        
        $this->controller = new AjaxController();
    }
    
    /**
     * @test
     */
    public function previewActionReturnsErrorResponseForEmptyText(): void
    {
        // Setup mocks
        $this->streamMock->method('getContents')->willReturn(json_encode(['text' => '']));
        
        // Call the action
        $response = $this->controller->previewAction($this->requestMock);
        
        // Assert response
        $responseData = json_decode($response->getBody()->getContents(), true);
        self::assertFalse($responseData['success']);
        self::assertEquals('No text provided', $responseData['message']);
    }
    
    /**
     * @test
     */
    public function previewActionCallsTextFlowServiceAndReturnsSuccessResponse(): void
    {
        // Setup mocks
        $this->streamMock->method('getContents')->willReturn(json_encode([
            'text' => 'Test text',
            'language' => 'de'
        ]));
        
        $this->textFlowServiceMock->expects(self::once())
            ->method('hyphenate')
            ->with('Test text', [
                'enable' => true,
                'enable_textflow' => 'de',
                'preserveStructure' => true
            ])
            ->willReturn('Test&shy;text');
        
        GeneralUtility::addInstance(TextFlowService::class, $this->textFlowServiceMock);
        
        // Call the action
        $response = $this->controller->previewAction($this->requestMock);
        
        // Assert response
        $responseData = json_decode($response->getBody()->getContents(), true);
        self::assertTrue($responseData['success']);
        self::assertEquals('Test&shy;text', $responseData['result']);
    }
    
    /**
     * @test
     */
    public function getPatternsActionReturnsPatterns(): void
    {
        // Setup repository mock
        $patternRepositoryMock = $this->getMockBuilder(TextFlowPatternRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
            
        $patternRepositoryMock->expects(self::once())
            ->method('findByLanguage')
            ->with('de')
            ->willReturn([
                ['pattern' => 'ung'],
                ['pattern' => 'lich']
            ]);
            
        GeneralUtility::addInstance(TextFlowPatternRepository::class, $patternRepositoryMock);
        
        // Setup request with query params
        $this->requestMock->method('getQueryParams')->willReturn(['language' => 'de']);
        
        // Call the action
        $response = $this->controller->getPatternsAction($this->requestMock);
        
        // Assert response
        $responseData = json_decode($response->getBody()->getContents(), true);
        self::assertEquals(['ung', 'lich'], $responseData);
    }
} 
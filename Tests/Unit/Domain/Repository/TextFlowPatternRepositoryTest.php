<?php
declare(strict_types=1);
namespace PixelCoda\TextFlow\Tests\Unit\Domain\Repository;

use PixelCoda\TextFlow\Domain\Repository\TextFlowPatternRepository;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Expression\ExpressionBuilder;
use TYPO3\CMS\Core\Database\Query\Statement;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Test case for the TextFlowPatternRepository
 */
class TextFlowPatternRepositoryTest extends UnitTestCase
{
    /**
     * @var TextFlowPatternRepository
     */
    protected $repository;
    
    /**
     * @var ConnectionPool|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $connectionPoolMock;
    
    /**
     * @var QueryBuilder|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $queryBuilderMock;

    /**
     * Set up
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // Setup query builder mock
        $this->queryBuilderMock = $this->getMockBuilder(QueryBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
            
        $expressionBuilderMock = $this->getMockBuilder(ExpressionBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
            
        $statementMock = $this->getMockBuilder(Statement::class)
            ->disableOriginalConstructor()
            ->getMock();
            
        // Mock behavior for expression builder
        $expressionBuilderMock->method('eq')->willReturn('language = "de"');
        $this->queryBuilderMock->method('expr')->willReturn($expressionBuilderMock);
        $this->queryBuilderMock->method('createNamedParameter')->willReturn('"de"');
        
        // Mock behavior for query builder
        $this->queryBuilderMock->method('select')->willReturn($this->queryBuilderMock);
        $this->queryBuilderMock->method('from')->willReturn($this->queryBuilderMock);
        $this->queryBuilderMock->method('where')->willReturn($this->queryBuilderMock);
        $this->queryBuilderMock->method('executeQuery')->willReturn($statementMock);
        
        // Setup connection pool mock
        $this->connectionPoolMock = $this->getMockBuilder(ConnectionPool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->connectionPoolMock->method('getQueryBuilderForTable')->willReturn($this->queryBuilderMock);
        
        // Create repository with mocked dependencies
        $this->repository = new TextFlowPatternRepository();
    }

    /**
     * @test
     */
    public function findByLanguageReturnsEmptyArrayIfNoResultsFound(): void
    {
        // Mock fetchAllAssociative to return empty array
        $statementMock = $this->queryBuilderMock->executeQuery();
        $statementMock->method('fetchAllAssociative')->willReturn([]);
        
        GeneralUtility::addInstance(ConnectionPool::class, $this->connectionPoolMock);
        
        // Call findByLanguage and assert it returns empty array
        $result = $this->repository->findByLanguage('de');
        self::assertEquals([], $result);
    }

    /**
     * @test
     */
    public function findByLanguageReturnsArrayOfPatterns(): void
    {
        // Define mock result
        $mockPatterns = [
            ['uid' => 1, 'language' => 'de', 'pattern' => 'ung'],
            ['uid' => 2, 'language' => 'de', 'pattern' => 'lich']
        ];
        
        // Mock fetchAllAssociative to return patterns
        $statementMock = $this->queryBuilderMock->executeQuery();
        $statementMock->method('fetchAllAssociative')->willReturn($mockPatterns);
        
        GeneralUtility::addInstance(ConnectionPool::class, $this->connectionPoolMock);
        
        // Call findByLanguage and assert it returns patterns
        $result = $this->repository->findByLanguage('de');
        self::assertEquals($mockPatterns, $result);
    }
} 
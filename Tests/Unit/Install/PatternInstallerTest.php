<?php
declare(strict_types=1);

namespace PixelCoda\TextFlow\Tests\Unit\Install;

use PHPUnit\Framework\TestCase;
use PixelCoda\TextFlow\Install\PatternInstaller;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class PatternInstallerTest extends UnitTestCase
{
    protected PatternInstaller $subject;
    protected Connection $connectionMock;
    protected Logger $loggerMock;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock der Connection
        $this->connectionMock = $this->getMockBuilder(Connection::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Mock des ConnectionPools
        $connectionPoolMock = $this->getMockBuilder(ConnectionPool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $connectionPoolMock->method('getConnectionForTable')
            ->willReturn($this->connectionMock);

        // Mock des Loggers
        $this->loggerMock = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $logManagerMock = $this->getMockBuilder(LogManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logManagerMock->method('getLogger')
            ->willReturn($this->loggerMock);

        // Registriere die Mocks im GeneralUtility
        GeneralUtility::addInstance(ConnectionPool::class, $connectionPoolMock);
        GeneralUtility::addInstance(LogManager::class, $logManagerMock);

        $this->subject = new PatternInstaller();
    }

    protected function tearDown(): void
    {
        GeneralUtility::purgeInstances();
        parent::tearDown();
    }

    /**
     * @test
     */
    public function installPatternsForLanguageInsertsCorrectPatterns(): void
    {
        $language = 'de';
        $expectedInsertCount = 16; // Anzahl der deutschen Muster

        $this->connectionMock->expects(self::once())
            ->method('delete')
            ->with('tx_textflow_domain_model_textflowpattern', ['language' => $language]);

        $this->connectionMock->expects(self::exactly($expectedInsertCount))
            ->method('insert')
            ->with(
                'tx_textflow_domain_model_textflowpattern',
                self::callback(function ($data) use ($language) {
                    return $data['language'] === $language
                        && isset($data['pattern'])
                        && isset($data['pid'])
                        && isset($data['tstamp'])
                        && isset($data['crdate']);
                })
            );

        $this->subject->installPatternsForLanguage($language);
    }

    /**
     * @test
     */
    public function arePatternsInstalledForLanguageReturnsCorrectValue(): void
    {
        $language = 'de';

        $this->connectionMock->expects(self::once())
            ->method('count')
            ->with('*', 'tx_textflow_domain_model_textflowpattern', ['language' => $language])
            ->willReturn(5);

        $result = $this->subject->arePatternsInstalledForLanguage($language);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function installAllPatternsInstallsPatternsForAllLanguages(): void
    {
        $expectedLanguages = ['de', 'en', 'fr', 'es'];
        
        foreach ($expectedLanguages as $language) {
            $this->connectionMock->expects(self::atLeastOnce())
                ->method('delete')
                ->with('tx_textflow_domain_model_textflowpattern', ['language' => $language]);
        }

        $this->subject->installAllPatterns();
    }
} 
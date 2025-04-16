<?php
declare(strict_types=1);

namespace PixelCoda\TextFlow\Tests\Unit\Hooks;

use PixelCoda\TextFlow\Hooks\PatternInstallerHook;
use PixelCoda\TextFlow\Install\PatternInstaller;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class PatternInstallerHookTest extends UnitTestCase
{
    protected PatternInstallerHook $subject;
    protected PatternInstaller $installerMock;
    protected DataHandler $dataHandlerMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->installerMock = $this->getMockBuilder(PatternInstaller::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->dataHandlerMock = $this->getMockBuilder(DataHandler::class)
            ->disableOriginalConstructor()
            ->getMock();

        GeneralUtility::addInstance(PatternInstaller::class, $this->installerMock);

        $this->subject = new PatternInstallerHook();
    }

    protected function tearDown(): void
    {
        GeneralUtility::purgeInstances();
        parent::tearDown();
    }

    /**
     * @test
     */
    public function hookInstallsAllPatternsWhenLanguageIsAll(): void
    {
        $status = 'update';
        $table = 'tt_content';
        $id = '1';
        $fieldArray = [
            'enable_textflow' => 'all'
        ];

        $this->installerMock->expects(self::once())
            ->method('installAllPatterns');

        $this->subject->processDatamap_afterDatabaseOperations(
            $status,
            $table,
            $id,
            $fieldArray,
            $this->dataHandlerMock
        );
    }

    /**
     * @test
     */
    public function hookInstallsPatternsForSpecificLanguageWhenNotInstalled(): void
    {
        $status = 'update';
        $table = 'tt_content';
        $id = '1';
        $fieldArray = [
            'enable_textflow' => 'de'
        ];

        $this->installerMock->expects(self::once())
            ->method('arePatternsInstalledForLanguage')
            ->with('de')
            ->willReturn(false);

        $this->installerMock->expects(self::once())
            ->method('installPatternsForLanguage')
            ->with('de');

        $this->subject->processDatamap_afterDatabaseOperations(
            $status,
            $table,
            $id,
            $fieldArray,
            $this->dataHandlerMock
        );
    }

    /**
     * @test
     */
    public function hookDoesNotInstallPatternsForSpecificLanguageWhenAlreadyInstalled(): void
    {
        $status = 'update';
        $table = 'tt_content';
        $id = '1';
        $fieldArray = [
            'enable_textflow' => 'de'
        ];

        $this->installerMock->expects(self::once())
            ->method('arePatternsInstalledForLanguage')
            ->with('de')
            ->willReturn(true);

        $this->installerMock->expects(self::never())
            ->method('installPatternsForLanguage');

        $this->subject->processDatamap_afterDatabaseOperations(
            $status,
            $table,
            $id,
            $fieldArray,
            $this->dataHandlerMock
        );
    }

    /**
     * @test
     */
    public function hookIgnoresInvalidLanguages(): void
    {
        $status = 'update';
        $table = 'tt_content';
        $id = '1';
        $fieldArray = [
            'enable_textflow' => 'invalid_language'
        ];

        $this->installerMock->expects(self::never())
            ->method('arePatternsInstalledForLanguage');

        $this->installerMock->expects(self::never())
            ->method('installPatternsForLanguage');

        $this->subject->processDatamap_afterDatabaseOperations(
            $status,
            $table,
            $id,
            $fieldArray,
            $this->dataHandlerMock
        );
    }
} 
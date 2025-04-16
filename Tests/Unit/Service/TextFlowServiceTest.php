<?php
declare(strict_types=1);
namespace PixelCoda\TextFlow\Tests\Unit\Service;

use PixelCoda\TextFlow\Domain\Model\TextFlowPattern;
use PixelCoda\TextFlow\Domain\Repository\TextFlowPatternRepository;
use PixelCoda\TextFlow\Service\TextFlowService;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use Psr\Log\LoggerInterface;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogManager;

/**
 * Unit tests for TextFlowService.
 */
class TextFlowServiceTest extends UnitTestCase
{
    protected bool $resetSingletonInstances = true;
    protected TextFlowService $textFlowService;
    protected TextFlowPatternRepository $patternRepository;
    protected LoggerInterface $loggerMock;
    protected Site $siteMock;
    protected SiteLanguage $siteLanguageMock;
    protected Logger $logger;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->patternRepository = $this->createMock(TextFlowPatternRepository::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->siteMock = $this->createMock(Site::class);
        $this->siteLanguageMock = $this->createMock(SiteLanguage::class);
        
        $patterns = [
            ['pattern' => 'Ent'],
            ['pattern' => 'Kon'],
            ['pattern' => 'Con']
        ];
        
        $this->patternRepository->method('findByLanguage')
            ->willReturn($patterns);
        
        $this->textFlowService = new TextFlowService(
            $this->patternRepository,
            $this->loggerMock
        );

        $this->logger = $this->createMock(Logger::class);
        
        $logManager = $this->createMock(LogManager::class);
        $logManager->method('getLogger')->willReturn($this->logger);
    }

    /**
     * @test
     */
    public function hyphenateAddsSoftHyphenForGermanWord(): void
    {
        $text = 'Konfiguration';
        $result = $this->textFlowService->hyphenate($text, ['enable_textflow' => 'de']);
        self::assertEquals('Kon­figuration', $result);
    }

    /**
     * @test
     */
    public function hyphenateAddsSoftHyphenForEnglishWord(): void
    {
        $text = 'Condition';
        $result = $this->textFlowService->hyphenate($text, ['enable_textflow' => 'en']);
        self::assertEquals('Con­dition', $result);
    }

    /**
     * @test
     */
    public function hyphenateSkipsShortWords(): void
    {
        $text = 'an zu';
        $result = $this->textFlowService->hyphenate($text, ['enable_textflow' => 'de']);
        self::assertEquals($text, $result);
    }

    /**
     * @test
     */
    public function hyphenateReturnsUnmodifiedTextWhenHyphenationIsDisabled(): void
    {
        $text = 'This is a test text without hyphenation';
        $result = $this->textFlowService->hyphenate($text, ['enable_textflow' => 'none']);
        self::assertEquals($text, $result);
    }

    /**
     * @test
     */
    public function hyphenateHandlesEmptyInput(): void
    {
        $text = '';
        $result = $this->textFlowService->hyphenate($text, ['enable_textflow' => 'de']);
        self::assertEquals('', $result);
    }

    /**
     * @test
     */
    public function hyphenateHandlesHtmlContent(): void
    {
        $inputText = '<p>Dies ist ein <strong>Beispieltext</strong></p>';
        $patterns = [
            'bei' => 'be-i',
            'spiel' => 'spi-el'
        ];

        $this->patternRepository->expects(self::once())
            ->method('findByLanguage')
            ->with('de')
            ->willReturn($patterns);

        $this->siteLanguageMock->method('getTwoLetterIsoCode')
            ->willReturn('de');

        $result = $this->textFlowService->hyphenate($inputText);
        self::assertEquals('<p>Dies ist ein <strong>Bei­spiel­text</strong></p>', $result);
    }

    /**
     * @test
     */
    public function hyphenateHandlesMultipleWords(): void
    {
        $inputText = 'Beispiel Beispieltext';
        $patterns = [
            'bei' => 'be-i',
            'spiel' => 'spi-el'
        ];

        $this->patternRepository->expects(self::once())
            ->method('findByLanguage')
            ->with('de')
            ->willReturn($patterns);

        $this->siteLanguageMock->method('getTwoLetterIsoCode')
            ->willReturn('de');

        $result = $this->textFlowService->hyphenate($inputText);
        self::assertEquals('Bei­spiel Bei­spiel­text', $result);
    }

    /**
     * @test
     */
    public function hyphenateHandlesCustomContentObject(): void
    {
        $inputText = 'Beispieltext';
        $contentObject = [
            'language' => 'en',
            'text' => $inputText
        ];
        $patterns = [
            'example' => 'ex-am-ple'
        ];

        $this->patternRepository->expects(self::once())
            ->method('findByLanguage')
            ->with('en')
            ->willReturn($patterns);

        $result = $this->textFlowService->hyphenate($inputText, $contentObject);
        self::assertEquals('Bei­spiel­text', $result);
    }

    /**
     * @test
     */
    public function hyphenateLogsDebugInformation(): void
    {
        $inputText = 'Beispieltext';
        $patterns = [
            'bei' => 'be-i',
            'spiel' => 'spi-el'
        ];

        $this->patternRepository->method('findByLanguage')
            ->willReturn($patterns);

        $this->siteLanguageMock->method('getTwoLetterIsoCode')
            ->willReturn('de');

        $this->loggerMock->expects(self::once())
            ->method('debug')
            ->with(
                'TextFlow Service: Processing text',
                [
                    'originalText' => $inputText,
                    'patterns' => $patterns,
                    'language' => 'de'
                ]
            );

        $this->textFlowService->hyphenate($inputText);
    }

    /**
     * @test
     */
    public function getCurrentLanguageReturnsDefaultLanguageIfNotSet(): void
    {
        $this->siteLanguageMock->method('getTwoLetterIsoCode')
            ->willReturn(null);

        $result = $this->textFlowService->getCurrentLanguage();
        self::assertEquals('de', $result);
    }

    /**
     * @test
     */
    public function buildPatternsReturnsEmptyArrayForUnknownLanguage(): void
    {
        $this->patternRepository->expects(self::once())
            ->method('findByLanguage')
            ->with('fr')
            ->willReturn([]);

        $result = $this->textFlowService->buildPatterns('fr');
        self::assertEmpty($result);
    }

    /**
     * @test
     */
    public function buildPatternsLogsErrorForInvalidPattern(): void
    {
        $invalidPattern = 'invalid-pattern';

        $this->patternRepository->expects(self::once())
            ->method('findByLanguage')
            ->with('de')
            ->willReturn([$invalidPattern]);

        $this->loggerMock->expects(self::once())
            ->method('error')
            ->with(
                'TextFlow Service: Invalid pattern format',
                ['pattern' => $invalidPattern]
            );

        $result = $this->textFlowService->buildPatterns('de');
        self::assertEmpty($result);
    }

    /**
     * @test
     */
    public function hyphenateHandlesMultipleHyphensInWord(): void
    {
        $text = 'Entwicklungsumgebung';
        $patterns = [
            ['pattern' => 'Ent'],
            ['pattern' => 'wick'],
            ['pattern' => 'lung']
        ];
        
        $this->patternRepository->method('findByLanguage')
            ->willReturn($patterns);
        
        $result = $this->textFlowService->hyphenate($text, ['enable_textflow' => 'de']);
        self::assertEquals('Ent­wick­lungs­um­gebung', $result);
    }

    /**
     * @test
     */
    public function hyphenatePreservesCase(): void
    {
        $patterns = [
            ['pattern' => 'pro'],
            ['pattern' => 'gram'],
            ['pattern' => 'mie'],
            ['pattern' => 'rung']
        ];

        $this->patternRepository->method('findByLanguage')
            ->with('de')
            ->willReturn($patterns);

        $result = $this->textFlowService->hyphenate('PROGRAMMIERUNG');
        self::assertEquals('PRO­GRAM­MIE­RUNG', $result);

        $result = $this->textFlowService->hyphenate('Programmierung');
        self::assertEquals('Pro­gram­mie­rung', $result);
    }

    /**
     * @test
     */
    public function buildPatternsReturnsCorrectStructure(): void
    {
        $patterns = [
            ['pattern' => 'ent'],
            ['pattern' => 'wick'],
            ['pattern' => 'lung']
        ];

        $result = $this->textFlowService->buildPatterns($patterns);
        
        self::assertIsArray($result);
        self::assertArrayHasKey(3, $result);
        self::assertArrayHasKey(4, $result);
        self::assertContains('ent', $result[3]);
        self::assertContains('wick', $result[4]);
        self::assertContains('lung', $result[4]);
    }

    /**
     * @test
     */
    public function buildPatternsHandlesInvalidPatterns(): void
    {
        $patterns = [
            ['pattern' => ''],
            ['pattern' => null],
            ['invalid_key' => 'test']
        ];

        $this->logger->expects($this->exactly(3))
            ->method('error')
            ->with('TextFlow Service: Invalid pattern format');

        $result = $this->textFlowService->buildPatterns($patterns);
        self::assertEmpty($result);
    }
}
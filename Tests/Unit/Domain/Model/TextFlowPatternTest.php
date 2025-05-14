<?php
declare(strict_types=1);
namespace PixelCoda\TextFlow\Tests\Unit\Domain\Model;

use PixelCoda\TextFlow\Domain\Model\TextFlowPattern;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Test case for the TextFlowPattern model
 */
class TextFlowPatternTest extends UnitTestCase
{
    /**
     * @var TextFlowPattern
     */
    protected $subject;

    /**
     * Set up
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->subject = new TextFlowPattern();
    }

    /**
     * @test
     */
    public function getLanguageReturnsInitialValueForString(): void
    {
        self::assertSame('', $this->subject->getLanguage());
    }

    /**
     * @test
     */
    public function setLanguageSetsLanguage(): void
    {
        $language = 'de';
        $this->subject->setLanguage($language);
        self::assertEquals($language, $this->subject->getLanguage());
    }

    /**
     * @test
     */
    public function getPatternReturnsInitialValueForString(): void
    {
        self::assertSame('', $this->subject->getPattern());
    }

    /**
     * @test
     */
    public function setPatternSetsPattern(): void
    {
        $pattern = 'ung';
        $this->subject->setPattern($pattern);
        self::assertEquals($pattern, $this->subject->getPattern());
    }
}

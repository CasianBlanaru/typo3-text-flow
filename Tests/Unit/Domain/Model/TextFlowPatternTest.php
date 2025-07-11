<?php
declare(strict_types=1);
namespace Tpwdag\TextFlow\Tests\Unit\Domain\Model;

use Tpwdag\TextFlow\Domain\Model\TextFlowPattern;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Unit tests for TextFlowPattern.
 */
class TextFlowPatternTest extends UnitTestCase
{
    protected TextFlowPattern $subject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->subject = new TextFlowPattern();
    }

    /**
     * @test
     */
    public function getLanguageReturnsInitialValue(): void
    {
        $this->assertSame('', $this->subject->getLanguage());
    }

    /**
     * @test
     */
    public function setLanguageSetsValue(): void
    {
        $this->subject->setLanguage('de');
        $this->assertSame('de', $this->subject->getLanguage());
    }

    /**
     * @test
     */
    public function getPatternReturnsInitialValue(): void
    {
        $this->assertSame('', $this->subject->getPattern());
    }

    /**
     * @test
     */
    public function setPatternSetsValue(): void
    {
        $this->subject->setPattern('kon');
        $this->assertSame('kon', $this->subject->getPattern());
    }
}

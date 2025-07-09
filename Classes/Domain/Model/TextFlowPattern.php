<?php
declare(strict_types=1);
namespace Tpwd\TextFlow\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Model for hyphenation patterns.
 *
 * @TYPO3\CMS\Extbase\Annotation\Entity
 * @TYPO3\CMS\Extbase\Annotation\Mapping\Table("tx_textflow_domain_model_textflowpattern")
 */
class TextFlowPattern extends AbstractEntity
{
    /**
     * @var string
     */
    protected string $language = '';

    /**
     * @var string
     */
    protected string $pattern = '';

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function setLanguage(string $language): void
    {
        $this->language = $language;
    }

    public function getPattern(): string
    {
        return $this->pattern;
    }

    public function setPattern(string $pattern): void
    {
        $this->pattern = $pattern;
    }
}
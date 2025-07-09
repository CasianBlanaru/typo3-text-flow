<?php
declare(strict_types=1);
namespace Tpwdag\TextFlow\Domain\Repository;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Repository;
use TYPO3\CMS\Core\Database\ConnectionPool;

/**
 * Repository for TextFlowPattern.
 */
class TextFlowPatternRepository extends Repository
{
    /**
     * Finds patterns by language.
     *
     * @param string $language The language code
     * @return array
     */
    public function findByLanguage(string $language): array
    {
        // Direct database access for better performance
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tx_textflow_domain_model_textflowpattern');

        $result = $queryBuilder
            ->select('*')
            ->from('tx_textflow_domain_model_textflowpattern')
            ->where(
                $queryBuilder->expr()->eq('language', $queryBuilder->createNamedParameter($language))
            )
            ->executeQuery()
            ->fetchAllAssociative();

        return $result;
    }
}
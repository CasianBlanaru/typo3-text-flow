<?php
declare(strict_types=1);
namespace Tpwdag\TextFlow\Install;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Log\LogManager;

class PatternInstaller
{
    protected $logger;
    protected $connection;

    public function __construct()
    {
        $this->logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
        $this->connection = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('tx_textflow_domain_model_textflowpattern');
    }

    /**
     * Installs hyphenation patterns for all languages
     */
    public function installAllPatterns(): void
    {
        $patterns = require dirname(__DIR__, 2) . '/Configuration/Patterns/patterns.php';
        
        foreach ($patterns as $language => $languagePatterns) {
            $this->installPatternsForLanguage($language);
        }
    }

    /**
     * Installs hyphenation patterns for a specific language
     */
    public function installPatternsForLanguage(string $language): void
    {
        $patterns = require dirname(__DIR__, 2) . '/Configuration/Patterns/patterns.php';
        
        if (!isset($patterns[$language])) {
            $this->logger->warning("No patterns found for language: {$language}");
            return;
        }

        try {
            // Delete existing patterns for this language
            $this->connection->delete(
                'tx_textflow_domain_model_textflowpattern',
                ['language' => $language]
            );

            // Add new patterns
            foreach ($patterns[$language] as $pattern) {
                $this->connection->insert(
                    'tx_textflow_domain_model_textflowpattern',
                    [
                        'language' => $language,
                        'pattern' => $pattern['pattern'],
                        'pid' => 0,
                        'tstamp' => time(),
                        'crdate' => time(),
                    ]
                );
            }

            $this->logger->info("Successfully installed {count} patterns for language {language}", [
                'count' => count($patterns[$language]),
                'language' => $language
            ]);
        } catch (\Exception $e) {
            $this->logger->error("Error installing patterns for language {language}: {message}", [
                'language' => $language,
                'message' => $e->getMessage(),
                'exception' => $e
            ]);
        }
    }

    /**
     * Checks if patterns are installed for a language
     */
    public function arePatternsInstalledForLanguage(string $language): bool
    {
        $count = $this->connection->count(
            '*',
            'tx_textflow_domain_model_textflowpattern',
            ['language' => $language]
        );

        return $count > 0;
    }
} 
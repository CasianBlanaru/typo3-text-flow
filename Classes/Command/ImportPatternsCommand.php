<?php
declare(strict_types=1);

namespace Tpwd\TextFlow\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Core\Environment;

/**
 * Command to import additional language patterns
 */
class ImportPatternsCommand extends Command
{
    protected function configure(): void
    {
        $this->setDescription('Imports additional language patterns for TextFlow');
        $this->setHelp('This command imports additional language patterns (fr, es, it, nl) for TextFlow');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('TextFlow Language Pattern Import');

        $sqlFilePath = Environment::getPublicPath() . '/typo3conf/ext/text_flow/Resources/Private/Sql/AdditionalLanguagePatterns.sql';

        // If the extension is installed via composer
        if (!file_exists($sqlFilePath)) {
            $sqlFilePath = GeneralUtility::getFileAbsFileName('EXT:text_flow/Resources/Private/Sql/AdditionalLanguagePatterns.sql');
        }

        if (!file_exists($sqlFilePath)) {
            $io->error('Could not find SQL file with patterns at: ' . $sqlFilePath);
            return Command::FAILURE;
        }

        $sqlContent = file_get_contents($sqlFilePath);
        if (!$sqlContent) {
            $io->error('Could not read SQL file content');
            return Command::FAILURE;
        }

        // Extract SQL statements
        $statements = $this->extractSqlStatements($sqlContent);
        if (empty($statements)) {
            $io->error('No valid SQL statements found in file');
            return Command::FAILURE;
        }

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('tx_textflow_domain_model_textflowpattern');

        $languageCounts = [];
        $totalImported = 0;

        // Begin transaction
        $connection->beginTransaction();

        try {
            // First check if patterns already exist
            $existingPatterns = $connection->executeQuery(
                'SELECT DISTINCT language FROM tx_textflow_domain_model_textflowpattern'
            )->fetchFirstColumn();

            $io->section('Existing languages: ' . implode(', ', $existingPatterns));

            // Process each INSERT statement
            foreach ($statements as $insertGroup) {
                // Extract language and patterns from SQL statement
                preg_match('/\'([a-z]{2})\'/', $insertGroup, $matches);
                if (empty($matches[1])) {
                    continue;
                }

                $language = $matches[1];

                // Skip if we already have this language
                if (in_array($language, $existingPatterns, true)) {
                    $io->info('Skipping language ' . $language . ' (already exists)');
                    continue;
                }

                // Extract each value set
                preg_match_all('/\(0, \'([a-z]{2})\', \'([^\']+)\'\)/', $insertGroup, $valueMatches, PREG_SET_ORDER);

                foreach ($valueMatches as $valueMatch) {
                    $lang = $valueMatch[1];
                    $pattern = $valueMatch[2];

                    // Insert new pattern
                    $connection->insert(
                        'tx_textflow_domain_model_textflowpattern',
                        [
                            'pid' => 0,
                            'language' => $lang,
                            'pattern' => $pattern,
                            'tstamp' => time(),
                            'crdate' => time(),
                        ]
                    );

                    $languageCounts[$lang] = ($languageCounts[$lang] ?? 0) + 1;
                    $totalImported++;
                }
            }

            // Commit transaction
            $connection->commit();

            // Output results
            $io->success('Successfully imported ' . $totalImported . ' language patterns');
            foreach ($languageCounts as $lang => $count) {
                $io->writeln(' - ' . $lang . ': ' . $count . ' patterns');
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            // Rollback on error
            $connection->rollBack();
            $io->error('Error during import: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Extract SQL statements from SQL content
     *
     * @param string $sqlContent
     * @return array
     */
    private function extractSqlStatements(string $sqlContent): array
    {
        // Remove comments
        $sqlContent = preg_replace('/--.*$/m', '', $sqlContent);

        // Split by INSERT INTO blocks
        $blocks = [];
        preg_match_all('/INSERT INTO.*?VALUES(.*?);/s', $sqlContent, $matches);

        if (!empty($matches[0])) {
            return $matches[0];
        }

        return [];
    }
}

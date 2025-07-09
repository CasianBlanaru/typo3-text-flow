<?php
declare(strict_types=1);
namespace Tpwd\TextFlow\Hooks;

use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Tpwd\TextFlow\Install\PatternInstaller;

class PatternInstallerHook
{
    /**
     * Hook called when content elements are saved
     */
    public function processDatamap_afterDatabaseOperations(
        string $status,
        string $table,
        $id,
        array $fieldArray,
        DataHandler $dataHandler
    ): void {
        // Check if this is a content element
        if ($table === 'tt_content' && isset($fieldArray['enable_textflow'])) {
            $language = $fieldArray['enable_textflow'];
            
            // Install all languages if 'all' is selected
            if ($language === 'all') {
                $installer = GeneralUtility::makeInstance(PatternInstaller::class);
                $installer->installAllPatterns();
                return;
            }
            
            // For specific language
            if (in_array($language, ['de', 'en', 'fr', 'es'])) {
                $installer = GeneralUtility::makeInstance(PatternInstaller::class);
                if (!$installer->arePatternsInstalledForLanguage($language)) {
                    $installer->installPatternsForLanguage($language);
                }
            }
        }
    }
} 
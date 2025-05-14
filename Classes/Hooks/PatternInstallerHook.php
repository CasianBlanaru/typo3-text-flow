<?php
declare(strict_types=1);
namespace PixelCoda\TextFlow\Hooks;

use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use PixelCoda\TextFlow\Install\PatternInstaller;

class PatternInstallerHook
{
    /**
     * Hook wird aufgerufen, wenn Inhaltselemente gespeichert werden
     */
    public function processDatamap_afterDatabaseOperations(
        string $status,
        string $table,
        $id,
        array $fieldArray,
        DataHandler $dataHandler
    ): void {
        // Prüfe, ob es sich um ein Content-Element handelt
        if ($table === 'tt_content' && isset($fieldArray['enable_textflow'])) {
            $language = $fieldArray['enable_textflow'];
            
            // Wenn 'all' ausgewählt wurde, installiere alle Sprachen
            if ($language === 'all') {
                $installer = GeneralUtility::makeInstance(PatternInstaller::class);
                $installer->installAllPatterns();
                return;
            }
            
            // Für spezifische Sprache
            if (in_array($language, ['de', 'en', 'fr', 'es'])) {
                $installer = GeneralUtility::makeInstance(PatternInstaller::class);
                if (!$installer->arePatternsInstalledForLanguage($language)) {
                    $installer->installPatternsForLanguage($language);
                }
            }
        }
    }
} 
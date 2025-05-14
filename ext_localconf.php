<?php
declare(strict_types=1);
defined('TYPO3') or die();

// Register namespace for ViewHelpers
if (!isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['textflow'])) {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['textflow'] = [];
}
$GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['textflow'][] = 'PixelCoda\\TextFlow\\ViewHelpers';

// Register pattern installer for extension installation
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['textFlowPatterns'] = \PixelCoda\TextFlow\Install\PatternInstaller::class;

// Register hook for automatic installation of missing patterns
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['text_flow'] = 
    \PixelCoda\TextFlow\Hooks\PatternInstallerHook::class;

(function () {
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'TextFlow',
        'TextOptimizer',
        [
            \PixelCoda\TextFlow\Controller\TextFlowController::class => 'optimize,show'
        ],
        [
            \PixelCoda\TextFlow\Controller\TextFlowController::class => 'optimize'
        ]
    );

    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'TextFlow',
        'Show',
        [
            \PixelCoda\TextFlow\Controller\TextFlowController::class => 'show'
        ],
        []
    );

    // Register cache for TextFlow
    if (!is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['tx_textflow_hyphenation'] ?? null)) {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['tx_textflow_hyphenation'] = [
            'frontend' => \TYPO3\CMS\Core\Cache\Frontend\VariableFrontend::class,
            'backend' => \TYPO3\CMS\Core\Cache\Backend\SimpleFileBackend::class,
            'options' => [
                'defaultLifetime' => 86400 // 24 hours
            ],
            'groups' => ['pages', 'all']
        ];
    }

    // Register TextFlow icons
    $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
        \TYPO3\CMS\Core\Imaging\IconRegistry::class
    );
    $iconRegistry->registerIcon(
        'extension-textflow',
        \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        ['source' => 'EXT:text_flow/Resources/Public/Icons/Extension.svg']
    );
    $iconRegistry->registerIcon(
        'plugin-textflow-textoptimizer',
        \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        ['source' => 'EXT:text_flow/Resources/Public/Icons/TextOptimizer.svg']
    );

    // Register hooks for content processing
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_content.php']['stdWrap']['tx_textflow'] = 
        \PixelCoda\TextFlow\Hooks\ContentObjectRendererHook::class . '->processStdWrap';
})();
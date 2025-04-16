<?php
declare(strict_types=1);
defined('TYPO3') or die();

(static function () {
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'PixelCoda.TextFlow',
        'Show',
        [
            \PixelCoda\TextFlow\Controller\TextFlowController::class => 'show'
        ],
        [
            \PixelCoda\TextFlow\Controller\TextFlowController::class => 'show'
        ]
    );

    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'PixelCoda.TextFlow',
        'TextOptimizer',
        [
            \PixelCoda\TextFlow\Controller\TextFlowController::class => 'optimize'
        ],
        [
            \PixelCoda\TextFlow\Controller\TextFlowController::class => 'optimize'
        ]
    );

    // Register content element icons
    $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
        \TYPO3\CMS\Core\Imaging\IconRegistry::class
    );
    $iconRegistry->registerIcon(
        'text-flow-icon',
        \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        ['source' => 'EXT:text_flow/Resources/Public/Icons/Extension.svg']
    );

    // Register for hook to show preview of tt_content element
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['tt_content_drawItem']['text_flow'] =
        \PixelCoda\TextFlow\Hooks\PageLayoutView\TextPreviewRenderer::class;

    // Register namespace for ViewHelpers
    if (!isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['textflow'])) {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['textflow'] = [];
    }
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['textflow'][] = 'PixelCoda\\TextFlow\\ViewHelpers';

    // Register the stdWrap hook for text processing
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_content.php']['stdWrap_postProcess'][\PixelCoda\TextFlow\Hooks\ContentObjectRendererHook::class] = \PixelCoda\TextFlow\Hooks\ContentObjectRendererHook::class;

    // Registriere den Pattern-Installer für die Extension-Installation
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['textFlowPatterns'] = \PixelCoda\TextFlow\Install\PatternInstaller::class;

    // Registriere einen Hook für die automatische Installation fehlender Muster
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['text_flow'] = 
        \PixelCoda\TextFlow\Hooks\PatternInstallerHook::class;
})();
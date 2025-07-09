<?php
declare(strict_types=1);
defined('TYPO3') or die();

// Register namespace for ViewHelpers
if (!isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['textflow'])) {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['textflow'] = [];
}
$GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['textflow'][] = 'Tpwdag\\TextFlow\\ViewHelpers';

// Register the stdWrap hook for text processing
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_content.php']['stdWrap_postProcess'][\Tpwdag\TextFlow\Hooks\ContentObjectRendererHook::class] = \Tpwdag\TextFlow\Hooks\ContentObjectRendererHook::class;

// Register the pattern installer for extension installation
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['textFlowPatterns'] = \Tpwdag\TextFlow\Install\PatternInstaller::class;

// Register hook for automatic installation of missing patterns
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['text_flow'] =
    \Tpwdag\TextFlow\Hooks\PatternInstallerHook::class;

// Direct content element rendering hook
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_content.php']['cObjTypeAndClass']['text'] = [
    'text',
    \Tpwdag\TextFlow\Hooks\ContentRenderingHook::class
];

// TYPO3 parseFunc hook for RTE content
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_parsehtml.php']['postTransformTagsHook']['text_flow'] =
    \Tpwdag\TextFlow\Hooks\TextContentHook::class . '->processContent';

<?php
declare(strict_types=1);
defined('TYPO3') or die();

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

// Direkter Content Element Rendering Hook
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_content.php']['cObjTypeAndClass']['text'] = [
    'text',
    \PixelCoda\TextFlow\Hooks\ContentRenderingHook::class
];

// TYPO3 parseFunc Hook für RTE-Content
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_parsehtml.php']['postTransformTagsHook']['text_flow'] =
    \PixelCoda\TextFlow\Hooks\TextContentHook::class . '->processContent';

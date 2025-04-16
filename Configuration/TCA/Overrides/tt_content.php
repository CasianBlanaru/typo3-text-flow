<?php
declare(strict_types=1);
defined('TYPO3') or die();

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

// Configure the field
$tempColumns = [
    'enable_textflow' => [
        'exclude' => true,
        'label' => 'LLL:EXT:text_flow/Resources/Private/Language/locallang.xlf:enable_textflow',
        'config' => [
            'type' => 'select',
            'renderType' => 'selectSingle',
            'items' => [
                ['LLL:EXT:text_flow/Resources/Private/Language/locallang.xlf:enable_textflow.all', 'all'],
                ['LLL:EXT:text_flow/Resources/Private/Language/locallang.xlf:enable_textflow.de', 'de'],
                ['LLL:EXT:text_flow/Resources/Private/Language/locallang.xlf:enable_textflow.en', 'en'],
                ['LLL:EXT:text_flow/Resources/Private/Language/locallang.xlf:enable_textflow.none', 'none'],
            ],
            'default' => 'all',
        ],
    ],
];

ExtensionManagementUtility::addTCAcolumns('tt_content', $tempColumns);
ExtensionManagementUtility::addToAllTCAtypes('tt_content', 'enable_textflow', '', 'after:bodytext');

// Register Plugin
ExtensionUtility::registerPlugin(
    'PixelCoda.TextFlow',
    'Show',
    'Text Flow Plugin',
    'text-flow-icon'
);

// Configure Plugin
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['textflow_show'] = 'layout,select_key,pages,recursive';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['textflow_show'] = 'pi_flexform';

// Add FlexForm
ExtensionManagementUtility::addPiFlexFormValue(
    'textflow_show',
    'FILE:EXT:text_flow/Configuration/FlexForms/flexform.xml'
);

// Configure the plugin fields
$GLOBALS['TCA']['tt_content']['types']['textflow_show'] = [
    'showitem' => '
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
            --palette--;;general,
            header,
            bodytext;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:bodytext_formlabel,
            pi_flexform,
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
            --palette--;;hidden,
            --palette--;;access,
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:categories,
            categories,
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:notes,
            rowDescription,
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:extended,
    ',
    'columnsOverrides' => [
        'bodytext' => [
            'config' => [
                'enableRichtext' => true,
                'richtextConfiguration' => 'default'
            ]
        ]
    ]
];

// Add Content Element
ExtensionManagementUtility::addTcaSelectItem(
    'tt_content',
    'CType',
    [
        'TextFlow',
        'textflow',
        'content-text',
    ],
    'text',
    'after'
);

$GLOBALS['TCA']['tt_content']['types']['textflow'] = [
    'showitem' => '
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
            --palette--;;general,
            --palette--;;headers,
            bodytext;Text,
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,
            --palette--;;language,
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
            --palette--;;hidden,
            --palette--;;access,
    ',
    'columnsOverrides' => [
        'bodytext' => [
            'config' => [
                'enableRichtext' => true,
                'richtextConfiguration' => 'default',
                'eval' => 'trim',
                'fieldControl' => [
                    'fullScreenRichtext' => [
                        'disabled' => false,
                    ],
                ],
            ],
        ],
    ],
];

// Register Text Optimizer Plugin
ExtensionUtility::registerPlugin(
    'PixelCoda.TextFlow',
    'TextOptimizer',
    'Text Flow Optimizer',
    'text-flow-icon'
);

// Configure Text Optimizer Plugin
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['textflow_textoptimizer'] = 'layout,select_key,pages,recursive';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['textflow_textoptimizer'] = 'pi_flexform';

// Add FlexForm for Text Optimizer
ExtensionManagementUtility::addPiFlexFormValue(
    'textflow_textoptimizer',
    'FILE:EXT:text_flow/Configuration/FlexForms/flexform.xml'
);
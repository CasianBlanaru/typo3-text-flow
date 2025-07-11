<?php
declare(strict_types=1);
defined('TYPO3') or die();

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

// Configure the field
$tempColumns = [
    'enable_textflow' => [
        'exclude' => true,
        'label' => 'LLL:EXT:text_flow/Resources/Private/Language/locallang.xlf:enable_textflow',
        'description' => 'Aktiviert die TextFlow-Silbentrennung für dieses Content-Element',
        'config' => [
            'type' => 'select',
            'renderType' => 'selectSingle',
            'items' => [
                ['LLL:EXT:text_flow/Resources/Private/Language/locallang.xlf:enable_textflow.none', 'none'],
                ['LLL:EXT:text_flow/Resources/Private/Language/locallang.xlf:enable_textflow.all', 'all'],
                ['LLL:EXT:text_flow/Resources/Private/Language/locallang.xlf:enable_textflow.de', 'de'],
                ['LLL:EXT:text_flow/Resources/Private/Language/locallang.xlf:enable_textflow.en', 'en'],
                ['LLL:EXT:text_flow/Resources/Private/Language/locallang.xlf:enable_textflow.fr', 'fr'],
                ['LLL:EXT:text_flow/Resources/Private/Language/locallang.xlf:enable_textflow.es', 'es'],
                ['LLL:EXT:text_flow/Resources/Private/Language/locallang.xlf:enable_textflow.it', 'it'],
                ['LLL:EXT:text_flow/Resources/Private/Language/locallang.xlf:enable_textflow.nl', 'nl'],
                ['LLL:EXT:text_flow/Resources/Private/Language/locallang.xlf:enable_textflow.pt', 'pt'],
                ['LLL:EXT:text_flow/Resources/Private/Language/locallang.xlf:enable_textflow.zh', 'zh'],
                ['LLL:EXT:text_flow/Resources/Private/Language/locallang.xlf:enable_textflow.ar', 'ar'],
                ['LLL:EXT:text_flow/Resources/Private/Language/locallang.xlf:enable_textflow.hi', 'hi'],
            ],
            'default' => 'none',
            'size' => 1,
            'maxitems' => 1,
        ],
    ],
    // Legacy field, now replaced by enable_textflow select field
];

ExtensionManagementUtility::addTCAcolumns('tt_content', $tempColumns);

// Add to all content elements with bodytext
ExtensionManagementUtility::addToAllTCAtypes(
    'tt_content',
    'enable_textflow',
    'text,textpic,textmedia,bullets,table,header,menu,list,mask_*',
    'after:bodytext'
);

// Configure Plugin
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['textflow_show'] = 'layout,select_key,pages,recursive';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['textflow_textoptimizer'] = 'layout,select_key,pages,recursive';

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
            bodytext;LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.text,
            enable_textflow,
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
            --palette--;;hidden,
            --palette--;;access,
    ',
    'columnsOverrides' => [
        'bodytext' => [
            'config' => [
                'enableRichtext' => true,
            ],
        ],
    ],
];

// Add FlexForm
ExtensionManagementUtility::addPiFlexFormValue(
    'textflow_show',
    'FILE:EXT:text_flow/Configuration/FlexForms/flexform.xml'
);

// Add FlexForm for Text Optimizer
ExtensionManagementUtility::addPiFlexFormValue(
    'textflow_textoptimizer',
    'FILE:EXT:text_flow/Configuration/FlexForms/flexform.xml'
);

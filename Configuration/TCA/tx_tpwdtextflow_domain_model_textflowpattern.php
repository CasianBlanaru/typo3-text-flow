<?php
declare(strict_types=1);
return [
    'ctrl' => [
        'title' => 'LLL:EXT:tpwd_textflow/Resources/Private/Language/locallang.xlf:textflow_pattern',
        'label' => 'pattern',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
        ],
        'searchFields' => 'language,pattern',
        'iconfile' => 'EXT:tpwd_textflow/Resources/Public/Icons/Extension.svg',
    ],
    'columns' => [
        'hidden' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.enabled',
            'config' => [
                'type' => 'check',
                'renderType' => 'checkboxToggle',
                'default' => 0,
            ],
        ],
        'language' => [
            'exclude' => true,
            'label' => 'LLL:EXT:tpwd_textflow/Resources/Private/Language/locallang.xlf:language',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim,required',
            ],
        ],
        'pattern' => [
            'exclude' => true,
            'label' => 'LLL:EXT:tpwd_textflow/Resources/Private/Language/locallang.xlf:pattern',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim,required',
            ],
        ],
    ],
    'types' => [
        '1' => ['showitem' => 'hidden, language, pattern'],
    ],
]; 
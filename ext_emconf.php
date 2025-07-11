<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Text Flow',
    'description' => 'Optimizes text flow with dynamic hyphenation for multiple languages (DE, EN, FR, ES, IT, NL)',
    'category' => 'fe',
    'author' => 'TPWD AG',
    'author_email' => 'cab@tpwd.de',
    'state' => 'stable',
    'version' => '1.1.1',
    'constraints' => [
        'depends' => [
            'typo3' => '12.0.0-12.4.99',
            'fluid' => '12.0.0-12.4.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];

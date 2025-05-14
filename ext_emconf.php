<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Text Flow',
    'description' => 'Optimizes text flow with dynamic hyphenation',
    'category' => 'fe',
    'author' => 'Casian Blanaru',
    'author_email' => 'casian@pixelcoda.com',
    'state' => 'stable',
    'version' => '1.0.22',
    'constraints' => [
        'depends' => [
            'typo3' => '12.0.0-12.4.99',
            'fluid' => '12.0.0-12.4.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Text Flow',
    'description' => 'Optimizes text flow with dynamic hyphenation',
    'category' => 'plugin',
    'author' => 'Casian Blanaru',
    'author_email' => 'casian@pixelcoda.com',
    'state' => 'stable',
    'clearCacheOnLoad' => true,
    'version' => '1.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '12.4.0-12.4.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
<?php
declare(strict_types=1);

return [
    \PixelCoda\TextFlow\Domain\Model\TextFlowPattern::class => [
        'tableName' => 'tx_textflow_domain_model_textflowpattern',
        'properties' => [
            'language' => [
                'fieldName' => 'language'
            ],
            'pattern' => [
                'fieldName' => 'pattern'
            ]
        ]
    ]
]; 
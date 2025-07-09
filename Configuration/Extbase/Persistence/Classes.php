<?php
declare(strict_types=1);

return [
    \Tpwd\TextFlow\Domain\Model\TextFlowPattern::class => [
        'tableName' => 'tx_tpwdtextflow_domain_model_textflowpattern',
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
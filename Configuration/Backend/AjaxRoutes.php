<?php
/**
 * Definitions for AJAX routes provided by TextFlow extension
 */
return [
    // Route for text preview
    'tx_textflow_preview' => [
        'path' => '/textflow/preview',
        'target' => \PixelCoda\TextFlow\Controller\AjaxController::class . '::previewAction'
    ],
    
    // Route for pattern loading
    'tx_textflow_patterns' => [
        'path' => '/textflow/patterns',
        'target' => \PixelCoda\TextFlow\Controller\AjaxController::class . '::getPatternsAction'
    ]
]; 
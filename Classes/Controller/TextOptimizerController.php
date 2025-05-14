<?php

namespace PixelCoda\TextFlow\Controller;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

class TextOptimizerController extends ActionController
{
    public function indexAction(): ResponseInterface
    {
        return $this->htmlResponse();
    }

    public function optimizeAction(): ResponseInterface
    {
        $text = $this->request->getArgument('text') ?? '';
        // Hier kommt später die Textoptimierungslogik
        return $this->htmlResponse();
    }
} 
<?php
declare(strict_types=1);
namespace PixelCoda\TextFlow\Controller;

use Psr\Http\Message\ResponseInterface;
use PixelCoda\TextFlow\Service\TextFlowService;
use Psr\Log\LoggerInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * Controller for TextFlow plugin.
 */
class TextFlowController extends ActionController
{
    protected TextFlowService $textFlowService;
    protected LoggerInterface $logger;

    public function __construct(TextFlowService $textFlowService, LoggerInterface $logger)
    {
        $this->textFlowService = $textFlowService;
        $this->logger = $logger;
    }

    /**
     * Initialize the optimize action
     */
    public function initializeOptimizeAction(): void
    {
        if ($this->request->getMethod() === 'POST') {
            $this->arguments->addNewArgument('text', 'string', false);
        }
    }

    /**
     * Renders the text flow content.
     */
    public function showAction(): ResponseInterface
    {
        $contentObject = $this->configurationManager->getContentObject()->data;
        $text = $contentObject['bodytext'] ?? '';
        $hyphenatedText = $this->textFlowService->hyphenate($text, $contentObject);
        $this->view->assign('hyphenatedText', $hyphenatedText);
        
        return $this->htmlResponse();
    }

    /**
     * Optimizes the text flow.
     */
    public function optimizeAction(): ResponseInterface
    {
        $text = '';
        
        if ($this->request->getMethod() === 'POST') {
            if ($this->request->hasArgument('text')) {
                $text = $this->request->getArgument('text');
            }
        }
        
        if (empty($text)) {
            $text = GeneralUtility::_GP('text');
        }
        
        if (empty($text)) {
            $contentObject = $this->configurationManager->getContentObject()->data;
            $text = $contentObject['bodytext'] ?? '';
        }
        
        if (empty($text)) {
            $this->logger->warning('TextFlow Plugin: No text content found for optimization');
            $this->view->assign('error', 'Bitte geben Sie einen Text zur Optimierung ein.');
            return $this->htmlResponse();
        }

        $optimizedText = $this->textFlowService->hyphenate($text, []);
        $this->view->assign('optimizedText', $optimizedText);
        $this->view->assign('originalText', $text);
        $this->view->assign('settings', $this->settings);
        
        return $this->htmlResponse();
    }

    public function listAction(): ResponseInterface
    {
        $this->logger->debug('TextFlow Plugin: listAction called', [
            'settings' => $this->settings,
            'contentObject' => $this->configurationManager->getContentObject()->data
        ]);

        $contentObject = $this->configurationManager->getContentObject()->data;
        $text = $contentObject['bodytext'] ?? '';
        
        if (empty($text)) {
            $this->logger->warning('TextFlow Plugin: No text content found');
        }

        $this->view->assign('text', $text);
        $this->view->assign('settings', $this->settings);
        $this->view->assign('contentObject', $contentObject);
        
        return $this->htmlResponse();
    }

    /**
     * Renders the text flow content element.
     */
    public function textFlowAction(): ResponseInterface
    {
        $contentObject = $this->configurationManager->getContentObject()->data;
        $text = $contentObject['bodytext'] ?? '';
        
        if (!empty($text)) {
            $optimizedText = $this->textFlowService->hyphenate($text, $contentObject);
            $this->view->assign('text', $optimizedText);
        }
        
        return $this->htmlResponse();
    }
}
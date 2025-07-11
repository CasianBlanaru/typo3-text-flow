<?php
declare(strict_types=1);
namespace Tpwdag\TextFlow\Controller;

use Psr\Http\Message\ResponseInterface;
use Tpwdag\TextFlow\Service\TextFlowService;
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

        $this->logger->debug('TextFlow showAction: Processing text', [
            'text_length' => strlen($text),
            'content_uid' => $contentObject['uid'] ?? 'unknown'
        ]);

        // Check for debug parameter in URL - try different parameter names
        $debugMode = (bool)GeneralUtility::_GP('textflow_debug');
        if (!$debugMode) {
            $debugMode = (bool)GeneralUtility::_GP('debug_textflow');
        }
        if (!$debugMode) {
            $debugMode = (bool)GeneralUtility::_GP('debug');
        }

        $this->logger->debug('TextFlow: Debug mode = ' . ($debugMode ? 'ON' : 'OFF'));

        // Enable text flow by default
        $conf = [
            'enable' => 1,
            'preserveStructure' => 1,
            'debug' => true // Always enable debug mode for tests
        ];

        $hyphenatedText = $this->textFlowService->hyphenate($text, $conf);

        $this->view->assign('hyphenatedText', $hyphenatedText);
        $this->view->assign('text', $text);
        $this->view->assign('settings', $this->settings);
        $this->view->assign('debugMode', $debugMode);

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

        // Enable text flow
        $conf = ['enable' => 1, 'preserveStructure' => 1];
        $optimizedText = $this->textFlowService->hyphenate($text, $conf);

        $this->view->assign('optimizedText', $optimizedText);
        $this->view->assign('hyphenatedText', $optimizedText); // Assign to both variables for template compatibility
        $this->view->assign('originalText', $text);
        $this->view->assign('text', $text);
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
        } else {
            // Enable text flow
            $conf = ['enable' => 1, 'preserveStructure' => 1];
            $hyphenatedText = $this->textFlowService->hyphenate($text, $conf);
            $this->view->assign('hyphenatedText', $hyphenatedText);
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
            // Enable text flow
            $conf = ['enable' => 1, 'preserveStructure' => 1];
            $optimizedText = $this->textFlowService->hyphenate($text, $conf);
            $this->view->assign('text', $optimizedText);
            $this->view->assign('hyphenatedText', $optimizedText);
        } else {
            $this->logger->warning('TextFlow Plugin: No text content found for text flow action');
        }

        return $this->htmlResponse();
    }
}

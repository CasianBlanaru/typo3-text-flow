<?php
declare(strict_types=1);
namespace PixelCoda\TextFlow\Controller;

use Psr\Http\Message\ResponseInterface;
use PixelCoda\TextFlow\Service\TextFlowService;
use Psr\Log\LoggerInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use PixelCoda\TextFlow\Domain\Repository\TextFlowPatternRepository;

/**
 * Controller for TextFlow plugin.
 */
class TextFlowController extends ActionController
{
    protected TextFlowService $textFlowService;
    protected LoggerInterface $logger;
    protected TextFlowPatternRepository $patternRepository;

    public function __construct(TextFlowService $textFlowService, LoggerInterface $logger, TextFlowPatternRepository $patternRepository)
    {
        $this->textFlowService = $textFlowService;
        $this->logger = $logger;
        $this->patternRepository = $patternRepository;
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
        $settings = $this->settings;
        $text = $this->request->hasArgument('text') ? $this->request->getArgument('text') : '';
        $language = $this->request->hasArgument('language') ? $this->request->getArgument('language') : 'de';
        
        $processedText = $this->textFlowService->hyphenate($text, [
            'enable' => true,
            'enable_textflow' => $language,
            'preserveStructure' => true
        ]);
        
        $this->view->assign('originalText', $text);
        $this->view->assign('processedText', $processedText);
        $this->view->assign('language', $language);
        $this->view->assign('settings', $settings);
        
        $availableLanguages = [
            'de' => 'Deutsch',
            'en' => 'English',
            'fr' => 'Français',
            'es' => 'Español',
            'it' => 'Italiano',
            'nl' => 'Nederlands',
            'pt' => 'Português',
            'zh' => '中文',
            'ar' => 'العربية',
            'hi' => 'हिन्दी'
        ];
        $this->view->assign('availableLanguages', $availableLanguages);
        
        $softHyphenCount = substr_count($processedText, "\u{00AD}");
        $this->view->assign('softHyphenCount', $softHyphenCount);
        
        $wordCount = str_word_count($text);
        $charCount = strlen($text);
        
        $this->view->assign('statistics', [
            'wordCount' => $wordCount,
            'charCount' => $charCount,
            'softHyphenCount' => $softHyphenCount,
            'softHyphenRatio' => $wordCount > 0 ? $softHyphenCount / $wordCount : 0
        ]);
        
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

    /**
     * Inject the TextFlowService
     */
    public function injectTextFlowService(TextFlowService $textFlowService): void
    {
        $this->textFlowService = $textFlowService;
    }
    
    /**
     * Inject the TextFlowPatternRepository
     */
    public function injectTextFlowPatternRepository(TextFlowPatternRepository $patternRepository): void
    {
        $this->patternRepository = $patternRepository;
    }
}
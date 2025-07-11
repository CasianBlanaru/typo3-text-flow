<?php
declare(strict_types=1);

namespace Tpwdag\TextFlow\Hooks;

use Tpwdag\TextFlow\Service\TextFlowService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Hook to process HTML content after parsing
 */
class TextContentHook
{
    /**
     * Process HTML content after parsing
     *
     * @param array $params Parameters from the parser
     * @param object $pObj Parent object
     * @return void
     */
    public function processContent(array &$params, $pObj): void
    {
        // Skip if content is empty
        if (empty($params['pObj']->content) || trim($params['pObj']->content) === '') {
            return;
        }

        // Check if TextFlow is enabled for this content element
        if (empty($params['pObj']->data['enable_textflow']) || $params['pObj']->data['enable_textflow'] === 'none') {
            return;
        }

        // Clean empty paragraphs and HTML comments
        $content = $params['pObj']->content;

        // Remove empty paragraphs
        $content = preg_replace('/<p>\s*(&nbsp;|\s)*\s*<\/p>/', '', $content);

        // Remove HTML comments
        $content = preg_replace('/<!--(.|\s)*?-->/', '', $content);

        // Remove multiple consecutive empty lines
        $content = preg_replace('/\n\s*\n/', "\n", $content);

        // Check if content is still available after cleaning
        if (empty(trim($content))) {
            return;
        }

        // Process content with TextFlow
        $textFlowService = GeneralUtility::makeInstance(TextFlowService::class);
        $processConf = [
            'enable' => 1,
            'preserveStructure' => 1
        ];

        // Enable debug mode via URL parameter
        $debugParam = GeneralUtility::_GP('debug_textflow');
        if ($debugParam) {
            $processConf['debug'] = (bool)$debugParam;
        }

        // Apply hyphenation
        $params['pObj']->content = $textFlowService->hyphenate($content, $processConf);
    }
}

<?php
declare(strict_types=1);

namespace PixelCoda\TextFlow\Hooks;

use PixelCoda\TextFlow\Service\TextFlowService;
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

        // Überprüfe, ob TextFlow für dieses Content-Element aktiviert ist
        if (empty($params['pObj']->data['enable_textflow']) || $params['pObj']->data['enable_textflow'] === 'none') {
            return;
        }

        // Clean empty paragraphs and HTML-Kommentare
        $content = $params['pObj']->content;

        // Entferne leere Paragraphen
        $content = preg_replace('/<p>\s*(&nbsp;|\s)*\s*<\/p>/', '', $content);

        // Entferne HTML-Kommentare
        $content = preg_replace('/<!--(.|\s)*?-->/', '', $content);

        // Entferne mehrere aufeinanderfolgende Leerzeilen
        $content = preg_replace('/\n\s*\n/', "\n", $content);

        // Erneut prüfen, ob der Inhalt nach der Reinigung noch vorhanden ist
        if (empty(trim($content))) {
            return;
        }

        // Process content with TextFlow
        $textFlowService = GeneralUtility::makeInstance(TextFlowService::class);
        $processConf = [
            'enable' => 1,
            'preserveStructure' => 1
        ];

        // Debug-Modus über URL-Parameter aktivieren
        $debugParam = GeneralUtility::_GP('debug_textflow');
        if ($debugParam) {
            $processConf['debug'] = (bool)$debugParam;
        }

        // Apply hyphenation
        $params['pObj']->content = $textFlowService->hyphenate($content, $processConf);
    }
}

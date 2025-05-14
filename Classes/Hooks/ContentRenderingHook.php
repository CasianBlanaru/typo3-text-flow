<?php
declare(strict_types=1);

namespace PixelCoda\TextFlow\Hooks;

use PixelCoda\TextFlow\Service\TextFlowService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\AbstractContentObject;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Hook to process content elements before rendering
 */
class ContentRenderingHook extends AbstractContentObject
{
    /**
     * @inheritDoc
     */
    public function render($conf = []): string
    {
        // Get content from parent
        $content = '';
        if (isset($this->cObj)) {
            $content = $this->cObj->cObjGetSingle('TEXT', $conf);
        }

        // Process content with TextFlow if not empty and if TextFlow is enabled for this element
        if (!empty($content) && isset($this->cObj) &&
            !empty($this->cObj->data['enable_textflow']) &&
            $this->cObj->data['enable_textflow'] !== 'none') {

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

            return $textFlowService->hyphenate($content, $processConf);
        }

        return $content;
    }

    /**
     * Renders the content element with TextFlow processing
     *
     * @param string $name The name of the cObject being rendered
     * @param array $conf The configuration array
     * @param string $TSkey TS key, not used here
     * @param ContentObjectRenderer $cObj The parent content object
     * @return string The processed content
     */
    public function cObjGetSingleExt(
        string $name,
        array $conf,
        string $TSkey,
        ContentObjectRenderer $cObj
    ): string {
        // Get the original content
        $content = $cObj->cObjGetSingle($name, $conf);

        // Skip if empty
        if (empty($content)) {
            return $content;
        }

        // Skip if disabled in configuration
        if (isset($conf['tx_textflow.']['disable']) && $conf['tx_textflow.']['disable']) {
            return $content;
        }

        // Skip if TextFlow is not enabled for this content element
        if (empty($cObj->data['enable_textflow']) || $cObj->data['enable_textflow'] === 'none') {
            return $content;
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

        return $textFlowService->hyphenate($content, $processConf);
    }
}

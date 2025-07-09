<?php
declare(strict_types=1);

namespace Tpwdag\TextFlow\DataProcessing;

use Tpwdag\TextFlow\Service\TextFlowService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\ContentObject\DataProcessorInterface;

class TextFlowProcessor implements DataProcessorInterface
{
    /**
     * Process content object data
     *
     * @param ContentObjectRenderer $cObj The data of the content element or page
     * @param array $contentObjectConfiguration The configuration of Content Object
     * @param array $processorConfiguration The configuration of this processor
     * @param array $processedData Key/value store of processed data (e.g. to be passed to a Fluid View)
     * @return array the processed data as key/value store
     */
    public function process(
        ContentObjectRenderer $cObj,
        array $contentObjectConfiguration,
        array $processorConfiguration,
        array $processedData
    ): array {
        $textFlowService = GeneralUtility::makeInstance(TextFlowService::class);

        // Get the content from the tt_content record
        $content = $cObj->data['bodytext'] ?? '';

        // Get configuration from content element
        $options = [
            'enable_textflow' => $cObj->data['enable_textflow'] ?? null
        ];

        // Enable debug mode via URL parameter
        $debugParam = GeneralUtility::_GP('debug_textflow');
        if ($debugParam) {
            $options['debug'] = (bool)$debugParam;
        }

        // Process the content
        $hyphenatedText = $textFlowService->hyphenate($content, $options);

        // Set the result in processedData using the key specified in TypoScript (default: hyphenatedText)
        $targetVariableName = $processorConfiguration['as'] ?? 'hyphenatedText';
        $processedData[$targetVariableName] = $hyphenatedText;

        return $processedData;
    }
}

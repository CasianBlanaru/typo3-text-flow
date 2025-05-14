<?php
declare(strict_types=1);
namespace PixelCoda\TextFlow\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use PixelCoda\TextFlow\Service\TextFlowService;

/**
 * AJAX Controller for TextFlow extension
 * Handles AJAX requests for preview functionality.
 */
class AjaxController
{
    /**
     * Process text preview request
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function previewAction(ServerRequestInterface $request): ResponseInterface
    {
        $data = json_decode($request->getBody()->getContents(), true);
        $result = [
            'success' => false,
            'message' => '',
            'result' => ''
        ];
        
        if (!isset($data['text']) || empty($data['text'])) {
            $result['message'] = 'No text provided';
            return new JsonResponse($result);
        }
        
        try {
            // Get service to process text
            $textFlowService = GeneralUtility::makeInstance(TextFlowService::class);
            
            // Configuration for processing
            $conf = [
                'enable' => true,
                'enable_textflow' => $data['language'] ?? 'de',
                'preserveStructure' => true
            ];
            
            // Process the text
            $processedText = $textFlowService->hyphenate($data['text'], $conf);
            
            $result['success'] = true;
            $result['result'] = $processedText;
        } catch (\Exception $e) {
            $result['message'] = 'Error processing text: ' . $e->getMessage();
        }
        
        return new JsonResponse($result);
    }
    
    /**
     * Get patterns for a specific language
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function getPatternsAction(ServerRequestInterface $request): ResponseInterface
    {
        $params = $request->getQueryParams();
        $language = $params['language'] ?? 'de';
        
        try {
            // Get repository
            $repository = GeneralUtility::makeInstance(
                \PixelCoda\TextFlow\Domain\Repository\TextFlowPatternRepository::class
            );
            
            // Get patterns for language
            $patterns = $repository->findByLanguage($language);
            
            // Extract pattern strings only
            $patternStrings = array_map(function($item) {
                return $item['pattern'];
            }, $patterns);
            
            return new JsonResponse($patternStrings);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Error loading patterns: ' . $e->getMessage()
            ], 500);
        }
    }
} 
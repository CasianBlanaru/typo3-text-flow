<?php
declare(strict_types=1);
namespace Tpwd\TextFlow\Hooks;

use TYPO3\CMS\Backend\View\PageLayoutView;
use TYPO3\CMS\Backend\View\PageLayoutViewDrawItemHookInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Hook to display preview of textflow plugin content element in page module
 */
class PageLayoutViewDrawItemHook implements PageLayoutViewDrawItemHookInterface
{
    /**
     * Preprocesses the preview rendering of a content element.
     *
     * @param PageLayoutView $parentObject The parent object that triggered this hook
     * @param bool $drawItem A switch to tell the parent object if the item still must be drawn
     * @param string $headerContent The content of the header bar
     * @param string $itemContent The content of the item itself
     * @param array $row The current data row for this item
     */
    public function preProcess(
        PageLayoutView &$parentObject,
        &$drawItem,
        &$headerContent,
        &$itemContent,
        array &$row
    ): void {
        if ($row['CType'] === 'textflow_pi1') {
            $itemContent .= '<div class="textflow-preview">';
            if ($row['bodytext']) {
                $itemContent .= '<p>' . htmlspecialchars($row['bodytext']) . '</p>';
            }
            if ($row['enable_textflow']) {
                $itemContent .= '<p><strong>TextFlow enabled:</strong> ' . htmlspecialchars($row['enable_textflow']) . '</p>';
            }
            $itemContent .= '</div>';
            $drawItem = false;
        }
    }
} 
<?php

defined('TYPO3') or die();

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
    'text_flow',
    'Configuration/TypoScript',
    'Text Flow'
);

// Nothing else needed here as plugin registration is done in TCA/Overrides/tt_content.php 
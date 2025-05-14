.. include:: /Includes.rst.txt

.. _start:

==================
PixelCoda TextFlow
==================

:Extension key: pixelcoda_textflow
:Version: 1.0.0
:Language: en
:Author: Casian Blanaru
:Company: PixelCoda
:Email: casianus
:License: GPL-2.0-or-later
:Rendered: |today|

PixelCoda TextFlow optimizes text flow in TYPO3 with dynamic hyphenation and
multi-language support, featuring a unique backend interface.

.. toctree::
   :maxdepth: 3
   :titlesonly:

   Introduction/Index
   Installation/Index
   Configuration/Index
   UserManual/Index
   AdministratorManual/Index
   DeveloperManual/Index

=============
TYPO3 TextFlow
=============

:Extension key: text_flow
:Package name: pixelcoda/text-flow
:Version: |release|
:Language: en
:Author: PixelCoda
:License: GPL-2.0-or-later

TextFlow Extension Manual
=======================

This documentation explains how to use the TextFlow extension for TYPO3 CMS.

Introduction
-----------

TextFlow is a powerful text optimization extension that provides intelligent hyphenation
for multiple languages while preserving HTML structure and text formatting.

Key Features
~~~~~~~~~~~

* Multi-language support (de, en, fr, es, it, nl)
* Smart hyphenation based on language patterns
* HTML and special character preservation
* Case-sensitive text processing
* Performance-optimized caching
* Backend pattern management
* Live preview functionality
* Debug mode with visual hyphenation markers
* Selective activation per content element

Quick Start Guide
---------------

1. Installation
~~~~~~~~~~~~~

Install via Composer::

    composer require pixelcoda/text-flow

2. Basic Setup
~~~~~~~~~~~~

a) Activate the extension in TYPO3 backend
b) Clear all caches
c) Include static TypoScript template

3. TypoScript Configuration
~~~~~~~~~~~~~~~~~~~~~~~~

Basic setup::

    page.10 = FLUIDTEMPLATE
    page.10 {
        templateRootPaths.10 = EXT:text_flow/Resources/Private/Templates/
        partialRootPaths.10 = EXT:text_flow/Resources/Private/Partials/
        layoutRootPaths.10 = EXT:text_flow/Resources/Private/Layouts/
    }

Advanced configuration::

    plugin.tx_textflow {
        settings {
            defaultLanguage = de
            enableCache = 1
            minWordLength = 5
        }
    }

Usage Guide
----------

1. Content Elements
~~~~~~~~~~~~~~~~

a) Create or edit a content element
b) Find TextFlow settings in the "Appearance" tab
c) Choose hyphenation options:

* all: Enable for all languages
* none: Disable hyphenation (default)
* de: German only
* en: English only
* fr: French only
* es: Spanish only

Note: By default, hyphenation is disabled for all content elements. You need to explicitly enable it for each element.

2. Fluid Templates
~~~~~~~~~~~~~~~

Basic usage::

    {namespace tf=PixelCoda\TextFlow\ViewHelpers}

    <tf:process>{text}</tf:process>

With options::

    <tf:process text="{text}" language="de" />

In a loop::

    <f:for each="{texts}" as="text">
        <tf:process>{text}</tf:process>
    </f:for>

3. PHP Integration
~~~~~~~~~~~~~~~

Basic usage::

    use PixelCoda\TextFlow\Service\TextFlowService;
    use TYPO3\CMS\Core\Utility\GeneralUtility;

    $textFlowService = GeneralUtility::makeInstance(TextFlowService::class);
    $hyphenatedText = $textFlowService->hyphenate($text);

With language option::

    $hyphenatedText = $textFlowService->hyphenate($text, ['enable_textflow' => 'de']);

With additional options::

    $options = [
        'enable_textflow' => 'de',
        'custom_setting' => 'value'
    ];
    $hyphenatedText = $textFlowService->hyphenate($text, $options);

With debug mode::

    $options = [
        'enable_textflow' => 'de',
        'debug' => true
    ];
    $hyphenatedText = $textFlowService->hyphenate($text, $options);

4. Debugging
~~~~~~~~~~

TextFlow offers multiple debugging options to visualize hyphenation points:

a) URL Parameter Debugging:

   Add one of these parameters to your page URL to activate debug mode:

   * ?debug_textflow=1 - Basic text markers ("-||-")
   * ?debug_textflow=3 - Very obvious markers ("▼TRENN▼")

b) Programmatic Debugging:

   * Direct Service Configuration::

        $textFlowService = GeneralUtility::makeInstance(TextFlowService::class);
        TextFlowService::setDebugLevel(1); // 1 or 3
        $hyphenatedText = $textFlowService->hyphenate($text, $options);

   * Via configuration array::

        $options = [
            'enable_textflow' => 'de',
            'debug' => true
        ];
        $hyphenatedText = $textFlowService->hyphenate($text, $options);

c) Debug Levels:

   * Level 1: Basic text markers ("-||-")
   * Level 3: Very obvious markers ("▼TRENN▼")

5. Backend Module
~~~~~~~~~~~~~~

The TextFlow backend module provides:

* Pattern management interface
* Language-specific settings
* Live preview functionality
* Pattern import/export
* Cache management

6. Pattern Management
~~~~~~~~~~~~~~~~~

Via backend module:

1. Navigate to Web > TextFlow
2. Add new patterns
3. Edit existing patterns
4. Filter patterns by language
5. Test hyphenation preview

Programmatically::

    use PixelCoda\TextFlow\Domain\Repository\TextFlowPatternRepository;
    use TYPO3\CMS\Core\Utility\GeneralUtility;

    $patternRepository = GeneralUtility::makeInstance(TextFlowPatternRepository::class);

    // Add single pattern
    $patternRepository->addPattern('beispiel', 'de');

    // Add multiple patterns
    $patterns = [
        'de' => ['bei', 'spiel'],
        'en' => ['ex', 'ample']
    ];
    foreach ($patterns as $language => $languagePatterns) {
        foreach ($languagePatterns as $pattern) {
            $patternRepository->addPattern($pattern, $language);
        }
    }

Pattern Format
------------

Rules for hyphenation patterns:

* Minimum length: 2 characters
* Maximum length: 20 characters
* Valid characters: a-z, A-Z, äöüßÄÖÜ
* Format: Word parts (e.g., 'bei', 'spiel')

Cache Management
-------------

1. Backend clearing:

   * TYPO3 backend > Admin Tools > Maintenance
   * Select "Clear all caches"

2. Programmatic clearing::

    use TYPO3\CMS\Core\Cache\CacheManager;
    use TYPO3\CMS\Core\Utility\GeneralUtility;

    $cacheManager = GeneralUtility::makeInstance(CacheManager::class);
    $cacheManager->flushCachesByTag('text_flow');

Troubleshooting
-------------

Common Issues
~~~~~~~~~~~

1. No hyphenation visible:

   * Check TextFlow activation in content element (must be explicitly enabled)
   * Verify language configuration
   * Clear caches
   * Check minimum word length (5 characters)
   * Ensure debug_textflow parameter is not set to 0

2. Wrong hyphenation:

   * Check language settings
   * Verify pattern repository
   * Rebuild cache

3. Debug mode not working:

   * Try different debug levels (?debug_textflow=3 for most visible markers)
   * Clear browser and TYPO3 caches
   * Check if the content element has TextFlow enabled

Logging
~~~~~~

The extension logs to TYPO3's system log::

    // In custom extensions
    $logger->warning('TextFlow Service: Empty text content');
    $logger->error('TextFlow Service: Invalid pattern format');

    // Check log files
    var/log/typo3_*.log

Support
------

For questions and issues:

* GitHub Issues: https://github.com/pixelcoda/text-flow/issues
* Email: support@pixelcoda.com

License
------

This extension is licensed under GPL-2.0-or-later. See LICENSE file for details.

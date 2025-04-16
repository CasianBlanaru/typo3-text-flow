.. include:: /Includes.rst.txt

.. _introduction:

============
Introduction
============

What does it do?
===============

The PixelCoda TextFlow extension enhances text presentation in TYPO3 by:

* Implementing intelligent hyphenation algorithms
* Supporting multiple languages
* Providing a user-friendly backend interface
* Optimizing text flow dynamically

Screenshots
==========

Backend View
-----------

.. figure:: ../Images/BackendView.png
   :class: with-shadow
   :alt: Backend view
   :width: 500px

   Backend view of the TextFlow extension

Features
=======

* Advanced hyphenation support
* Multi-language capabilities
* Customizable text flow settings
* Easy-to-use backend interface
* Performance optimized
* TYPO3 v11 LTS compatible

.. _installation:

Installation
============

Requirements
------------

- TYPO3 12.4 or higher
- PHP 8.1 or 8.2

Installation
------------

1. Install via Composer:
   .. code-block:: bash

      composer require pixelcoda/textflow

2. Activate the extension in the TYPO3 backend.

3. Include the TypoScript setup:
   .. code-block:: typoscript

      @import 'EXT:pixelcoda_textflow/Configuration/TypoScript/setup.typoscript'

4. Clear the TYPO3 cache.

5. Create initial patterns in the backend module (optional). 
.. include:: /Includes.rst.txt

.. _configuration:

=============
Configuration
=============

TypoScript Reference
===================

All configuration options are set via TypoScript. Here's a complete reference of available settings:

Properties
---------

.. container:: ts-properties

   ============================= ===================================== ====================
   Property                      Data type                             Default
   ============================= ===================================== ====================
   enableHyphenation_           boolean                               1
   defaultLanguage_             string                                en
   customPatterns_              array                                 {}
   minWordLength_               integer                               5
   leftMin_                     integer                               2
   rightMin_                    integer                               2
   ============================= ===================================== ====================

Property Details
--------------

.. _enableHyphenation:

enableHyphenation
""""""""""""""""
.. container:: table-row

   Property
      enableHyphenation
   Data type
      boolean
   Description
      Enables or disables the hyphenation feature globally.
   Default
      1

.. _defaultLanguage:

defaultLanguage
"""""""""""""
.. container:: table-row

   Property
      defaultLanguage
   Data type
      string
   Description
      Sets the default language for hyphenation patterns. Available options: en, de, fr, es, it
   Default
      en

.. _customPatterns:

customPatterns
""""""""""""
.. container:: table-row

   Property
      customPatterns
   Data type
      array
   Description
      Define custom hyphenation patterns for specific words.
   Default
      {}

Example Configuration
===================

.. code-block:: typoscript

   plugin.tx_pixelcodatextflow {
       settings {
           enableHyphenation = 1
           defaultLanguage = en
           minWordLength = 5
           leftMin = 2
           rightMin = 2
           customPatterns {
               example = ex-am-ple
               custom = cus-tom
           }
       }
   }

Integration
==========

To use the extension in your templates:

.. code-block:: html

   <f:textflow.hyphenate>{text}</f:textflow.hyphenate> 
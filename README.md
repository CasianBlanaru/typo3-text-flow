# TextFlow TYPO3 Extension

## Overview

TextFlow ist eine TYPO3-Erweiterung für intelligente Texttrennung und -optimierung in mehreren Sprachen. Sie verbessert die Lesbarkeit Ihrer Inhalte durch sprachspezifische Trennungsmuster unter Beibehaltung der HTML-Struktur und Textformatierung.

## Features

- Mehrsprachige Unterstützung (Deutsch, Englisch, Französisch, Spanisch)
- Intelligente Trennung basierend auf sprachspezifischen Mustern
- Erhält HTML-Tags und Sonderzeichen
- Berücksichtigt Groß-/Kleinschreibung
- Konfigurierbar über Content-Element-Einstellungen
- Performance-optimiert mit Pattern-Caching
- Erweiterbare Muster-Bibliothek

## Installation

```bash
composer require pixelcoda/text-flow
```

## Schnellstart-Anleitung

1. **Installation**
   ```bash
   composer require pixelcoda/text-flow
   ```

2. **Extension aktivieren**
   - Im TYPO3-Backend zur Extension-Verwaltung navigieren
   - TextFlow aktivieren
   - Cache leeren

3. **TypoScript einbinden**
   - Im Template-Modul das Root-Template bearbeiten
   - "Include Static" auswählen
   - "TextFlow (text_flow)" hinzufügen

4. **Basis-Konfiguration**
   ```typoscript
   page.10 = FLUIDTEMPLATE
   page.10 {
       templateRootPaths.10 = EXT:text_flow/Resources/Private/Templates/
       partialRootPaths.10 = EXT:text_flow/Resources/Private/Partials/
       layoutRootPaths.10 = EXT:text_flow/Resources/Private/Layouts/
   }
   ```

## Verwendung

### 1. Im Content Element

1. Content-Element erstellen/bearbeiten
2. Im Tab "Erscheinungsbild" die TextFlow-Einstellungen finden
3. Trennungsoptionen auswählen:
   - `all`: Für alle Sprachen aktivieren
   - `none`: Trennung deaktivieren
   - `de`, `en`, `fr`, `es`: Nur für bestimmte Sprache aktivieren

### 2. In Fluid Templates

```html
{namespace tf=PixelCoda\TextFlow\ViewHelpers}

<!-- Einfache Verwendung -->
<tf:process>{text}</tf:process>

<!-- Mit Optionen -->
<tf:process text="{text}" language="de" />

<!-- In einer Schleife -->
<f:for each="{texts}" as="text">
    <tf:process>{text}</tf:process>
</f:for>
```

### 3. In PHP

```php
use PixelCoda\TextFlow\Service\TextFlowService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

// Service instanziieren
$textFlowService = GeneralUtility::makeInstance(TextFlowService::class);

// Einfache Verwendung
$hyphenatedText = $textFlowService->hyphenate($text);

// Mit Sprach-Option
$hyphenatedText = $textFlowService->hyphenate($text, ['enable_textflow' => 'de']);

// Mit zusätzlichen Optionen
$options = [
    'enable_textflow' => 'de',
    'custom_setting' => 'value'
];
$hyphenatedText = $textFlowService->hyphenate($text, $options);
```

### 4. Backend-Modul

1. Im TYPO3-Backend zu "Web > TextFlow" navigieren
2. Trennungsmuster verwalten:
   - Neue Muster hinzufügen
   - Bestehende Muster bearbeiten
   - Muster nach Sprachen filtern
   - Vorschau der Trennung testen

### 5. Programmatische Muster-Verwaltung

```php
use PixelCoda\TextFlow\Domain\Repository\TextFlowPatternRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;

$patternRepository = GeneralUtility::makeInstance(TextFlowPatternRepository::class);

// Muster hinzufügen
$patternRepository->addPattern('beispiel', 'de');

// Muster für mehrere Sprachen
$patterns = [
    'de' => ['bei', 'spiel'],
    'en' => ['ex', 'ample']
];
foreach ($patterns as $language => $languagePatterns) {
    foreach ($languagePatterns as $pattern) {
        $patternRepository->addPattern($pattern, $language);
    }
}
```

## Muster-Format

Trennungsmuster müssen folgende Regeln befolgen:
- Mindestlänge: 2 Zeichen
- Maximallänge: 20 Zeichen
- Erlaubte Zeichen: a-z, A-Z, äöüßÄÖÜ
- Format: Einzelne Wortteile (z.B. 'bei', 'spiel')

## Cache-Management

### Cache leeren

1. **Über Backend**
   - TYPO3-Backend > Admin Tools > Maintenance
   - "Clear all caches" wählen

2. **Programmatisch**
   ```php
   use TYPO3\CMS\Core\Cache\CacheManager;
   use TYPO3\CMS\Core\Utility\GeneralUtility;

   $cacheManager = GeneralUtility::makeInstance(CacheManager::class);
   $cacheManager->flushCachesByTag('text_flow');
   ```

## Fehlerbehebung

### 1. Keine Trennung sichtbar
- TextFlow in Content-Element aktiviert?
- Sprache korrekt konfiguriert?
- Cache geleert?
- Mindestlänge (5 Zeichen) erreicht?

### 2. Falsche Trennungen
- Spracheinstellung überprüfen
- Muster-Repository kontrollieren
- Cache leeren und neu aufbauen

### 3. Performance-Probleme
- Pattern-Cache aktiviert?
- Anzahl der Muster optimieren
- Logging-Level anpassen

## Logging

```php
// In eigenen Extensions
$logger->warning('TextFlow Service: Empty text content');
$logger->error('TextFlow Service: Invalid pattern format');

// Log-Dateien prüfen
var/log/typo3_*.log
```

## Support

Bei Fragen oder Problemen:
- GitHub Issues: [Project Issues](https://github.com/pixelcoda/text-flow/issues)
- E-Mail: support@pixelcoda.com

## Lizenz

Diese Extension ist unter GPL-2.0-or-later lizenziert. Details in der LICENSE-Datei.

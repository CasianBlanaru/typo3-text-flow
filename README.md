# TYPO3 Text Flow Extension

Optimizes text flow with dynamic hyphenation for multiple languages.

## Features

- Multi-language support (DE, EN, FR, ES, IT, NL, PT, ZH, AR, HI)
- Smart hyphenation based on language patterns
- HTML and special character preservation
- Case-sensitive text processing
- Performance-optimized caching
- Backend pattern management
- Debug mode with visual hyphenation markers
- Selective activation per content element

## Installation

### Via Composer

```bash
composer require pixelcoda/text-flow
```

After installation, make sure to:
1. Activate the extension in the Extension Manager
2. Clear all caches
3. Run the import command for additional languages:

```bash
vendor/bin/typo3 textflow:import-patterns
```

## Usage

### In Backend

1. Edit any content element
2. Go to the "Appearance" tab
3. Find "Text Flow Language" dropdown
4. Select your preferred language:
   - Disabled [none] (default)
   - All languages [all]
   - German [de]
   - English [en]
   - French [fr]
   - Spanish [es]
   - Italian [it]
   - Dutch [nl]
   - Portuguese [pt]
   - Chinese [zh]
   - Arabic [ar]
   - Hindi [hi]

### In Templates

```html
<html xmlns:f="http://typo3.org/ns/TYPO3/CMS/Fluid/ViewHelpers"
      xmlns:tf="http://typo3.org/ns/PixelCoda/TextFlow/ViewHelpers"
      data-namespace-typo3-fluid="true">

<!-- WICHTIG: Beachten Sie die korrekte Kleinschreibung des ViewHelpers -->

<!-- Basic usage (using the textflow ViewHelper) -->
<tf:textflow>{text}</tf:textflow>

<!-- With text parameter -->
<tf:textflow text="{text}" />

<!-- With language parameter -->
<tf:textflow text="{text}" language="de" />

<!-- With content element data -->
<tf:textflow data="{data}">{text}</tf:textflow>

<!-- Legacy ViewHelpers (weiterhin unterstützt) -->
<tf:process>{text}</tf:process>
<tf:optimize>{text}</tf:optimize>
</html>
```

### Debug Mode

Add one of these URL parameters:

- `?debug_textflow=1` - Basic text markers ("-||-")
- `?debug_textflow=3` - Prominent markers ("▼TRENN▼")

**Important:** The debug mode now only shows hyphenation markers for elements where TextFlow is actually enabled.

## Supported Languages

- German (de)
- English (en)
- French (fr)
- Spanish (es)
- Italian (it)
- Dutch (nl)
- Portuguese (pt)
- Chinese (zh)
- Arabic (ar)
- Hindi (hi)

## License

GPL-2.0-or-later. See LICENSE file for details.

## Contributing

1. Fork the repository
2. Create a feature branch
3. Commit your changes
4. Push to the branch
5. Create a Pull Request

## Support

- Documentation: [docs.typo3.org](https://docs.typo3.org)
- Issue Tracker: [GitHub Issues](https://github.com/pixelcoda/text-flow/issues)
- Slack: #ext-text-flow on typo3.slack.com

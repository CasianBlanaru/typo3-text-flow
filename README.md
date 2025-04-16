# TextFlow Extension for TYPO3

A powerful text optimization extension for TYPO3 that provides intelligent hyphenation and text flow enhancement.

## Features

- Multi-language support for text hyphenation
- Smart pattern-based hyphenation algorithm
- HTML content preservation
- Case-sensitive text processing
- Performance optimization through caching
- Backend module for text optimization
- Frontend plugin for automatic text processing
- Configurable content element

## Installation

Install via composer:

```bash
composer require pixelcoda/text-flow
```

## Configuration

### Basic Setup

1. Install the extension through the Extension Manager
2. Include the static TypoScript template
3. Configure language settings in your site configuration

### Content Element Settings

Configure the TextFlow content element in your page properties:

```typoscript
tt_content.text_flow {
    settings {
        enableTextFlow = 1
        defaultLanguage = en
    }
}
```

## Usage

### In Fluid Templates

Use the ViewHelper to process text:

```html
{namespace tf=PixelCoda\TextFlow\ViewHelpers}

<tf:process text="{text}" />
```

### In PHP

```php
use PixelCoda\TextFlow\Service\TextFlowService;

$textFlowService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(TextFlowService::class);
$processedText = $textFlowService->hyphenate($text);
```

### Content Element

1. Create a new content element
2. Select "TextFlow" from the content type list
3. Enter your text
4. Configure language and hyphenation settings

## Pattern Management

### Adding Custom Patterns

Create a pattern file in `Configuration/Patterns/`:

```php
return [
    'pattern' => 'your-pattern',
    'language' => 'en',
    'priority' => 1
];
```

### Pattern Format Rules

- Use hyphens (-) to indicate possible break points
- Patterns must be at least 2 characters long
- Priority determines pattern application order

## Development

### Running Tests

```bash
composer test
```

### Code Style

```bash
composer cs-fix
```

## Troubleshooting

### Common Issues

1. No hyphenation visible:
   - Check if TextFlow is enabled in TypoScript
   - Verify language settings
   - Clear TYPO3 cache

2. Pattern not working:
   - Check pattern format
   - Verify language assignment
   - Clear pattern cache

### Logging

TextFlow logs important operations to TYPO3's system log. Check the log for:
- Pattern loading issues
- Language configuration problems
- Processing errors

## Contributing

1. Fork the repository
2. Create a feature branch
3. Commit your changes
4. Push to the branch
5. Create a Pull Request

## License

GPL-2.0-or-later. See LICENSE file for details.

## Support

- Documentation: [docs.typo3.org](https://docs.typo3.org)
- Issue Tracker: [GitHub Issues](https://github.com/pixelcoda/text-flow/issues)
- Slack: #ext-text-flow on typo3.slack.com

# Meta Scraper

**Fast and reliable PHP library for extracting meta information from web pages.**

Extract Open Graph data, Schema.org structured data, and standard meta tags from any webpage with high-performance parsers and flexible architecture.

## ✨ Features

- **Multiple Parser Support** - Open Graph, Schema.org, and standard meta tags
- **High Performance** - Optimized regex and DOM-based parsing engines
- **Flexible Architecture** - Combine multiple parsers with fallback support
- **PHP 7.4+ Compatible** - Tested across PHP 7.4, 8.0, 8.1, 8.2, and 8.4
- **Zero Configuration** - Works out of the box with sensible defaults
- **Guzzle Integration** - Built-in HTTP client for fetching remote content

## 🚀 Quick Start

### Installation

```bash
composer require tomaj/meta-scraper
```

### Basic Usage

Extract meta information from HTML content:

```php
use Tomaj\Scraper\Scraper;
use Tomaj\Scraper\Parser\OgParser;

$scraper = new Scraper();
$meta = $scraper->parse($htmlContent, [new OgParser()]);

echo $meta->getTitle();        // Page title
echo $meta->getDescription();  // Page description  
echo $meta->getOgImage();      // Open Graph image
```

### Fetch and Parse URLs

Let the scraper handle HTTP requests for you:

```php
use Tomaj\Scraper\Scraper;
use Tomaj\Scraper\Parser\OgParser;

$scraper = new Scraper();
$meta = $scraper->parseUrl('https://example.com', [new OgParser()]);

var_dump($meta->toArray());
```

## 🔧 Available Parsers

Choose the right parser for your needs:

| Parser | Description | Best For |
|--------|-------------|----------|
| **OgParser** | Regex-based Open Graph parser | High performance, simple meta tags |
| **OgDomParser** | DOM-based Open Graph parser | Complex HTML, better accuracy |
| **SchemaParser** | JSON-LD Schema.org parser | Rich structured data |

### Combining Parsers

Use multiple parsers with automatic fallback - missing data from the first parser gets filled by subsequent parsers:

```php
use Tomaj\Scraper\Scraper;
use Tomaj\Scraper\Parser\{SchemaParser, OgParser, OgDomParser};

$scraper = new Scraper();
$parsers = [
    new SchemaParser(),  // Try Schema.org first
    new OgParser(),      // Fallback to Open Graph
    new OgDomParser()    // Final fallback with DOM parsing
];

$meta = $scraper->parseUrl('https://news-site.com/article', $parsers);
```

## 🛠️ Custom Parsers

Extend functionality by implementing the `ParserInterface`:

```php
use Tomaj\Scraper\Parser\ParserInterface;
use Tomaj\Scraper\Meta;

class CustomParser implements ParserInterface
{
    public function parse(string $content): Meta
    {
        $meta = new Meta();
        // Your custom parsing logic here
        return $meta;
    }
}
```

## 📋 Requirements

- **PHP 7.4+** (tested up to PHP 8.4)
- **ext-dom** (for OgDomParser)
- **ext-json** (for SchemaParser)
- **guzzlehttp/guzzle** (for URL fetching)

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## 📄 License

This project is licensed under the MIT License.

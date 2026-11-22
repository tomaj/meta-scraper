# Meta Scraper

**Extracts title, description, Open Graph and schema.org JSON-LD metadata from HTML pages.**

**Documentation: https://tomaj.github.io/meta-scraper/**

[Getting started](https://tomaj.github.io/meta-scraper/guide/getting-started) · [Parsers](https://tomaj.github.io/meta-scraper/guide/parsers) · [API reference](https://tomaj.github.io/meta-scraper/api/reference) · [Changelog](https://tomaj.github.io/meta-scraper/changelog)

[![Latest Stable Version](https://img.shields.io/packagist/v/tomaj/meta-scraper.svg)](https://packagist.org/packages/tomaj/meta-scraper)
[![CI](https://github.com/tomaj/meta-scraper/actions/workflows/ci.yml/badge.svg?branch=master)](https://github.com/tomaj/meta-scraper/actions/workflows/ci.yml?query=branch%3Amaster)
[![Coverage Status](https://coveralls.io/repos/github/tomaj/meta-scraper/badge.svg?branch=master)](https://coveralls.io/github/tomaj/meta-scraper?branch=master)
[![PHPStan](https://img.shields.io/badge/PHPStan-level%20max-brightgreen.svg)](https://phpstan.org/)
[![OpenSSF Scorecard](https://api.securityscorecards.dev/projects/github.com/tomaj/meta-scraper/badge)](https://scorecard.dev/viewer/?uri=github.com/tomaj/meta-scraper)
[![Documentation](https://img.shields.io/badge/docs-5.x-4f46e5.svg)](https://tomaj.github.io/meta-scraper/)
[![License](https://img.shields.io/packagist/l/tomaj/meta-scraper.svg)](LICENSE.md)
[![PHP Version](https://img.shields.io/badge/php-%5E8.4-777bb4.svg)](https://www.php.net/)

## What is meta-scraper?

meta-scraper reads the metadata a web page publishes about itself and returns it as one `Meta` object:

- the `<title>` and the `description`, `keywords` and `author` meta tags,
- Open Graph properties (`og:title`, `og:description`, `og:type`, `og:url`, `og:site_name`, `og:image`, `article:section`, `article:published_time`, `article:modified_time`),
- schema.org JSON-LD (`<script type="application/ld+json">`): headline, description, authors, sections, tags, image, URL and dates.

Each source has its own parser. Combine them and the first parser that finds a value wins.

## Installation

Requires PHP 8.4 or newer.

```bash
composer require tomaj/meta-scraper
```

## Usage

```php
use Tomaj\Scraper\Parser\OgParser;
use Tomaj\Scraper\Parser\SchemaParser;
use Tomaj\Scraper\Scraper;

$scraper = new Scraper();

// parse HTML you already have
$meta = $scraper->parse($html, [new SchemaParser(), new OgParser()]);

// or download the page with Guzzle and parse it
$meta = $scraper->parseUrl('https://example.com/article', [new SchemaParser(), new OgParser()]);

echo $meta->getTitle();
echo $meta->getOgImage();
foreach ($meta->getAuthors() as $author) {
    echo $author->name();
}
```

Parsers, fetching options (timeouts, User-Agent, your own Guzzle client), error handling and the full API are described in the **[documentation](https://tomaj.github.io/meta-scraper/)**.

## Contributing

Contributions are welcome. See [CONTRIBUTING.md](CONTRIBUTING.md) and the [Code of Conduct](CODE_OF_CONDUCT.md).

## Security

Do not report security vulnerabilities in public issues. See [SECURITY.md](SECURITY.md); it also explains why `parseUrl()` must not receive untrusted URLs without SSRF protection.

## License

MIT. See [LICENSE.md](LICENSE.md).

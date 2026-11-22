# Getting Started

## Requirements

- PHP 8.4 or newer
- `guzzlehttp/guzzle` 7.15.2+ or 8 (installed with the package, used by `parseUrl()`)
- `ext-dom` and `ext-libxml` for `OgDomParser` (enabled in most PHP builds)

## Installation

```bash
composer require tomaj/meta-scraper
```

## First example

Pass the HTML and a list of parsers to `Scraper::parse()`. Each parser reads one kind of metadata; the result is one `Meta` object.

<<< @/snippets/getting-started.php

Output:

<<< @/snippets/getting-started.php.out{text}

The title is the JSON-LD `headline`, because `SchemaParser` runs first. The description and image come from the meta tags, which `SchemaParser` did not find. See [Parsers](./parsers.md#combining-parsers) for the rules.

## Downloading the page

`parseUrl()` downloads the page with Guzzle and parses it:

```php
$meta = new Scraper()->parseUrl('https://example.com/article', [new SchemaParser(), new OgParser()]);
```

Timeouts, headers, your own Guzzle client and error handling are described in [Fetching Pages](./fetching.md).

::: warning Untrusted URLs
Do not pass URLs from users to `parseUrl()` without protection against server-side request forgery. See [Fetching Pages](./fetching.md#security).
:::

## What you get

`Meta` has a getter for every field; a field that no parser found is `null` (or an empty list):

| Getter | Source |
| --- | --- |
| `getTitle()` | `<title>`, JSON-LD `headline` |
| `getDescription()` | `<meta name="description">`, JSON-LD `description` |
| `getKeywords()` | `<meta name="keywords">` |
| `getAuthors()` | `<meta name="author">`, JSON-LD `author` (list of `Author`) |
| `getSections()` | `article:section`, JSON-LD `articleSection` / `articleTerms` (list of `Section`) |
| `getTags()` | JSON-LD `about` (list of `Tag`) |
| `getOgTitle()`, `getOgDescription()`, `getOgType()`, `getOgSiteName()` | Open Graph |
| `getOgUrl()` | `og:url`, JSON-LD `url` |
| `getOgImage()` | `og:image`, JSON-LD `image.url` |
| `getPublishedTime()`, `getModifiedTime()` | `article:published_time` / `article:modified_time`, JSON-LD `datePublished` / `dateModified` (`DateTime` in UTC) |

The full list of classes and methods is in the [API reference](../api/reference.md).

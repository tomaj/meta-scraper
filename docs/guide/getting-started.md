---
description: Install tomaj/meta-scraper, parse the metadata of your first HTML page and download a page with parseUrl().
---

# Getting started

## Requirements

| Requirement | Version | Why |
| --- | --- | --- |
| PHP | 8.4 or newer | |
| `guzzlehttp/guzzle` | `^7.15.2` or `^8.0.1` | installed with the package, used by `parseUrl()` |
| `ext-dom`, `ext-libxml` | any | only for `OgDomParser`; enabled in most PHP builds |

## Installation

```bash
composer require tomaj/meta-scraper
```

## Your first page

`Scraper::parse()` takes the HTML and a list of parsers. Each parser reads one kind of metadata; the scraper merges their results into one `Meta` object:

<<< @/snippets/getting-started.php

Output:

<<< @/snippets/getting-started.php.out{text}

The title is the JSON-LD `headline`, because `SchemaParser` runs first. The description and the image come from the meta tags, which `SchemaParser` did not find. [Combining parsers](./combining.md) explains the rules.

## Downloading a page

`parseUrl()` downloads the page with a `GET` request and parses it. `getBody()` returns the HTML it downloaded:

```php
use Tomaj\Scraper\Exception\FetchException;

$scraper = new Scraper(['timeout' => 10]);

try {
    $meta = $scraper->parseUrl($url, [new SchemaParser(), new OgDomParser()]);
    echo $meta->getOgSiteName(), ': ', $meta->getTitle(), "\n";
    echo strlen($scraper->getBody()) > 0 ? "body downloaded\n" : "empty body\n";
} catch (FetchException $e) {
    echo "Cannot download {$e->url}\n";
}
```

<!-- snippet: output -->
```text
Example Blog: Ten tips for PHP 8.4
body downloaded
```

Timeouts, headers, proxies and your own Guzzle client are in [Fetching pages](./fetching.md); every option is in the [configuration reference](./configuration.md).

::: warning URLs from users
Do not pass a URL that a user typed to `parseUrl()` without protection against server-side request forgery. See [Security](./security.md).
:::

## What you get

`Meta` has a getter for every field. A field that no parser found is `null`, a list that no parser found is `[]`:

| Getter | Type | Read from |
| --- | --- | --- |
| `getTitle()` | `?string` | `<title>`, JSON-LD `headline` |
| `getDescription()` | `?string` | `<meta name="description">`, JSON-LD `description` |
| `getKeywords()` | `?string` | `<meta name="keywords">` |
| `getAuthors()` | `list<Author>` | `<meta name="author">`, JSON-LD `author` |
| `getSections()` | `list<Section>` | `article:section`, JSON-LD `articleTerms` / `articleSection` |
| `getTags()` | `list<Tag>` | JSON-LD `about` |
| `getOgType()` | `?string` | `og:type` |
| `getOgTitle()` | `?string` | `og:title` |
| `getOgDescription()` | `?string` | `og:description` |
| `getOgUrl()` | `?string` | `og:url`, JSON-LD `url` |
| `getOgSiteName()` | `?string` | `og:site_name` |
| `getOgImage()` | `?string` | `og:image`, JSON-LD `image.url` |
| `getPublishedTime()` | `?DateTimeImmutable` (the page's offset) | `article:published_time`, JSON-LD `datePublished` |
| `getModifiedTime()` | `?DateTimeImmutable` (the page's offset) | `article:modified_time`, JSON-LD `dateModified` |

[The Meta object](./meta.md) describes every field; the [API reference](../api/reference.md) lists every class and method.

## Next steps

- [How it works](./how-it-works.md): what happens between the HTML and the `Meta` object.
- [Choosing a parser](./parsers.md): which parsers to use, and in which order.
- [Examples](../examples/index.md): seven runnable recipes, from a news article to an SSRF-safe link preview.

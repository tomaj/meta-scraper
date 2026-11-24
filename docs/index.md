---
layout: home
title: Meta Scraper – Open Graph and JSON-LD metadata for PHP
titleTemplate: false
description: 'PHP library that reads the title, description, Open Graph tags and schema.org JSON-LD of any HTML page into one Meta object, with Guzzle download, typed results and parsers you can combine or write yourself.'
hero:
  name: Meta Scraper
  text: Page metadata for PHP, in one object
  tagline: 'Title, description, authors, image, dates, sections and tags from the meta tags, Open Graph and schema.org JSON-LD of any HTML page. Combine parsers, download with Guzzle, get one Meta object back.'
  image:
    light: /illustrations/hero-light.svg
    dark: /illustrations/hero-dark.svg
    alt: An HTML page with title, Open Graph and JSON-LD tags goes through SchemaParser, OgDomParser and your own parser and becomes one Meta object with the title, an image, the authors and the publication date
  actions:
    - theme: brand
      text: Get started
      link: /guide/getting-started
    - theme: alt
      text: Examples
      link: /examples/
    - theme: alt
      text: GitHub
      link: https://github.com/tomaj/meta-scraper
features:
  - icon: '<svg class="ms-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" width="44" height="44" aria-hidden="true" focusable="false"><rect class="ms-soft" x="4" y="6" width="30" height="36" rx="6"/><path class="ms-ink" d="M11 15h16M11 22h12M11 29h8"/><path class="ms-acc-f" d="M24 36.5l6-6.5h12.5a2.5 2.5 0 0 1 2.5 2.5v8a2.5 2.5 0 0 1-2.5 2.5H30z"/><circle class="ms-ink-f" cx="30.5" cy="36.5" r="2"/></svg>'
    title: Meta tags and Open Graph
    details: '&lt;title&gt;, description, keywords and author, og:title, og:description, og:type, og:url, og:site_name, og:image, article:section and the published and modified times.'
    link: /guide/open-graph
    linkText: OgParser and OgDomParser
  - icon: '<svg class="ms-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" width="44" height="44" aria-hidden="true" focusable="false"><rect class="ms-soft" x="3" y="5" width="34" height="34" rx="7"/><path class="ms-ink" d="M14 13c-3 0-3 2-3 4.5S10 21 8.5 21c1.5 0 2.5 1 2.5 3.5s0 4.5 3 4.5M26 13c3 0 3 2 3 4.5s1 3.5 2.5 3.5c-1.5 0-2.5 1-2.5 3.5s0 4.5-3 4.5"/><circle class="ms-acc-f" cx="36" cy="36" r="9.5"/><path class="ms-on-acc" d="M32 36h8M36 32v8"/></svg>'
    title: schema.org JSON-LD
    details: 'Every application/ld+json block, @graph arrays and {"@id"} references. Picks the article (NewsArticle, BlogPosting, …) or the web page node and ignores the rest.'
    link: /guide/json-ld
    linkText: SchemaParser
  - icon: '<svg class="ms-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" width="44" height="44" aria-hidden="true" focusable="false"><rect class="ms-soft" x="4" y="5" width="26" height="10" rx="4"/><rect class="ms-soft" x="4" y="19" width="26" height="10" rx="4"/><rect class="ms-soft" x="4" y="33" width="26" height="10" rx="4"/><path class="ms-ink" d="M9 10h12M9 24h8M9 38h14"/><path class="ms-acc" d="M34 10c6 0 8 4 8 14s-2 14-8 14M38 20l4 4 4-4"/></svg>'
    title: Combine parsers
    details: Run several parsers in the order you choose. The first one that finds a value wins, the next ones fill only what is still missing.
    link: /guide/combining
    linkText: How merging works
  - icon: '<svg class="ms-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" width="44" height="44" aria-hidden="true" focusable="false"><circle class="ms-soft" cx="20" cy="20" r="16"/><path class="ms-ink" d="M4.5 20h31M20 4.5c5 4.5 7 9.5 7 15.5s-2 11-7 15.5M20 4.5c-5 4.5-7 9.5-7 15.5s2 11 7 15.5"/><circle class="ms-acc-f" cx="36" cy="36" r="9.5"/><path class="ms-on-acc" d="M36 31v9M32 36.5l4 4 4-4"/></svg>'
    title: Download with Guzzle
    details: parseUrl() fetches the page with Guzzle 7 or 8 and your timeouts, headers and proxy, or with your own configured client. Every failure is one FetchException.
    link: /guide/fetching
    linkText: Fetching pages
  - icon: '<svg class="ms-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" width="44" height="44" aria-hidden="true" focusable="false"><path class="ms-soft" d="M20 3l16 6v11c0 10.5-6.5 18-16 22C10.5 38 4 30.5 4 20V9z"/><path class="ms-ink" d="M12 21l6 6 10-11"/><circle class="ms-acc-f" cx="36" cy="36" r="9.5"/><path class="ms-on-acc" d="M36 31.5v5.5M36 40.5v.1"/></svg>'
    title: Never throws on bad HTML
    details: Unclosed tags, invalid JSON-LD, empty pages and legacy charsets give the values that could be read and null for the rest. OgDomParser decodes declared charsets.
    link: /examples/broken-pages
    linkText: See it on broken pages
  - icon: '<svg class="ms-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" width="44" height="44" aria-hidden="true" focusable="false"><path class="ms-soft" d="M8 10h9a4 4 0 1 1 8 0h9v9a4 4 0 1 1 0 8v9H8z"/><path class="ms-ink" d="M14 20h8M14 27h12"/><circle class="ms-acc-f" cx="37" cy="37" r="9.5"/><path class="ms-on-acc" d="M33.5 37.5l2.5 2.5 5-5.5"/></svg>'
    title: Your own parser
    details: One interface with one method. Read Twitter cards, microdata or anything else, and combine it with the built-in parsers.
    link: /guide/custom-parser
    linkText: Writing a parser
---

## What it looks like

<Diagram name="how-it-works" alt="parseUrl downloads the page with Guzzle, parse takes HTML you already have; the parsers run in order, each returns a new Meta, merge keeps the first value found, and the result is one Meta object" />

Give the scraper HTML (or a URL) and the parsers you want, in the order you trust them:

```php
use Tomaj\Scraper\Parser\OgDomParser;
use Tomaj\Scraper\Parser\SchemaParser;
use Tomaj\Scraper\Scraper;

$meta = new Scraper()->parse($html, [new SchemaParser(), new OgDomParser()]);

echo $meta->getTitle(), "\n";
echo $meta->getOgImage(), "\n";
echo $meta->getAuthors()[0]->name(), "\n";
echo $meta->getPublishedTime()?->format('Y-m-d H:i e'), "\n";
```

<!-- snippet: output -->
```text
Ten tips for PHP 8.4
https://blog.example/php84.jpg
Jana Nováková
2024-11-21 08:00 +00:00
```

The title and the author come from the JSON-LD `BlogPosting`, the image from `og:image`, which JSON-LD did not have. [`examples/open-graph-article`](/examples/open-graph-article) runs the same against a real web server.

## Where to go next

<LinkCards :items="[
  { title: 'Getting started', text: 'Install the package and scrape your first page.', link: '/guide/getting-started' },
  { title: 'Choosing a parser', text: 'OgParser, OgDomParser or SchemaParser, and in which order.', link: '/guide/parsers' },
  { title: 'Examples', text: 'Seven runnable recipes against fixture pages, tested in CI.', link: '/examples/' },
  { title: 'Security', text: 'Scraping URLs that users submit without opening an SSRF hole.', link: '/guide/security' },
]" />

## FAQ

### What does Meta Scraper do?

It reads the metadata a web page publishes about itself: the `<title>` and meta tags, Open Graph properties and schema.org JSON-LD, and returns it as one `Meta` object with a getter per field. See [Getting started](/guide/getting-started).

### Which PHP version does it need?

PHP 8.4 or newer and Guzzle 7.15.2+ or 8. `OgDomParser` also needs `ext-dom` and `ext-libxml`, which most PHP builds have. See [Requirements](/guide/getting-started#requirements).

### Does it run JavaScript?

No. It parses the HTML the server returns. For pages that build their metadata in the browser, render them with a headless browser and pass the HTML to `parse()`. See the [FAQ](/faq).

### Is it safe to scrape URLs that users paste?

Only with protection against server-side request forgery: allow http(s) only, reject private addresses, check redirects and set timeouts. The [link preview example](/examples/link-preview) shows a complete guard. See [Security](/guide/security).

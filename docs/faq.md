---
description: Frequently asked questions about meta-scraper - choosing parsers, titles, character sets, JavaScript pages, relative URLs, Twitter cards, testing and security.
---

# FAQ

## Which parser should I use?

`[new SchemaParser(), new OgDomParser()]` for news and blog articles: JSON-LD has the cleanest headline, authors and dates, Open Graph fills the rest. For link previews put `OgDomParser` first. Use `OgParser` only where `ext-dom` is not available. See [Choosing a parser](./guide/parsers.md).

## Why is `getTitle()` different from the `<title>` tag?

The first parser that finds a title wins. `SchemaParser` uses the JSON-LD `headline`, which usually lacks the " | Site name" suffix of `<title>`. Put `OgDomParser` first if you want the `<title>`. And `getOgTitle()` is a different field: the `og:title` tag. See [Combining parsers](./guide/combining.md).

## Why is a field `null` although the page has it?

Check that the tag is one the parsers read ([the list](./guide/open-graph.md#tags-that-are-read)): `<meta name="og:title">` (with `name` instead of `property`), `twitter:*`, `og:image:width` and `article:tag` are not read. For JSON-LD, check that the node is an article or web page type and that `image` is an object with a `url`, not a plain string ([schema.org JSON-LD](./guide/json-ld.md#fields)). With `OgParser`, check the attribute order.

## Why are non-ASCII characters garbled?

`OgParser` and `SchemaParser` return the bytes of the page as they are. If the page is not UTF-8 (for example Windows-1250), convert it with `iconv()` before parsing; `OgDomParser` decodes declared charsets itself. See [Character sets](./guide/fetching.md#character-sets) and the [broken pages example](./examples/broken-pages.md).

## Can I parse a page that needs JavaScript?

No. meta-scraper reads the HTML the server returns. For pages that build their metadata in the browser, render them with a headless browser first and pass the resulting HTML to `Scraper::parse()`.

## Why is `og:image` a relative URL?

The parsers return the value as the page wrote it. Resolve it against the page URL yourself; the [link preview example](./examples/link-preview.md) does, and also drops non-http(s) URLs.

## How do I read Twitter cards, microdata or other markup?

Write a parser: one class with one method. See [Writing a parser](./guide/custom-parser.md) and the [Twitter card example](./examples/twitter-card.md).

## Which time zone are the dates in?

In the offset the page wrote: `2026-11-20T06:30:00+01:00` comes back as a `DateTimeImmutable` with `+01:00`. A date without an offset is in PHP's default time zone. Convert them with `setTimezone()` for display. See [Dates](./guide/meta.md#dates).

## How do I set a timeout or a User-Agent?

Pass Guzzle options to the constructor: `new Scraper(['timeout' => 10, 'headers' => ['User-Agent' => 'MyBot/1.0']])`. A `headers` array replaces the default one. See the [configuration reference](./guide/configuration.md).

## How do I add retries, cookies or logging?

Configure a Guzzle client with the middleware or options you need and pass it as the second constructor argument. See [Your own Guzzle client](./guide/fetching.md#your-own-guzzle-client).

## How do I test code that uses `parseUrl()`?

Give the `Scraper` a Guzzle client with a `MockHandler`. See [Testing your code](./guide/testing.md) and the [testing example](./examples/testing.md).

## Is it safe to scrape URLs that users submit?

Only with protection against server-side request forgery, and only if you escape what you show. See [Security](./guide/security.md) and the [link preview example](./examples/link-preview.md).

## Does it work with Guzzle 7 and 8?

Yes: `^7.15.2 || ^8.0.1`. The Guzzle exception classes inside `FetchException::getPrevious()` differ between the versions; see [Error handling](./guide/errors.md#fetchexception).

## Where is the documentation for 4.x?

4.x is documented in its [README](https://github.com/tomaj/meta-scraper/blob/4.3.0/README.md). See [Upgrade 4.x → 5.0](./upgrade.md) for the differences.

---
description: Download pages with Scraper::parseUrl() and Guzzle - timeouts, User-Agent, redirects, proxies, your own Guzzle client, getBody() and character sets.
---

# Fetching pages

<!-- snippet: skip -->
```php
public function parseUrl(string $url, array $parsers): Meta;
```

`parseUrl()` sends one `GET` request for `$url` with Guzzle, stores the response body (see `getBody()`) and returns `parse($body, $parsers)`. A failed request throws `FetchException` ([Error handling](./errors.md)).

## The scraper's Guzzle client

<!-- snippet: skip -->
```php
public function __construct(array $guzzleOptions = [], ?\GuzzleHttp\ClientInterface $httpClient = null);
```

The scraper creates its Guzzle client once, in the constructor, and uses it for every `parseUrl()` call. Reuse one `Scraper` for many pages.

Without a client, `$guzzleOptions` configure the client the scraper creates. They are merged over the defaults with `array_merge()`:

| Default | Value |
| --- | --- |
| `connect_timeout` | `5` seconds |
| `headers` | `['User-Agent' => 'Tomaj\Scraper']` |

`array_merge()` is shallow: a `headers` array you pass **replaces** the default headers, so set the `User-Agent` in it if you want one:

<<< @/snippets/fetch.php

Every accepted option, with its type and default, is in the [configuration reference](./configuration.md).

::: tip Always set a timeout
The default has a connect timeout but no total `timeout`: a server that accepts the connection and then sends nothing, or sends a body forever, blocks `parseUrl()` indefinitely. Set `timeout` (and limit redirects) whenever you fetch hosts you do not control.
:::

## Your own Guzzle client

For anything the options do not cover (middleware such as retries or logging, cookies, authentication, `on_redirect` checks, a mock handler in tests), configure a Guzzle client yourself and pass it as the second argument:

- the scraper's defaults are **not** applied to your client: set the timeouts and the User-Agent yourself;
- `$guzzleOptions` become **request options**, sent with every request and merged by Guzzle over your client's configuration.

<<< @/snippets/own-client.php

The [link preview example](../examples/link-preview.md) uses its own client to check every redirect target and to pin the connection to a checked IP address.

## getBody()

<!-- snippet: skip -->
```php
public function getBody(): string;
```

Returns the body downloaded by the last **successful** `parseUrl()` call (`''` before the first one). A failed call does not change it. Use it to store the HTML, or to run other parsers without downloading the page again:

```php
$scraper->parseUrl($url, []);           // only download
$html = $scraper->getBody();

$social = $scraper->parse($html, [new OgDomParser()]);
$article = $scraper->parse($html, [new SchemaParser()]);
echo $social->getOgSiteName(), ' / ', $article->getTitle(), "\n";
```

<!-- snippet: output -->
```text
Example Blog / Ten tips for PHP 8.4
```

The body is kept in the `Scraper` object, so one `Scraper` used from several places at once (in an async runtime) would mix bodies; use one `Scraper` per worker.

## Character sets

`parseUrl()` passes the body to the parsers exactly as it was received; the `Content-Type` header is not used. How the parsers handle it:

- `OgDomParser` decodes a charset declared in the HTML (`<meta charset>`, `http-equiv`) and reads documents without a declaration as UTF-8.
- `OgParser` and `SchemaParser` return the bytes as they are.

For a page that is not UTF-8, convert the body before you give it to `OgParser` or `SchemaParser`. PHP's `mbstring` does not know Windows-1250, `iconv` does:

```php
$legacy = iconv('UTF-8', 'Windows-1250', '<title>Žltý kôň</title>');   // a Windows-1250 page

$utf8 = iconv('Windows-1250', 'UTF-8', $legacy);
echo new OgParser()->parse((string) $utf8)->getTitle(), "\n";
```

<!-- snippet: output -->
```text
Žltý kôň
```

The [broken pages example](../examples/broken-pages.md) serves a real Windows-1250 page and parses it with all three parsers.

## Compressed and large responses

Guzzle asks for and decodes gzip and deflate responses (`decode_content`, on by default). The whole body is loaded into memory before parsing; limit what you download from untrusted hosts (see [Security](./security.md#resource-limits)).

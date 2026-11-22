# Fetching Pages

`Scraper::parseUrl(string $url, array $parsers)` downloads the page with a `GET` request and parses it. `getBody()` returns the downloaded HTML afterwards.

## Options

The first constructor argument configures the Guzzle client the scraper creates. It is merged over the defaults: a 5 second connect timeout and the `Tomaj\Scraper` User-Agent. A `headers` array replaces the default headers.

<<< @/snippets/fetch.php

Supported options: `connect_timeout`, `timeout`, `read_timeout`, `headers`, `proxy`, `allow_redirects`, `verify`, `http_errors`, `decode_content`, `force_ip_resolve` and `version`, with the meaning they have in [Guzzle](https://docs.guzzlephp.org/en/stable/request-options.html).

::: tip
Without a `timeout`, a server that accepts the connection but never answers blocks `parseUrl()` indefinitely. Set one.
:::

## Your own Guzzle client

For anything else (middleware, retries, cookies, authentication, a mock handler in your tests), configure a Guzzle client and pass it as the second argument. The defaults are not applied to it; the first argument becomes request options sent with every request.

<<< @/snippets/own-client.php

## Errors

Every failure of the download throws `Tomaj\Scraper\Exception\FetchException`: connection errors, timeouts, TLS errors, too many redirects, and 4xx/5xx responses (unless you set `http_errors` to `false`). The original Guzzle exception is `getPrevious()`, the URL is `$e->url`.

All exceptions of the package implement `Tomaj\Scraper\Exception\ScraperException`:

```php
use Tomaj\Scraper\Exception\ScraperException;

try {
    $meta = $scraper->parseUrl($url, $parsers);
} catch (ScraperException $e) {
    // ...
}
```

Parsers do not throw on bad HTML or invalid JSON-LD: they return what they could read.

## Character sets

`parseUrl()` passes the body to the parsers as it was received. `OgDomParser` honours a charset declared in the HTML and reads undeclared documents as UTF-8. `OgParser` and `SchemaParser` return the bytes unchanged, so for pages that are not UTF-8 convert the body first:

```php
$scraper->parseUrl($url, []);
$html = mb_convert_encoding($scraper->getBody(), 'UTF-8', 'Windows-1250');
$meta = $scraper->parse($html, [new OgParser()]);
```

## Security

`parseUrl()` requests whatever URL it gets. When the URL comes from a user (a link preview, an "import from URL" form), an attacker can make your server request internal addresses: `localhost`, private networks, or the cloud metadata service at `169.254.169.254`. This is server-side request forgery (SSRF). Before you call `parseUrl()` with such a URL:

- allow only `http` and `https`;
- resolve the host name and reject private, loopback and link-local addresses, and pin the request to the checked address (for example with Guzzle's `curl` options `CURLOPT_RESOLVE`), so a DNS change between the check and the request does not bypass it;
- disable redirects (`'allow_redirects' => false`) or check every redirect target the same way (`on_redirect` in your own client);
- set `timeout` and limit the response size, so a huge or slow response cannot tie up a worker.

Or send the requests through an egress proxy that blocks internal networks (`proxy` option).

The parsed values come from the page author: escape them before you put them into HTML, and check URLs such as `getOgImage()` (scheme, host) before you fetch or embed them.

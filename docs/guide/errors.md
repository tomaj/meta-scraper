---
description: Every exception meta-scraper throws - FetchException, MissingExtensionException and the ScraperException interface - when, and how to handle them.
---

# Error handling

meta-scraper throws in two situations only: a page cannot be downloaded, or a parser cannot work in this PHP. Parsing itself never throws.

| Exception | Extends | Thrown by | When |
| --- | --- | --- | --- |
| `Tomaj\Scraper\Exception\FetchException` | `RuntimeException` | `Scraper::parseUrl()` | the request failed |
| `Tomaj\Scraper\Exception\MissingExtensionException` | `LogicException` | `new OgDomParser()` | `ext-dom` or `ext-libxml` is not loaded |
| `Tomaj\Scraper\Exception\ScraperException` | interface (`Throwable`) | | implemented by both, to catch everything from the package |

## FetchException

<!-- snippet: skip -->
```php
final class FetchException extends \RuntimeException implements ScraperException
{
    public readonly string $url;                 // the URL you passed to parseUrl()
    // getPrevious(): the Guzzle exception
    // getMessage(): 'Cannot fetch "<url>": <Guzzle message>'
}
```

Every `GuzzleHttp\Exception\GuzzleException` from the request is wrapped, so these all become a `FetchException`:

| Cause | Previous exception (Guzzle 7) | Previous exception (Guzzle 8) |
| --- | --- | --- |
| 4xx response (with `http_errors` on, the default) | `ClientException` | `ClientException` |
| 5xx response | `ServerException` | `ServerException` |
| more redirects than `allow_redirects.max` | `TooManyRedirectsException` | `TooManyRedirectsException` |
| connection refused, DNS failure | `ConnectException` | `ConnectException` |
| `timeout` reached | `ConnectException` | `NetworkTimeoutException` |
| TLS errors, invalid URLs, other transfer errors | another `GuzzleException` | another `GuzzleException` |

The exact Guzzle classes differ between Guzzle 7 and 8. To react to an HTTP status, check for `BadResponseException`, which both versions have:

```php
use GuzzleHttp\Exception\BadResponseException;

$scraper = new Scraper(['timeout' => 5]);
$missing = str_replace('article.html', 'missing.html', $url);

try {
    $meta = $scraper->parseUrl($missing, $parsers);
} catch (FetchException $e) {
    $previous = $e->getPrevious();
    if ($previous instanceof BadResponseException) {
        echo 'HTTP ', $previous->getResponse()->getStatusCode(), ' for ', basename($e->url), "\n";
    } else {
        echo 'Network error for ', $e->url, "\n";
    }
}
```

<!-- snippet: output -->
```text
HTTP 404 for missing.html
```

The [fetch errors example](../examples/fetch-errors.md) triggers every cause against a local server and runs the same on Guzzle 7 and 8.

After a `FetchException`, `getBody()` still returns the body of the previous successful call.

### Exceptions you throw in your own client

`FetchException` wraps only Guzzle exceptions. An exception thrown by your own middleware or by an `on_redirect` callback on your own client (not a `GuzzleException`) is not wrapped and leaves `parseUrl()` as it is. The [link preview example](../examples/link-preview.md) relies on this: its `BlockedUrlException` from `on_redirect` reaches the caller unchanged.

## MissingExtensionException

`new OgDomParser()` checks for `ext-dom` and `ext-libxml` and throws when one is missing, with a message such as `Tomaj\Scraper\Parser\OgDomParser requires the PHP extension "dom"`. It is a `LogicException`: fix the environment, or fall back to `OgParser`:

```php
try {
    $metaTags = new OgDomParser();
} catch (MissingExtensionException) {
    $metaTags = new OgParser();
}
echo $metaTags::class, "\n";
```

<!-- snippet: output -->
```text
Tomaj\Scraper\Parser\OgDomParser
```

## Catching everything

Both exceptions implement `ScraperException`:

```php
try {
    $meta = new Scraper(['timeout' => 5])->parseUrl('http://127.0.0.1:9/', [new OgDomParser()]);
} catch (ScraperException $e) {
    echo $e::class, "\n";
}
```

<!-- snippet: output -->
```text
Tomaj\Scraper\Exception\FetchException
```

## Bad HTML does not throw

The parsers return what they could read and leave the rest `null`:

| Input | Result |
| --- | --- |
| `''` (empty string) | an empty `Meta` from every parser |
| unclosed tags, upper-case tags, unquoted attributes | `OgDomParser` reads them; `OgParser` may miss them |
| invalid JSON in a JSON-LD block | the block is skipped, other blocks are used |
| a JSON-LD value of an unexpected type | ignored (`null`) |
| a date that is not absolute (`not a date`, `yesterday noon`, `2026-02-30`) | ignored (`null`) |
| a page in another charset | `OgDomParser` decodes a declared charset, the other parsers return the raw bytes |

```php
$meta = $scraper->parse('<TITLE>Broken page</TITLE><div><p>unclosed<script type="application/ld+json">{oops</script>', $parsers);
var_dump($meta->getTitle(), $meta->getAuthors());
```

<!-- snippet: output -->
```text
string(11) "Broken page"
array(0) {
}
```

Check the fields you need and fall back to your own defaults: an empty title is a normal result, not an error. The [broken pages example](../examples/broken-pages.md) shows the parsers on real broken pages.

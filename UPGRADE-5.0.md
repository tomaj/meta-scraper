# Upgrading from meta-scraper 4.x to 5.0

This file lists the changes in 5.0 that may need work in your application. The full list of changes is in [CHANGELOG.md](CHANGELOG.md).

Checklist:

- PHP 8.4 and Guzzle 7.9 or 8: [Requirements](#requirements).
- `parseUrl()` throws `FetchException` instead of Guzzle exceptions: [Exceptions](#parseurl-throws-fetchexception).
- Classes are `final`, value objects are `readonly`, `protected` members are gone: [Final classes](#classes-are-final).
- `Scraper` options are typed; options outside the documented list go into your own Guzzle client: [Scraper options](#scraper-options-and-your-own-guzzle-client).
- `OgDomParser` throws without `ext-dom`: [OgDomParser](#ogdomparser-requires-ext-dom-and-ext-libxml).
- Parsers return different (correct) values for some pages: [Parser output](#parser-output-changes).

## Requirements

- PHP 8.4 or newer (4.x ran on PHP 7.1+).
- `guzzlehttp/guzzle` `^7.9 || ^8.0`. Guzzle 6 is no longer supported.

## `parseUrl()` throws `FetchException`

Before, a failed download let the Guzzle exception escape. Now every failure (connection error, timeout, TLS error, too many redirects, 4xx/5xx response) is a `Tomaj\Scraper\Exception\FetchException`. The Guzzle exception is its previous exception. All exceptions of the package implement `Tomaj\Scraper\Exception\ScraperException`.

```php
// 4.x
try {
    $meta = $scraper->parseUrl($url, $parsers);
} catch (\GuzzleHttp\Exception\GuzzleException $e) {
    // ...
}

// 5.0
try {
    $meta = $scraper->parseUrl($url, $parsers);
} catch (\Tomaj\Scraper\Exception\FetchException $e) {
    $guzzleException = $e->getPrevious();
    $failedUrl = $e->url;
}
```

## Classes are final

`Scraper`, `Meta`, `Author`, `Section`, `Tag`, `OgParser`, `OgDomParser` and `SchemaParser` are `final`; `Author`, `Section` and `Tag` are `readonly`. `Scraper::$guzzleOptions`, `OgDomParser::$meta`, `OgDomParser::$allowedAttributes`, `OgDomParser::processMetaTag()` and `OgDomParser::fixMetaTag()` (all `protected`) are removed.

If you extended a class, compose it instead, or implement `ParserInterface`:

```php
// 4.x
class MyParser extends OgParser
{
    public function parse(string $content): Meta
    {
        $meta = parent::parse($content);
        // ...
        return $meta;
    }
}

// 5.0
final class MyParser implements ParserInterface
{
    public function __construct(private readonly OgParser $og = new OgParser()) {}

    public function parse(string $content): Meta
    {
        $meta = $this->og->parse($content);
        // ...
        return $meta;
    }
}
```

If you changed the default Guzzle options by overriding `Scraper::$guzzleOptions`, pass them to the constructor instead (next section).

## Scraper options and your own Guzzle client

`new Scraper(array $guzzleOptions = [])` still works and the options are still merged over the defaults (5 second connect timeout, `Tomaj\Scraper` User-Agent). The options array is now typed for static analysis: `connect_timeout`, `timeout`, `read_timeout`, `headers`, `proxy`, `allow_redirects`, `verify`, `http_errors`, `decode_content`, `force_ip_resolve` and `version`. Other Guzzle options still reach Guzzle at runtime, but PHPStan and Psalm report them. For those, pass your own client as the second argument; the defaults are not applied to it, and the options array becomes request options:

```php
// 4.x
$scraper = new Scraper(['handler' => $stack, 'cookies' => true]);

// 5.0
$scraper = new Scraper([], new \GuzzleHttp\Client([
    'handler' => $stack,
    'cookies' => true,
    'connect_timeout' => 5,
    'headers' => ['User-Agent' => 'MyBot/1.0'],
]));
```

The client is created once in the constructor instead of on every `parseUrl()` call.

## OgDomParser requires ext-dom and ext-libxml

Without `ext-dom` or `ext-libxml`, `new OgDomParser()` used to succeed and then fail inside `parse()` (or find nothing). It now throws `Tomaj\Scraper\Exception\MissingExtensionException` (a `LogicException`). Use `OgParser` where the extensions are not available.

## Parser output changes

These are bug fixes, but they change what the parsers return:

- `OgDomParser` returns a new `Meta` from every `parse()` call. Before, one parser instance returned the same object every time, with authors and sections piling up across pages ([#21](https://github.com/tomaj/meta-scraper/issues/21)). If you relied on that accumulation, merge the results yourself with `Meta::merge()`.
- `OgDomParser` reads documents without a charset declaration as UTF-8. Before, their non-ASCII text came back garbled (`Å½ltÃ½` instead of `Žltý`) ([#22](https://github.com/tomaj/meta-scraper/issues/22)).
- `SchemaParser` reads standard JSON-LD (`<script type="application/ld+json">` without `id="schema"`, multiline JSON, several blocks, `@graph`, `{"@id": …}` references). Pages that used to give an empty result from `SchemaParser` may now return a title, authors, dates and more. When `SchemaParser` runs first, its values win over the Open Graph ones, so `getTitle()` may now be the article headline instead of the `<title>` ([#23](https://github.com/tomaj/meta-scraper/issues/23)). Pages with an `id="schema"` block keep using it.
- `OgDomParser` restores the previous `libxml_use_internal_errors()` setting after parsing; 4.x left internal errors switched on for the rest of the process.

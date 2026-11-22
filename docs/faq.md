# FAQ

## Which parser should I use?

`[new SchemaParser(), new OgDomParser()]` for news and blog articles: JSON-LD has the cleanest headline, authors and dates, Open Graph fills the rest. Use `OgParser` instead of `OgDomParser` where `ext-dom` is not available or for HTML fragments where speed matters more than attribute order. See [Combining parsers](./guide/parsers.md#combining-parsers).

## Why is `getTitle()` different from the `<title>` tag?

The first parser that finds a title wins. `SchemaParser` uses the JSON-LD `headline`, which usually lacks the " | Site name" suffix of `<title>`. Put `OgDomParser` first if you want the `<title>`.

## Why are non-ASCII characters garbled?

`OgParser` and `SchemaParser` return the bytes of the page as they are. If the page is not UTF-8 (for example `windows-1250`), convert it to UTF-8 before parsing; see [Character sets](./guide/fetching.md#character-sets). `OgDomParser` decodes declared charsets itself.

## Can I parse a page that needs JavaScript?

No. meta-scraper reads the HTML the server returns. For pages that build their metadata in the browser, render them with a headless browser first and pass the resulting HTML to `Scraper::parse()`.

## How do I test code that uses `parseUrl()`?

Pass a Guzzle client with a `MockHandler` as the second constructor argument:

```php
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

$client = new Client(['handler' => HandlerStack::create(new MockHandler([
    new Response(200, [], '<title>Test page</title>'),
]))]);

$meta = new Scraper([], $client)->parseUrl('https://example.com/', [new OgParser()]);
```

## Is it safe to scrape URLs that users submit?

Only with protection against server-side request forgery; see [Security](./guide/fetching.md#security).

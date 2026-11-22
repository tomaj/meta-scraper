# API Reference

All classes are in the `Tomaj\Scraper` namespace and are `final`. Extension happens through `ParserInterface`.

## Scraper

```php
final class Scraper
{
    public function __construct(array $guzzleOptions = [], ?GuzzleHttp\ClientInterface $httpClient = null);
    public function parse(string $content, array $parsers): Meta;
    public function parseUrl(string $url, array $parsers): Meta;
    public function getBody(): string;
}
```

### `__construct(array $guzzleOptions = [], ?ClientInterface $httpClient = null)`

- Without `$httpClient`: creates a `GuzzleHttp\Client` with `$guzzleOptions` merged over the defaults `['connect_timeout' => 5, 'headers' => ['User-Agent' => 'Tomaj\Scraper']]`.
- With `$httpClient`: uses that client as it is; `$guzzleOptions` are request options sent with every request.

`$guzzleOptions` accepts `connect_timeout`, `timeout`, `read_timeout` (int|float), `headers` (`array<string, string|list<string>>`), `proxy`, `allow_redirects`, `verify`, `http_errors`, `decode_content`, `force_ip_resolve` and `version`. See [Fetching Pages](../guide/fetching.md#options).

### `parse(string $content, array $parsers): Meta`

Runs each `ParserInterface` of `$parsers` on `$content` in order and merges the results into a new `Meta` (the first value found wins, see `Meta::merge()`). No parsers give an empty `Meta`.

### `parseUrl(string $url, array $parsers): Meta`

Sends `GET $url`, stores the body for `getBody()` and returns `parse($body, $parsers)`.

Throws `Exception\FetchException` when the request fails or the response is 4xx/5xx.

### `getBody(): string`

The body downloaded by the last successful `parseUrl()` call, `''` before the first one.

## Meta

A mutable result object. Setters return the same `Meta` for chaining.

| Getter | Setter / adder | Type |
| --- | --- | --- |
| `getTitle()` | `setTitle(string)` | `?string` |
| `getDescription()` | `setDescription(string)` | `?string` |
| `getKeywords()` | `setKeywords(string)` | `?string` |
| `getAuthors()` | `addAuthor(Author)`, `addAuthorByName(string)` | `list<Author>` |
| `getSections()` | `addSection(Section)`, `addSectionByName(string)` | `list<Section>` |
| `getTags()` | `addTag(Tag)` | `list<Tag>` |
| `getOgType()` | `setOgType(string)` | `?string` |
| `getOgTitle()` | `setOgTitle(string)` | `?string` |
| `getOgDescription()` | `setOgDescription(string)` | `?string` |
| `getOgUrl()` | `setOgUrl(string)` | `?string` |
| `getOgSiteName()` | `setOgSiteName(string)` | `?string` |
| `getOgImage()` | `setOgImage(string)` | `?string` |
| `getPublishedTime()` | `setPublishedTime(DateTime\|string)` | `?DateTime` |
| `getModifiedTime()` | `setModifiedTime(DateTime\|string)` | `?DateTime` |

`setPublishedTime()` and `setModifiedTime()` parse a string with `strtotime()` and store it as a UTC `DateTime`; a string `strtotime()` cannot parse is ignored.

### `merge(Meta $meta): Meta`

Copies every value of `$meta` into this object where this object has none (`null`, `''` or `'0'`; for authors, sections and tags an empty list) and returns `$this`.

## Author, Section, Tag

Readonly value objects with the same shape:

```php
final readonly class Author // Section, Tag
{
    public function __construct(?string $id, ?string $name);
    public function id(): ?string;
    public function name(): ?string;
}
```

`id()` is the schema.org `@id` when the parser knows it, otherwise `null`.

## Parsers

```php
namespace Tomaj\Scraper\Parser;

interface ParserInterface
{
    public function parse(string $content): Meta;
}
```

An implementation returns a new `Meta` from every call and does not throw on malformed input.

| Class | Description |
| --- | --- |
| `Parser\OgParser` | Meta tags and Open Graph with regular expressions. |
| `Parser\OgDomParser` | Meta tags and Open Graph with `ext-dom`. The constructor throws `Exception\MissingExtensionException` without `ext-dom` or `ext-libxml`. |
| `Parser\SchemaParser` | schema.org JSON-LD. |

What each parser reads is described in [Parsers](../guide/parsers.md).

## Exceptions

| Class | Extends | Thrown by |
| --- | --- | --- |
| `Exception\ScraperException` | interface, `Throwable` | implemented by every exception below |
| `Exception\FetchException` | `RuntimeException` | `Scraper::parseUrl()`; `getPrevious()` is the Guzzle exception, `public readonly string $url` the requested URL |
| `Exception\MissingExtensionException` | `LogicException` | `new OgDomParser()` without `ext-dom` / `ext-libxml` |

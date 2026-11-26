---
description: API reference of tomaj/meta-scraper 5.x - every public class, method, parameter, return value and exception.
---

# API reference

All classes are in the `Tomaj\Scraper` namespace and `final`; extension happens through `ParserInterface`. Version 5.x, PHP 8.4+.

| Class | Kind | Page |
| --- | --- | --- |
| [`Scraper`](#scraper) | service | [Fetching pages](../guide/fetching.md) |
| [`Meta`](#meta) | result | [The Meta object](../guide/meta.md) |
| [`Author`, `Section`, `Tag`](#author-section-tag) | readonly values | [The Meta object](../guide/meta.md#author-section-and-tag) |
| [`Parser\ParserInterface`](#parserinterface) | interface | [Writing a parser](../guide/custom-parser.md) |
| [`Parser\OgParser`](#ogparser) | parser | [Meta tags and Open Graph](../guide/open-graph.md#ogparser) |
| [`Parser\OgDomParser`](#ogdomparser) | parser | [Meta tags and Open Graph](../guide/open-graph.md#ogdomparser) |
| [`Parser\SchemaParser`](#schemaparser) | parser | [schema.org JSON-LD](../guide/json-ld.md) |
| [`Exception\ScraperException`](#scraperexception) | interface | [Error handling](../guide/errors.md) |
| [`Exception\FetchException`](#fetchexception) | exception | [Error handling](../guide/errors.md#fetchexception) |
| [`Exception\MissingExtensionException`](#missingextensionexception) | exception | [Error handling](../guide/errors.md#missingextensionexception) |

## Scraper

<!-- snippet: skip -->
```php
final class Scraper
{
    public function __construct(array $guzzleOptions = [], ?\GuzzleHttp\ClientInterface $httpClient = null);
    public function parseUrl(string $url, array $parsers): Meta;
    public function parse(string $content, array $parsers): Meta;
    public function getBody(): string;
}
```

### __construct()

| Parameter | Type | Default | Description |
| --- | --- | --- | --- |
| `$guzzleOptions` | `GuzzleOptions` (array shape) | `[]` | Without `$httpClient`: configuration of the Guzzle client the scraper creates, merged with `array_merge()` over `['connect_timeout' => 5, 'headers' => ['User-Agent' => 'Tomaj\Scraper']]`. With `$httpClient`: request options sent with every request. Keys: see [Configuration reference](../guide/configuration.md#options). |
| `$httpClient` | `?GuzzleHttp\ClientInterface` | `null` | Your own configured Guzzle client, used as it is (the defaults are not applied). |

The Guzzle client is created once, here. Throws nothing.

### parseUrl()

| Parameter | Type | Description |
| --- | --- | --- |
| `$url` | `string` | The URL to download with `GET`. |
| `$parsers` | `array<ParserInterface>` | Parsers to run, in order of priority. May be empty. |

**Returns** `Meta`: the merged result of the parsers on the downloaded body.
**Throws** `Exception\FetchException` when Guzzle throws (connection error, timeout, TLS error, too many redirects, a 4xx/5xx response unless `http_errors` is `false`).
**Side effect:** on success, stores the body for `getBody()`.

Does not validate `$url`; see [Security](../guide/security.md) before passing URLs from users.

### parse()

| Parameter | Type | Description |
| --- | --- | --- |
| `$content` | `string` | The HTML (or any string). |
| `$parsers` | `array<ParserInterface>` | Parsers to run, in order of priority. May be empty. |

**Returns** a new `Meta`: for each parser in order, `$meta->merge($parser->parse($content))`. An empty parser list gives an empty `Meta`. Throws nothing itself (only what a custom parser throws).

### getBody()

**Returns** `string`: the body downloaded by the last successful `parseUrl()` call, `''` before the first one.

## Meta

A mutable result object. Every setter and adder returns `$this` (`Meta`).

| Method | Returns | Notes |
| --- | --- | --- |
| `getTitle()` / `setTitle(string $title)` | `?string` / `Meta` | |
| `getDescription()` / `setDescription(string $description)` | `?string` / `Meta` | |
| `getKeywords()` / `setKeywords(string $keywords)` | `?string` / `Meta` | the whole `content`, not split |
| `getAuthors()` | `list<Author>` | |
| `addAuthor(Author $author)` | `Meta` | appends |
| `addAuthorByName(string $name)` | `Meta` | `addAuthor(new Author(null, $name))` |
| `getSections()` | `list<Section>` | |
| `addSection(Section $section)` | `Meta` | appends |
| `addSectionByName(string $name)` | `Meta` | `addSection(new Section(null, $name))` |
| `getTags()` | `list<Tag>` | |
| `addTag(Tag $tag)` | `Meta` | appends |
| `getOgType()` / `setOgType(string $ogType)` | `?string` / `Meta` | |
| `getOgTitle()` / `setOgTitle(string $ogTitle)` | `?string` / `Meta` | |
| `getOgDescription()` / `setOgDescription(string $ogDescription)` | `?string` / `Meta` | |
| `getOgUrl()` / `setOgUrl(string $ogUrl)` | `?string` / `Meta` | |
| `getOgSiteName()` / `setOgSiteName(string $ogSiteName)` | `?string` / `Meta` | |
| `getOgImage()` / `setOgImage(string $ogImage)` | `?string` / `Meta` | |
| `getPublishedTime()` / `setPublishedTime(DateTime\|string $publishedTime)` | `?DateTime` / `Meta` | a string is parsed with `strtotime()` into a UTC `DateTime`; an unparsable string is ignored |
| `getModifiedTime()` / `setModifiedTime(DateTime\|string $modifiedTime)` | `?DateTime` / `Meta` | as above |
| `merge(Meta $meta)` | `Meta` (`$this`) | copies each value of `$meta` into fields of `$this` that are empty (`null`, `''`, `'0'`, `[]`); lists are copied whole |

The constructor takes no arguments; every field starts as `null` or `[]`.

## Author, Section, Tag

<!-- snippet: skip -->
```php
final readonly class Author // Section and Tag have the same shape
{
    public function __construct(?string $id, ?string $name);
    public function id(): ?string;    // schema.org @id, or null
    public function name(): ?string;
}
```

`id()` is set by `SchemaParser` for authors, `articleTerms` sections and `about` tags that have an `@id`; it is `null` for values from meta tags and for plain-string JSON-LD values.

## ParserInterface

<!-- snippet: skip -->
```php
namespace Tomaj\Scraper\Parser;

interface ParserInterface
{
    public function parse(string $content): Meta;
}
```

Contract: return a new `Meta` on every call, do not throw on malformed input, do not keep state between calls.

## OgParser

`Tomaj\Scraper\Parser\OgParser implements ParserInterface`. Constructor without arguments. `parse()` reads `<title>`, the `description`, `keywords` and `author` meta tags and the Open Graph / `article:` properties with regular expressions; first occurrence of each tag, `null` for a tag whose `content` comes before `name`/`property`. See [OgParser](../guide/open-graph.md#ogparser).

## OgDomParser

`Tomaj\Scraper\Parser\OgDomParser implements ParserInterface`.

- `__construct()`: throws `Exception\MissingExtensionException` when `ext-dom` or `ext-libxml` is not loaded.
- `parse(string $content): Meta`: the same tags as `OgParser`, with the HTML5 parser `Dom\HTMLDocument`; any attribute order, case-insensitive attribute values, all `author` and `article:section` tags, the last occurrence of the other tags; entities decoded once; declared charsets decoded, UTF-8 otherwise. See [OgDomParser](../guide/open-graph.md#ogdomparser).

## SchemaParser

`Tomaj\Scraper\Parser\SchemaParser implements ParserInterface`. Constructor without arguments. `parse()` reads schema.org JSON-LD: the `id="schema"` block, else the first article node, else the first web page node, with `@graph` and `@id` references resolved. See [schema.org JSON-LD](../guide/json-ld.md) for the node selection and the field mapping.

## ScraperException

`interface Tomaj\Scraper\Exception\ScraperException extends \Throwable`, implemented by every exception of the package.

## FetchException

`final class Tomaj\Scraper\Exception\FetchException extends \RuntimeException implements ScraperException`

| Member | Description |
| --- | --- |
| `public readonly string $url` | The URL passed to `parseUrl()`. |
| `getPrevious()` | The `GuzzleHttp\Exception\GuzzleException` that caused it. |
| `getMessage()` | `Cannot fetch "<url>": <Guzzle message>` |
| `__construct(string $url, \Throwable $previous)` | Code `0`. |

## MissingExtensionException

`final class Tomaj\Scraper\Exception\MissingExtensionException extends \LogicException implements ScraperException`

| Member | Description |
| --- | --- |
| `__construct(string $parser, string $extension)` | Message: `<parser class> requires the PHP extension "<extension>"`. |

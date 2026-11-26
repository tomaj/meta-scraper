---
description: Referencia API tomaj/meta-scraper 5.x - každá verejná trieda, metóda, parameter, návratová hodnota a výnimka.
---

# Referencia API

Všetky triedy sú v mennom priestore `Tomaj\Scraper` a sú `final`; rozširuje sa cez `ParserInterface`. Verzia 5.x, PHP 8.4+.

| Trieda | Druh | Stránka |
| --- | --- | --- |
| [`Scraper`](#scraper) | služba | [Sťahovanie stránok](../guide/fetching.md) |
| [`Meta`](#meta) | výsledok | [Objekt Meta](../guide/meta.md) |
| [`Author`, `Section`, `Tag`](#author-section-tag) | readonly hodnoty | [Objekt Meta](../guide/meta.md#author-section-and-tag) |
| [`Parser\ParserInterface`](#parserinterface) | rozhranie | [Vlastný parser](../guide/custom-parser.md) |
| [`Parser\OgParser`](#ogparser) | parser | [Meta tagy a Open Graph](../guide/open-graph.md#ogparser) |
| [`Parser\OgDomParser`](#ogdomparser) | parser | [Meta tagy a Open Graph](../guide/open-graph.md#ogdomparser) |
| [`Parser\SchemaParser`](#schemaparser) | parser | [schema.org JSON-LD](../guide/json-ld.md) |
| [`Exception\ScraperException`](#scraperexception) | rozhranie | [Spracovanie chýb](../guide/errors.md) |
| [`Exception\FetchException`](#fetchexception) | výnimka | [Spracovanie chýb](../guide/errors.md#fetchexception) |
| [`Exception\MissingExtensionException`](#missingextensionexception) | výnimka | [Spracovanie chýb](../guide/errors.md#missingextensionexception) |

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

| Parameter | Typ | Predvolená hodnota | Opis |
| --- | --- | --- | --- |
| `$guzzleOptions` | `GuzzleOptions` (array shape) | `[]` | Bez `$httpClient`: konfigurácia Guzzle klienta, ktorého si scraper vytvorí, zlúčená cez `array_merge()` s `['connect_timeout' => 5, 'headers' => ['User-Agent' => 'Tomaj\Scraper']]`. S `$httpClient`: request options posielané s každou požiadavkou. Kľúče: pozrite [Prehľad konfigurácie](../guide/configuration.md#options). |
| `$httpClient` | `?GuzzleHttp\ClientInterface` | `null` | Váš vlastný nakonfigurovaný Guzzle klient, použije sa tak, ako je (predvolené hodnoty sa neuplatnia). |

Guzzle klient sa vytvorí raz, práve tu. Nevyhadzuje žiadnu výnimku.

### parseUrl()

| Parameter | Typ | Opis |
| --- | --- | --- |
| `$url` | `string` | URL, ktorá sa stiahne cez `GET`. |
| `$parsers` | `array<ParserInterface>` | Parsery, ktoré sa spustia, v poradí priority. Zoznam môže byť prázdny. |

**Vracia** `Meta`: zlúčený výsledok parserov nad stiahnutým telom odpovede.
**Vyhadzuje** `Exception\FetchException`, keď výnimku vyhodí Guzzle (chyba spojenia, timeout, chyba TLS, priveľa presmerovaní, odpoveď 4xx/5xx, ak `http_errors` nie je `false`).
**Vedľajší efekt:** pri úspechu uloží telo odpovede pre `getBody()`.

`$url` nekontroluje; skôr než jej odovzdáte URL od používateľov, prečítajte si [Bezpečnosť](../guide/security.md).

### parse()

| Parameter | Typ | Opis |
| --- | --- | --- |
| `$content` | `string` | HTML (alebo akýkoľvek reťazec). |
| `$parsers` | `array<ParserInterface>` | Parsery, ktoré sa spustia, v poradí priority. Zoznam môže byť prázdny. |

**Vracia** nový `Meta`: pre každý parser v poradí `$meta->merge($parser->parse($content))`. Prázdny zoznam parserov dá prázdny `Meta`. Sama nič nevyhadzuje (len to, čo vyhodí vlastný parser).

### getBody()

**Vracia** `string`: telo odpovede stiahnuté posledným úspešným volaním `parseUrl()`, pred prvým volaním `''`.

## Meta

Meniteľný objekt s výsledkom. Každý setter a metóda `add…` vracia `$this` (`Meta`).

| Metóda | Vracia | Poznámky |
| --- | --- | --- |
| `getTitle()` / `setTitle(string $title)` | `?string` / `Meta` | |
| `getDescription()` / `setDescription(string $description)` | `?string` / `Meta` | |
| `getKeywords()` / `setKeywords(string $keywords)` | `?string` / `Meta` | celý `content`, nerozdelený |
| `getAuthors()` | `list<Author>` | |
| `addAuthor(Author $author)` | `Meta` | pridá na koniec |
| `addAuthorByName(string $name)` | `Meta` | `addAuthor(new Author(null, $name))` |
| `getSections()` | `list<Section>` | |
| `addSection(Section $section)` | `Meta` | pridá na koniec |
| `addSectionByName(string $name)` | `Meta` | `addSection(new Section(null, $name))` |
| `getTags()` | `list<Tag>` | |
| `addTag(Tag $tag)` | `Meta` | pridá na koniec |
| `getOgType()` / `setOgType(string $ogType)` | `?string` / `Meta` | |
| `getOgTitle()` / `setOgTitle(string $ogTitle)` | `?string` / `Meta` | |
| `getOgDescription()` / `setOgDescription(string $ogDescription)` | `?string` / `Meta` | |
| `getOgUrl()` / `setOgUrl(string $ogUrl)` | `?string` / `Meta` | |
| `getOgSiteName()` / `setOgSiteName(string $ogSiteName)` | `?string` / `Meta` | |
| `getOgImage()` / `setOgImage(string $ogImage)` | `?string` / `Meta` | |
| `getPublishedTime()` / `setPublishedTime(DateTime\|string $publishedTime)` | `?DateTime` / `Meta` | reťazec sa cez `strtotime()` prevedie na `DateTime` v UTC; reťazec, ktorý sa nedá prečítať, sa ignoruje |
| `getModifiedTime()` / `setModifiedTime(DateTime\|string $modifiedTime)` | `?DateTime` / `Meta` | ako vyššie |
| `merge(Meta $meta)` | `Meta` (`$this`) | skopíruje každú hodnotu z `$meta` do polí `$this`, ktoré sú prázdne (`null`, `''`, `'0'`, `[]`); zoznamy sa skopírujú celé |

Konštruktor nemá argumenty; každé pole začína ako `null` alebo `[]`.

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

`id()` nastavuje `SchemaParser` pri autoroch, sekciách z `articleTerms` a tagoch z `about`, ktoré majú `@id`; pri hodnotách z meta tagov a pri JSON-LD hodnotách zadaných ako obyčajný reťazec je `null`.

## ParserInterface

<!-- snippet: skip -->
```php
namespace Tomaj\Scraper\Parser;

interface ParserInterface
{
    public function parse(string $content): Meta;
}
```

Kontrakt: pri každom volaní vráťte nový `Meta`, pri poškodenom vstupe nevyhadzujte výnimku a medzi volaniami si neuchovávajte stav.

## OgParser

`Tomaj\Scraper\Parser\OgParser implements ParserInterface`. Konštruktor bez argumentov. `parse()` číta regulárnymi výrazmi `<title>`, meta tagy `description`, `keywords` a `author` a vlastnosti Open Graph / `article:`; z každého tagu prvý výskyt, `null` pre tag, ktorý má `content` pred `name`/`property`. Pozrite [OgParser](../guide/open-graph.md#ogparser).

## OgDomParser

`Tomaj\Scraper\Parser\OgDomParser implements ParserInterface`.

- `__construct()`: vyhodí `Exception\MissingExtensionException`, keď nie je načítané `ext-dom` alebo `ext-libxml`.
- `parse(string $content): Meta`: tie isté tagy ako `OgParser`, cez `DOMDocument`; atribúty v ľubovoľnom poradí, hodnoty atribútov bez ohľadu na veľkosť písmen, všetky tagy `author` a `article:section`, z ostatných tagov posledný výskyt; deklarované kódovanie sa dekóduje, inak sa predpokladá UTF-8. Pozrite [OgDomParser](../guide/open-graph.md#ogdomparser).

## SchemaParser

`Tomaj\Scraper\Parser\SchemaParser implements ParserInterface`. Konštruktor bez argumentov. `parse()` číta schema.org JSON-LD: blok s `id="schema"`, inak prvý uzol článku, inak prvý uzol webovej stránky, s vyriešenými `@graph` a odkazmi `@id`. Výber uzla a mapovanie polí opisuje [schema.org JSON-LD](../guide/json-ld.md).

## ScraperException

`interface Tomaj\Scraper\Exception\ScraperException extends \Throwable`, implementuje ho každá výnimka balíka.

## FetchException

`final class Tomaj\Scraper\Exception\FetchException extends \RuntimeException implements ScraperException`

| Člen | Opis |
| --- | --- |
| `public readonly string $url` | URL odovzdaná do `parseUrl()`. |
| `getPrevious()` | `GuzzleHttp\Exception\GuzzleException`, ktorá ju spôsobila. |
| `getMessage()` | `Cannot fetch "<url>": <Guzzle message>` |
| `__construct(string $url, \Throwable $previous)` | Kód `0`. |

## MissingExtensionException

`final class Tomaj\Scraper\Exception\MissingExtensionException extends \LogicException implements ScraperException`

| Člen | Opis |
| --- | --- |
| `__construct(string $parser, string $extension)` | Správa: `<parser class> requires the PHP extension "<extension>"`. |

---
description: The three parsers of meta-scraper - OgParser, OgDomParser and SchemaParser - what each reads, what it needs, and which order to use.
---

# Choosing a parser

A parser implements `Tomaj\Scraper\Parser\ParserInterface`: it gets the HTML as a string and returns a new `Meta`. The package has three:

| | `OgParser` | `OgDomParser` | `SchemaParser` |
| --- | --- | --- | --- |
| Reads | `<title>`, meta tags, Open Graph | `<title>`, meta tags, Open Graph | schema.org JSON-LD |
| How | regular expressions | HTML5 parser of PHP 8.4 (`Dom\HTMLDocument`) | regular expression for the `<script>` blocks, `json_decode()` |
| Needs | nothing | `ext-dom`, `ext-libxml` | `ext-json` (always on) |
| Attribute order | `name`/`property` must come before `content` | any | any |
| Repeated tags (`article:section`) | first one only | all of them | lists in JSON |
| Character set | bytes as they are | declared charset decoded, otherwise UTF-8 | bytes as they are |
| Authors, sections, tags with `@id` | no | no | yes |
| Page | [Meta tags and Open Graph](./open-graph.md) | [Meta tags and Open Graph](./open-graph.md) | [schema.org JSON-LD](./json-ld.md) |

## Which ones, in which order

The order decides which value wins when two parsers find the same field ([Combining parsers](./combining.md)).

| Use case | Parsers |
| --- | --- |
| News and blog articles: clean headlines, structured authors with `@id`, dates | `[new SchemaParser(), new OgDomParser()]` |
| Link previews: what the page shows when shared on social networks | `[new OgDomParser(), new SchemaParser()]` |
| No `ext-dom`, or speed over accuracy on well-formed pages | `[new SchemaParser(), new OgParser()]` |
| Pages with only Twitter cards or other markup | add [your own parser](./custom-parser.md) at the end |

`OgDomParser` is the better choice for meta tags whenever `ext-dom` is available: `OgParser` relies on the attribute order and finds nothing in tags that write `content` before `property` (see the [broken pages example](../examples/broken-pages.md)). `OgParser` stays for environments without `ext-dom` and for compatibility.

## Creating parsers

All three have a constructor without arguments and keep no state between calls, so create them once and reuse them:

```php
$parsers = [new SchemaParser(), new OgDomParser()];

foreach ([$html, $html] as $page) {
    echo new Scraper()->parse($page, $parsers)->getTitle(), "\n";
}
```

<!-- snippet: output -->
```text
Ten tips for PHP 8.4
Ten tips for PHP 8.4
```

`new OgDomParser()` throws `MissingExtensionException` when `ext-dom` or `ext-libxml` is not loaded, so a missing extension shows up when the parser is created, not later as empty results.

A parser can also be used without the `Scraper`:

```php
$meta = new SchemaParser()->parse($html);
echo $meta->getAuthors()[0]->id(), "\n";
```

<!-- snippet: output -->
```text
https://blog.example/#jana
```

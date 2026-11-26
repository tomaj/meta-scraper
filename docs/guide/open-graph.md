---
description: OgParser and OgDomParser read the title, description, keywords, author and Open Graph tags of a page. Every tag they read, and how the two differ.
---

# Meta tags and Open Graph

`OgParser` and `OgDomParser` read the same tags into the same `Meta` fields. `OgParser` uses regular expressions and needs no extension; `OgDomParser` parses the document with PHP's DOM extension and is more accurate.

## Tags that are read

| Tag | `Meta` field |
| --- | --- |
| `<title>…</title>` | `setTitle()` / `getTitle()` |
| `<meta name="description" content="…">` | `getDescription()` |
| `<meta name="keywords" content="…">` | `getKeywords()` (the whole string, not split) |
| `<meta name="author" content="…">` | `getAuthors()`: an `Author` with `id()` `null` |
| `<meta property="og:title" content="…">` | `getOgTitle()` |
| `<meta property="og:description" content="…">` | `getOgDescription()` |
| `<meta property="og:type" content="…">` | `getOgType()` |
| `<meta property="og:url" content="…">` | `getOgUrl()` |
| `<meta property="og:site_name" content="…">` | `getOgSiteName()` |
| `<meta property="og:image" content="…">` | `getOgImage()` |
| `<meta property="article:section" content="…">` | `getSections()`: a `Section` with `id()` `null` |
| `<meta property="article:published_time" content="…">` | `getPublishedTime()`, parsed with `strtotime()` |
| `<meta property="article:modified_time" content="…">` | `getModifiedTime()`, parsed with `strtotime()` |

The `name` tags must use `name`, the Open Graph tags must use `property` (that is what the Open Graph protocol specifies): `<meta name="og:title">` is not read. Tags that are not in the table, including `twitter:*`, `og:image:width`, `og:locale` and `article:tag`, are ignored; read them with [your own parser](./custom-parser.md).

```php
$meta = new OgDomParser()->parse($html);

echo $meta->getOgType(), ' | ', $meta->getOgSiteName(), ' | ', $meta->getOgUrl(), "\n";
echo $meta->getSections()[0]->name(), ' | ', $meta->getPublishedTime()?->format(DATE_ATOM), "\n";
```

<!-- snippet: output -->
```text
article | Example Blog | https://blog.example/php-84-tips
PHP | 2024-11-21T08:00:00+00:00
```

## OgDomParser

```php
$parser = new OgDomParser(); // throws MissingExtensionException without ext-dom or ext-libxml
```

- **Any markup.** The document is parsed by libxml's HTML parser, so attribute order, quoting style, upper-case tags and missing closing tags do not matter. libxml warnings about bad markup are suppressed and cleared; your `libxml_use_internal_errors()` setting is restored afterwards.
- **Title:** the text of the first `<title>` element.
- **Attribute values are case-insensitive:** `property="OG:Title"` is `og:title`.
- **Repeated tags:** every `author` and `article:section` tag is added to the list; for the other fields the **last** tag on the page wins.
- **Entities** are decoded by the HTML parser (`&hellip;` becomes `…`) and then once more with `htmlspecialchars_decode()`, so a double-escaped `&amp;lt;` becomes `<`.
- **Character set:** a document that declares its charset (`<meta charset="windows-1250">` or `<meta http-equiv="Content-Type" content="text/html; charset=…">`, or an XML declaration with `encoding`) is decoded from it. A document without a declaration is read as UTF-8. The `Content-Type` header of the response is not used: `parse()` only sees the HTML.
- **Empty input:** `parse('')` returns an empty `Meta`.

## OgParser

```php
$parser = new OgParser(); // no requirements
```

- **Regular expressions:** one expression per tag, case-insensitive, over the whole string. It works on any string, including fragments such as only the `<head>`.
- **Attribute order matters:** the `name` or `property` attribute must come before `content` in the same tag. Single and double quotes both work (a value may contain the other kind of quote and `>`); unquoted values do not.
- **One tag at a time:** every expression stays inside one `<meta>` tag. A tag that cannot be read, such as `<meta content="…" property="og:title">` (content first) or a tag without `content`, gives `null`, never the value of another tag. An empty `content=""` gives an empty string.
- **First match only:** for every tag, including `author` and `article:section`, only the first occurrence is read.
- **Entities:** every named and numeric HTML5 entity is decoded once with `html_entity_decode()` (`&eacute;` becomes `é`, `&hellip;` `…`, `&#8211;` `–`, `&nbsp;` a no-break space U+00A0), in the title and in every `content`. A double-escaped `&amp;amp;` becomes the literal text `&amp;`. Unknown entities and entities without the closing `;` stay as they are.
- **Character set:** the bytes are returned as they are; convert pages that are not UTF-8 before parsing ([Fetching pages](./fetching.md#character-sets)).

::: tip Content first
`OgParser` does not read tags written as `<meta content="…" property="og:title">`; `OgDomParser` does. The [broken pages example](../examples/broken-pages.md) compares the two. Use `OgDomParser` for pages you do not control.
:::

## The two side by side

The [Open Graph article example](../examples/open-graph-article.md) parses one news article with both parsers. The output differs only in the sections: the page has two `article:section` tags and `OgParser` reads the first one.

```php
$html = '<title>A &amp; B &hellip;</title>'
    . '<meta property="og:title" content="First"><meta property="og:title" content="Second">'
    . '<meta name="author" content="Ann"><meta name="author" content="Bob">';

foreach ([new OgParser(), new OgDomParser()] as $parser) {
    $meta = $parser->parse($html);
    $authors = implode(', ', array_map(static fn(Author $a): ?string => $a->name(), $meta->getAuthors()));
    echo new ReflectionClass($parser)->getShortName(), ' | ', $meta->getTitle(), ' | ', $meta->getOgTitle(), ' | ', $authors, "\n";
}
```

<!-- snippet: output -->
```text
OgParser | A & B … | First | Ann
OgDomParser | A & B … | Second | Ann, Bob
```

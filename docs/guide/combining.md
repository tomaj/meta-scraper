---
description: How Scraper::parse() merges the results of several parsers with Meta::merge() - the first value wins - and which order to choose.
---

# Combining parsers

`Scraper::parse($html, $parsers)` runs the parsers in the order of the array and merges their results into one `Meta` with `Meta::merge()`.

<Diagram name="merge" alt="SchemaParser runs first and sets the title and the authors; OgDomParser runs second: its title and authors are ignored because those fields are already set, its image and site name fill fields that were still empty" />

## The rules

1. **The first parser that finds a value wins.** A later parser fills only fields that are still empty.
2. A text field is empty when it is `null`, `''` or `'0'`. A parser that found `''` (for example `<meta name="description" content="">`) does not block a later parser.
3. **Lists are taken whole.** Authors, sections and tags come from the first parser whose list is not empty, and are never mixed: JSON-LD authors with `@id` are not extended with a duplicate name from `<meta name="author">`.
4. The published and modified times are taken from the first parser that parsed a date.
5. Each field is decided on its own: the title may come from one parser and the image from another.

## The order changes the result

<<< @/snippets/combine-parsers.php

Output:

<<< @/snippets/combine-parsers.php.out{text}

Typical orders:

| Order | Gives |
| --- | --- |
| `[new SchemaParser(), new OgDomParser()]` | The editorial headline (no `" \| Site"` suffix), authors with `@id`, exact dates; Open Graph fills the image, site name and the `og:*` fields. Good for archiving and indexing articles. |
| `[new OgDomParser(), new SchemaParser()]` | What the page shows when shared: the `<title>`, the `og:image` chosen for social networks. JSON-LD only fills the gaps (authors with `@id` when there is no `author` meta tag). Good for link previews. |
| `[…, new YourParser()]` | Your parser as the last fallback, for example for [Twitter cards](../examples/twitter-card.md). |

The [JSON-LD @graph example](../examples/json-ld-graph.md) runs both orders on one page and prints which value won.

## `getTitle()` versus `getOgTitle()`

They are different fields. `getTitle()` is the `<title>` (or the JSON-LD `headline`), `getOgTitle()` is `og:title`. A link preview usually wants `og:title` first:

```php
$meta = $scraper->parse($html, [new OgDomParser()]);
$headline = $meta->getOgTitle() ?? $meta->getTitle();
echo $headline, "\n";
```

<!-- snippet: output -->
```text
Ten tips for PHP 8.4
```

## Merging yourself

`Meta::merge()` is public, so you can combine results that you produced separately, for example cached JSON-LD with freshly downloaded Open Graph. It changes and returns the object it is called on:

```php
$fromCache = new Meta()->setTitle('Cached title');
$fresh = new OgDomParser()->parse($html);

$meta = $fromCache->merge($fresh);

echo $meta->getTitle(), "\n";      // kept: it was already set
echo $meta->getOgImage(), "\n";    // filled from $fresh
```

<!-- snippet: output -->
```text
Cached title
https://blog.example/php84.jpg
```

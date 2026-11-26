---
description: The Meta result object of meta-scraper - every field with its type and source, Author, Section and Tag, dates, setters and merge().
---

# The Meta object

`Tomaj\Scraper\Meta` is what every parser and `Scraper::parse()` / `parseUrl()` return. It is a plain mutable object: one nullable field per value and three lists.

## Fields

| Field | Getter | Type | Setter / adder | Read by |
| --- | --- | --- | --- | --- |
| Title | `getTitle()` | `?string` | `setTitle(string)` | Og*, Schema (`headline`) |
| Description | `getDescription()` | `?string` | `setDescription(string)` | Og*, Schema |
| Keywords | `getKeywords()` | `?string` | `setKeywords(string)` | Og* |
| Authors | `getAuthors()` | `list<Author>` | `addAuthor(Author)`, `addAuthorByName(string)` | Og*, Schema |
| Sections | `getSections()` | `list<Section>` | `addSection(Section)`, `addSectionByName(string)` | Og*, Schema |
| Tags | `getTags()` | `list<Tag>` | `addTag(Tag)` | Schema (`about`) |
| Open Graph type | `getOgType()` | `?string` | `setOgType(string)` | Og* |
| Open Graph title | `getOgTitle()` | `?string` | `setOgTitle(string)` | Og* |
| Open Graph description | `getOgDescription()` | `?string` | `setOgDescription(string)` | Og* |
| Canonical URL | `getOgUrl()` | `?string` | `setOgUrl(string)` | Og*, Schema (`url`) |
| Site name | `getOgSiteName()` | `?string` | `setOgSiteName(string)` | Og* |
| Image URL | `getOgImage()` | `?string` | `setOgImage(string)` | Og*, Schema (`image.url`) |
| Published | `getPublishedTime()` | `?DateTimeImmutable` | `setPublishedTime(DateTimeInterface\|string)` | Og*, Schema |
| Modified | `getModifiedTime()` | `?DateTimeImmutable` | `setModifiedTime(DateTimeInterface\|string)` | Og*, Schema |

"Og\*" is `OgParser` and `OgDomParser`; the exact tags are on [Meta tags and Open Graph](./open-graph.md) and [schema.org JSON-LD](./json-ld.md).

- A field nobody set is `null`; a list nobody added to is `[]`.
- Values are strings exactly as the page wrote them, after entity decoding: not trimmed, not validated, URLs not resolved. Treat them as untrusted input ([Security](./security.md#untrusted-values)).
- Setters and adders return the same `Meta` (`$this`), so calls can be chained. A setter overwrites the value; an adder appends to the list.

## Author, Section and Tag

Three small readonly value objects with the same shape:

```php
$author = new Author('https://blog.example/#jana', 'Jana Nováková');

echo $author->id(), "\n";    // the schema.org @id, or null
echo $author->name(), "\n";  // the name, or null
```

<!-- snippet: output -->
```text
https://blog.example/#jana
Jana Nováková
```

Both values are nullable: a JSON-LD author may have only an `@id`, or a value of an unexpected type gives `null`. `addAuthorByName('Jana')` is a shortcut for `addAuthor(new Author(null, 'Jana'))`, the same for `addSectionByName()`. There is no `addTagByName()`.

## Dates

`setPublishedTime()` and `setModifiedTime()` accept any `DateTimeInterface` (a copy is stored, so changing your object later does not change the `Meta`) or a string. The getters return a `DateTimeImmutable`.

- A string is parsed with `new DateTimeImmutable()` and keeps the **offset the page wrote**: `2026-11-20T06:30:00+01:00` stays `+01:00`, `…Z` is `+00:00`. Convert it with `setTimezone()` for display.
- Only **absolute** dates are accepted: ISO 8601 / RFC 3339 (with or without seconds, fractions and offset), RFC 2822 (`Fri, 20 Nov 2026 06:30:00 +0100`, the weekday must match the date), `2026-11-20 06:30`, a date alone (`2026-11-20`, midnight). A string without an offset or time zone is in PHP's default time zone (`date_default_timezone_get()`).
- Anything else is ignored and the field keeps its previous value: text, relative expressions that depend on the current time (`yesterday noon`, `+1 week`, `now`, `2026-11-20 +1 day`), a time without a date, a year alone, a Unix timestamp (`@1763616600`), invalid dates (`2026-02-30`) and a weekday that does not match the date.

```php
$meta = new Meta()
    ->setPublishedTime('2026-11-20T06:30:00+01:00')
    ->setModifiedTime('yesterday noon');

echo $meta->getPublishedTime()?->format(DATE_ATOM), "\n";
var_dump($meta->getModifiedTime());

// show it in your time zone: setTimezone() returns a new object, $meta is not changed
echo $meta->getPublishedTime()?->setTimezone(new DateTimeZone('UTC'))->format('j. n. Y H:i e'), "\n";
```

<!-- snippet: output -->
```text
2026-11-20T06:30:00+01:00
NULL
20. 11. 2026 05:30 UTC
```

## merge()

<!-- snippet: skip -->
```php
public function merge(Meta $meta): Meta;
```

Copies every value of `$meta` into this object where this object has none, and returns `$this`. A text field counts as empty when it is `null`, `''` or `'0'`; a list when it is `[]`; a date when it is `null`. Lists are copied whole. `Scraper::parse()` uses it to combine the parsers; see [Combining parsers](./combining.md).

## Building a Meta yourself

`new Meta()` with the setters is handy in tests of code that consumes metadata, and in [your own parser](./custom-parser.md):

```php
$meta = new Meta()
    ->setTitle('Hello')
    ->setOgImage('https://example.com/a.jpg')
    ->addAuthorByName('Ann')
    ->addTag(new Tag('https://example.com/tag/php', 'PHP'));

echo $meta->getTitle(), ' by ', $meta->getAuthors()[0]->name(), ', tag ', $meta->getTags()[0]->name(), "\n";
```

<!-- snippet: output -->
```text
Hello by Ann, tag PHP
```

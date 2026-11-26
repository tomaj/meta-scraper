---
description: SchemaParser reads schema.org JSON-LD - which script blocks and which node it uses, @graph and @id references, and every field it maps into Meta.
---

# schema.org JSON-LD

`SchemaParser` reads [JSON-LD](https://json-ld.org/) structured data, the `<script type="application/ld+json">` blocks that news sites, blogs and CMS plugins (Yoast, Rank Math, …) add for search engines. It needs no extension.

```php
$parser = new SchemaParser(); // no requirements
```

## Which blocks are read

- Every `<script>` element whose `type` attribute is `application/ld+json`, whatever the attribute order, quoting or letter case, with the JSON on one line or many.
- A block that is not valid JSON, or is valid JSON but not an object or a list (`"text"`, `[]`), is skipped; the other blocks are still read.
- A block may contain one object, a list of objects, or an object with a `@graph` list. Graphs are flattened, nested graphs too, so every node of every block takes part.

## Which node is used

One node of the page describes the page; the others describe the publisher, the website, the breadcrumbs or an image. `SchemaParser` picks the node in this order:

1. **A block marked `id="schema"`**, if the page has one: the whole decoded block is used as the node. This is the markup the parser supported before 5.0.
2. **The first article node**, in document order: an `@type` of `Article`, `AdvertiserContentArticle`, `AnalysisNewsArticle`, `AskPublicNewsArticle`, `BackgroundNewsArticle`, `BlogPosting`, `DiscussionForumPosting`, `LiveBlogPosting`, `NewsArticle`, `OpinionNewsArticle`, `Report`, `ReportageNewsArticle`, `ReviewNewsArticle`, `SatiricalArticle`, `ScholarlyArticle`, `SocialMediaPosting` or `TechArticle`.
3. **The first web page node**: `WebPage`, `AboutPage`, `CheckoutPage`, `CollectionPage`, `ContactPage`, `FAQPage`, `ItemPage`, `MedicalWebPage`, `ProfilePage`, `QAPage`, `RealEstateListing` or `SearchResultsPage`.

If none matches, the result is an empty `Meta`. `@type` may be a string or a list (`["NewsArticle", "Article"]`); the type names are compared exactly, without the `https://schema.org/` prefix. Nodes of other types (`Organization`, `WebSite`, `BreadcrumbList`, `ImageObject`, `Person`, `Product`, `Event`, …) are never chosen, because their `url`, `description` or `image` do not describe the page.

## References

Values written as a reference, an object with nothing but an `@id` such as `{"@id": "https://example.com/#author"}`, are replaced with the node of that `@id` from any block on the page. This applies to `author`, `articleTerms`, `about` and `image`. When two nodes have the same `@id`, the first one counts. A reference to a node that does not exist stays as it is (an object with an `@id` and no name).

## Fields

| JSON-LD property of the node | `Meta` | Accepted values |
| --- | --- | --- |
| `headline` | `getTitle()` | string or integer |
| `description` | `getDescription()` | string or integer |
| `author` | `getAuthors()` | an object with `@id`/`name`, a string (the name), or a list of those |
| `articleTerms` | `getSections()` | list of objects with `"@type": "Category"` and `@id`/`name`; other objects are skipped |
| `articleSection` | `getSections()`, only when there is no `articleTerms` list | a string or a list of strings |
| `about` | `getTags()` | an object with `@id`/`name` or a list of them; plain strings are skipped |
| `image` | `getOgImage()` | a URL string, an object with a `url` or, without one, a `contentUrl` (an `ImageObject`, possibly a reference), or a list of those: the first entry with a URL |
| `url` | `getOgUrl()` | string |
| `datePublished` | `getPublishedTime()` | an absolute date string, offset kept ([Dates](./meta.md#dates)) |
| `dateModified` | `getModifiedTime()` | an absolute date string, offset kept |

Details:

- Where a text is expected, strings and integers are used (`"@id": 42` becomes `"42"`); other types (booleans, numbers with a fraction, objects) are ignored. Values are never trimmed or decoded: JSON already has its own escaping.
- The node's `name` is not used for the title, only `headline`. A `WebPage` node usually has a `name` and no `headline`, so its title stays `null` and a later parser can fill it.
- `image` may be a URL string (`"image": "https://…/a.jpg"`), an `ImageObject` or a reference to one, or a list of any of these (`"image": ["https://…/1x1.jpg", "https://…/16x9.jpg"]`, the form Google recommends for articles). From a list, the first entry that gives a non-empty URL string is used; an `ImageObject` gives its `url`, or its `contentUrl` when it has no `url`. Empty strings, numbers, references to missing nodes and objects with neither a string `url` nor a string `contentUrl` are skipped.
- `Author`, `Section` and `Tag` objects get the node's `@id` as `id()`; for authors given as strings and for `articleSection` it is `null`.
- `og:type`, `og:site_name`, `og:title`, `og:description` and the keywords have no JSON-LD source and stay `null`.

## Example

A WordPress-style `@graph` where the article references its author and image by `@id`:

```php
$page = <<<'HTML'
    <script type="application/ld+json">
    {"@context": "https://schema.org", "@graph": [
        {"@type": "WebSite", "@id": "https://news.example/#website", "name": "News", "description": "Not the page"},
        {"@type": "ImageObject", "@id": "https://news.example/a/#image", "url": "https://news.example/a.jpg"},
        {"@type": "NewsArticle", "headline": "The headline", "url": "https://news.example/a/",
         "image": {"@id": "https://news.example/a/#image"},
         "author": [{"@id": "https://news.example/#eva"}, "Guest Writer"],
         "articleSection": ["World", "Europe"], "about": {"@id": "https://news.example/t/eu", "name": "EU"},
         "datePublished": "2026-01-15T10:00:00+01:00"},
        {"@type": "Person", "@id": "https://news.example/#eva", "name": "Eva"}
    ]}
    </script>
    HTML;

$meta = new SchemaParser()->parse($page);

echo $meta->getTitle(), ' | ', $meta->getOgUrl(), ' | ', $meta->getOgImage(), "\n";
foreach ($meta->getAuthors() as $author) {
    echo 'author: ', $author->name(), ' (', $author->id() ?? 'no @id', ")\n";
}
echo 'sections: ', implode(', ', array_map(static fn(Section $s): ?string => $s->name(), $meta->getSections())), "\n";
echo 'tag: ', $meta->getTags()[0]->name(), ' (', $meta->getTags()[0]->id(), ")\n";
echo 'published: ', $meta->getPublishedTime()?->format(DATE_ATOM), "\n";
echo 'description: ', var_export($meta->getDescription(), true), "\n";
```

<!-- snippet: output -->
```text
The headline | https://news.example/a/ | https://news.example/a.jpg
author: Eva (https://news.example/#eva)
author: Guest Writer (no @id)
sections: World, Europe
tag: EU (https://news.example/t/eu)
published: 2026-01-15T10:00:00+01:00
description: NULL
```

The `WebSite` description is not used: the `NewsArticle` has none of its own. The [JSON-LD @graph example](../examples/json-ld-graph.md) does the same with a full Yoast-style page over HTTP and combines it with Open Graph.

## Sections from `articleTerms`

Some publishers describe sections as `Category` nodes with an `@id`. When the node has an `articleTerms` list, it is used instead of `articleSection`:

```php
$page = '<script type="application/ld+json">{"@type": "Article", "articleSection": "ignored",
    "articleTerms": [{"@type": "Category", "@id": "https://news.example/c/sport", "name": "Sport"},
                     {"@type": "Thing", "name": "skipped, not a Category"}]}</script>';

foreach (new SchemaParser()->parse($page)->getSections() as $section) {
    echo $section->name(), ' ', $section->id(), "\n";
}
```

<!-- snippet: output -->
```text
Sport https://news.example/c/sport
```

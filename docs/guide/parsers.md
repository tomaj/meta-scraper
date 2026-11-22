# Parsers

A parser implements `Tomaj\Scraper\Parser\ParserInterface`: it gets the HTML and returns a new `Meta`. The package has three.

| Parser | Reads | Needs |
| --- | --- | --- |
| `OgParser` | meta tags and Open Graph, with regular expressions | nothing |
| `OgDomParser` | the same tags, with the DOM extension | `ext-dom`, `ext-libxml` |
| `SchemaParser` | schema.org JSON-LD | nothing |

## OgParser

Reads `<title>`, `<meta name="description|keywords|author">` and the Open Graph properties `og:title`, `og:description`, `og:type`, `og:url`, `og:site_name`, `og:image`, `article:section`, `article:published_time` and `article:modified_time`. Tag and attribute names are case-insensitive, attribute values may use single or double quotes, and HTML entities are decoded.

It works on any string, even a broken fragment, and returns the bytes as they are: decode non-UTF-8 pages yourself (for example with `mb_convert_encoding()`) before you parse them.

The expressions expect the `name`/`property` attribute before `content`. For tags written the other way round, use `OgDomParser`.

## OgDomParser

Reads the same tags as `OgParser` with PHP's DOM extension, so attribute order does not matter and the first `<title>` element is used. Attribute values are matched case-insensitively (`OG:Title` is `og:title`).

Character set: a document that declares its charset (`<meta charset="windows-1250">` or the `http-equiv` form) is decoded from it; a document without a declaration is read as UTF-8.

Every `parse()` call returns a new `Meta`, so one instance can parse any number of pages. `new OgDomParser()` throws `MissingExtensionException` when `ext-dom` or `ext-libxml` is missing.

## SchemaParser

Reads schema.org [JSON-LD](https://json-ld.org/):

- every `<script type="application/ld+json">` block, whatever the attribute order or quoting, with JSON on one line or many;
- a block may contain one object, a list of objects, or an object with a `@graph` list (nested graphs too);
- references such as `{"@id": "https://example.com/#author"}` are replaced with the object of that `@id` from any block on the page.

Which object is used:

1. A block marked `id="schema"`, if the page has one (the markup the parser supported before 5.0).
2. Otherwise the first article: `Article`, `NewsArticle`, `BlogPosting`, `Report`, `ScholarlyArticle`, `TechArticle`, `LiveBlogPosting`, `SocialMediaPosting`, `DiscussionForumPosting` and the news article subtypes (`AnalysisNewsArticle`, `OpinionNewsArticle`, `ReportageNewsArticle`, `ReviewNewsArticle`, `BackgroundNewsArticle`, `AskPublicNewsArticle`, `AdvertiserContentArticle`, `SatiricalArticle`).
3. Otherwise the first web page: `WebPage`, `AboutPage`, `CheckoutPage`, `CollectionPage`, `ContactPage`, `FAQPage`, `ItemPage`, `MedicalWebPage`, `ProfilePage`, `QAPage`, `RealEstateListing`, `SearchResultsPage`.

Other objects (`Organization`, `WebSite`, `BreadcrumbList`, `ImageObject`, …) are ignored, because their `url`, `description` or `image` do not describe the page. An `@type` may be a list (`["NewsArticle", "WebPage"]`).

Fields of the chosen object:

| JSON-LD | `Meta` |
| --- | --- |
| `headline` | `getTitle()` |
| `description` | `getDescription()` |
| `author`: object(s) with `@id`/`name`, or plain strings | `getAuthors()` |
| `articleTerms`: objects with `"@type": "Category"` | `getSections()` (with `@id`) |
| `articleSection`: string or list, used when there is no `articleTerms` | `getSections()` |
| `about`: object(s) with `@id`/`name` | `getTags()` |
| `image.url` (an `ImageObject`, possibly referenced by `@id`) | `getOgImage()` |
| `url` | `getOgUrl()` |
| `datePublished`, `dateModified` | `getPublishedTime()`, `getModifiedTime()` |

Strings and integers are accepted where a text is expected; values of other types are ignored. Invalid JSON blocks are skipped.

## Combining parsers

`Scraper::parse()` runs the parsers in the given order and merges their results with `Meta::merge()`: a value found by an earlier parser is kept, a later parser only fills fields that are still empty. Empty strings and `"0"` count as empty. Authors, sections and tags are taken as a whole list from the first parser that found any.

<<< @/snippets/combine-parsers.php

Output:

<<< @/snippets/combine-parsers.php.out{text}

Typical orders:

- `[new SchemaParser(), new OgDomParser()]`: clean article headlines and structured authors, Open Graph for the rest.
- `[new OgDomParser(), new SchemaParser()]`: what the page shows when shared on social networks, JSON-LD only for the gaps.

## Writing a parser

Implement `ParserInterface` and return a new `Meta` from every call:

<<< @/snippets/custom-parser.php

Output:

<<< @/snippets/custom-parser.php.out{text}

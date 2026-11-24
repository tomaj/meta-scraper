---
description: Read a WordPress/Yoast-style schema.org JSON-LD @graph with @id references using meta-scraper's SchemaParser, and see how the parser order decides which value wins.
---

# Example: JSON-LD @graph

A news page as WordPress with Yoast SEO renders it: one JSON-LD block with a `@graph` of six nodes (organization, website, image, web page, the article, a person) linked by `@id`, a second block with breadcrumbs, and Open Graph tags whose title and image differ from the JSON-LD ones.

<ProjectSource name="json-ld-graph" :files="['run.php', 'site/news.html', 'e2e.sh', 'expected-output.txt']" />

## The page

<<< @/../examples/json-ld-graph/site/news.html

## The code

<<< @/../examples/json-ld-graph/run.php

## Output

<<< @/../examples/json-ld-graph/expected-output.txt{text}

## What to notice

- **The right node.** `SchemaParser` uses the `NewsArticle` (its `@type` is a list, `["NewsArticle", "Article"]`) and ignores the `Organization`, `WebSite`, `ImageObject`, `WebPage` and `BreadcrumbList` nodes, even though they come first and have their own `url`, `name` and `description`. See [Which node is used](../guide/json-ld.md#which-node-is-used).
- **References resolved.** The first author is only `{"@id": ".../person/peter"}` in the article; the parser finds the `Person` node and returns its name with the `@id`. The second author is inline and has no `@id`. The image is a reference to the `ImageObject` node.
- **No Open Graph from JSON-LD.** `og:title` and the site name stay `null` with `SchemaParser` alone.
- **The order decides.** With `SchemaParser` first, the title is the clean headline and the image is the 1600 px one from JSON-LD. With `OgDomParser` first, you get the `<title>` with the site suffix, the meta description and the social image. The authors are the same either way: the page has no `author` meta tag, so only JSON-LD has them. See [Combining parsers](../guide/combining.md).

## What CI checks

The selected node, both resolved references, the UTC dates, sections and tags with their `@id`, and the merged result of both parser orders.

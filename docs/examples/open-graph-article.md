---
description: Scrape every meta tag and Open Graph property of a news article with meta-scraper - OgDomParser and OgParser compared on the same page.
---

# Example: Open Graph article

A news article with the usual tags: `<title>`, description, keywords, author, Open Graph, two `article:section` tags and the published and modified times. `run.php` downloads it with `parseUrl()` and prints every field, first with `OgDomParser`, then with `OgParser` on the same HTML from `getBody()`.

<ProjectSource name="open-graph-article" :files="['run.php', 'site/article.html', 'e2e.sh', 'expected-output.txt']" />

## The page

<<< @/../examples/open-graph-article/site/article.html

## The code

<<< @/../examples/open-graph-article/run.php

## Output

<<< @/../examples/open-graph-article/expected-output.txt{text}

## What to notice

- **Times are UTC.** `2026-11-20T06:30:00+01:00` becomes `2026-11-20T05:30:00+00:00`: `Meta` stores dates parsed with `strtotime()` as UTC `DateTime` objects. See [Dates](../guide/meta.md#dates).
- **Sections:** the page has two `article:section` tags. `OgDomParser` returns both, `OgParser` only the first. Everything else is the same for this well-formed page.
- **Non-ASCII text** (`Račianske mýto`, `Kováčová`) comes through unchanged: the page is UTF-8 and declares it.
- **`twitter:card` is ignored**: no built-in parser reads Twitter tags. The [Twitter card example](./twitter-card.md) adds a parser for them.
- `getBody()` returns the HTML of the last download, so the second parse needs no second request.

## What CI checks

The output must match `expected-output.txt` exactly: every field read by `OgDomParser` and `OgParser`, the UTC conversion of both dates and the difference in sections.

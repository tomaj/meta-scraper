---
description: What meta-scraper's parsers return for broken and unusual pages - reversed attributes, unquoted tags, invalid JSON-LD, missing or legacy charsets and empty bodies.
---

# Example: broken pages

Real pages are not always well-formed. This example runs the parsers over six fixture pages that break something and prints what each parser returns. None of them throws.

<ProjectSource name="broken-pages" :files="['run.php', 'site/reversed.html', 'site/unquoted.html', 'site/invalid-json-ld.html', 'site/windows-1250.html', 'site/router.php', 'expected-output.txt']" />

## The pages

::: code-group

<<< @/../examples/broken-pages/site/reversed.html [reversed.html]

<<< @/../examples/broken-pages/site/unquoted.html [unquoted.html]

<<< @/../examples/broken-pages/site/invalid-json-ld.html [invalid-json-ld.html]

<<< @/../examples/broken-pages/site/router.php [router.php]

:::

`no-charset.html` is a UTF-8 page without any charset declaration, `windows-1250.html` is encoded in Windows-1250 and declares it (the file is not valid UTF-8, so it is not shown here; `router.php` sends it with the matching `Content-Type`), and `empty.html` is an empty file.

## The code

<<< @/../examples/broken-pages/run.php

## Output

<<< @/../examples/broken-pages/expected-output.txt{text}

## What to notice

- **`reversed.html`: `OgParser` returns wrong values.** With `content` before `property`, its expressions match across tags: `og:title` becomes the URL of the next tag and `og:url` the site name. `OgDomParser` reads all three correctly. This is why `OgDomParser` is the recommended parser for pages you do not control; see [Choosing a parser](../guide/parsers.md).
- **`unquoted.html`:** upper-case tags and unquoted attribute values are fine for `OgDomParser`; `OgParser` needs quotes and finds only the title.
- **`invalid-json-ld.html`:** the block with a trailing comma is skipped and the next block is used. The author given as a plain string is read; `"datePublished": "not a date"` is ignored.
- **`no-charset.html`:** both parsers return correct UTF-8: `OgParser` passes the bytes through, `OgDomParser` reads undeclared documents as UTF-8.
- **`windows-1250.html`:** `OgParser` returns Windows-1250 bytes, which are not valid UTF-8. `OgDomParser` decodes the declared charset. Converting the body with `iconv()` first fixes `OgParser` (and would fix `SchemaParser`); `mb_convert_encoding()` does not support Windows-1250.
- **`empty.html`:** every field is `null`, every list empty.

## What CI checks

Each value above, including the wrong `OgParser` values on `reversed.html`, so a change in the parsers' behaviour on broken input shows up as a failing example.

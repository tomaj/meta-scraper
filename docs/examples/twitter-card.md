---
description: Write a custom meta-scraper parser that reads Twitter (X) card tags as a fallback for Open Graph, and combine it with OgDomParser.
---

# Example: Twitter card parser

The built-in parsers do not read `twitter:*` tags. Some pages only have a Twitter card and no Open Graph. This example adds a parser for the card and uses it as a fallback after `OgDomParser`.

<ProjectSource name="twitter-card" :files="['src/TwitterCardParser.php', 'run.php', 'site/card-only.html', 'site/both.html', 'expected-output.txt']" />

## The parser

<<< @/../examples/twitter-card/src/TwitterCardParser.php

It follows the [rules for parsers](../guide/custom-parser.md#rules): a new `Meta` per call, nothing thrown on bad markup, only found values set. It maps the card to the Open Graph fields, the closest ones `Meta` has.

## The pages

::: code-group

<<< @/../examples/twitter-card/site/card-only.html [card-only.html]

<<< @/../examples/twitter-card/site/both.html [both.html]

:::

## The code

<<< @/../examples/twitter-card/run.php

## Output

<<< @/../examples/twitter-card/expected-output.txt{text}

## What to notice

- With `OgDomParser` alone, the card-only page has a `<title>` and nothing else.
- With the card parser after it, the Open Graph fields are filled from the card. `&amp;` in the description is decoded by the DOM parser.
- On the page with both, Open Graph wins for the title and the image, because `OgDomParser` runs first; the card fills only the description, which the page has only as `twitter:description`.

## What CI checks

The three results above: nothing from the card without the parser, the card as a fallback, and Open Graph taking precedence.

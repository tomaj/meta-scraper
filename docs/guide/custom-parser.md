---
description: Write your own meta-scraper parser by implementing ParserInterface - rules, a full example reading Twitter cards, and how to combine it with the built-in parsers.
---

# Writing a parser

The built-in parsers read meta tags, Open Graph and JSON-LD. For anything else (Twitter cards, microdata, a site's own markup, data in the page body) implement `ParserInterface`:

<!-- snippet: skip -->
```php
namespace Tomaj\Scraper\Parser;

use Tomaj\Scraper\Meta;

interface ParserInterface
{
    public function parse(string $content): Meta;
}
```

## Rules

- **Return a new `Meta` from every call.** One parser instance is used for many pages; keeping a `Meta` in a property leaks values from one page into the next.
- **Do not throw on bad input.** Return what you could read. Throw only for programming errors, such as a missing extension (do that in the constructor, like `OgDomParser`).
- **Set only what you found.** An unset field stays `null` and a later parser can fill it.
- **Do not depend on other parsers.** Each parser gets the original HTML; the scraper merges the results.
- The built-in classes are `final`: wrap them instead of extending them.

`Meta` has no fields of its own for other markup. Map what you read to the closest field (a Twitter card title to `setOgTitle()`), or keep your own result next to the `Meta`.

## A short one

<<< @/snippets/custom-parser.php

Output:

<<< @/snippets/custom-parser.php.out{text}

## A complete one: Twitter cards

The [Twitter card example](../examples/twitter-card.md) has a parser built on PHP 8.4's HTML5 parser (`Dom\HTMLDocument`, like `OgDomParser`) that reads `twitter:title`, `twitter:description` and `twitter:image` (also written as `property="twitter:…"`):

<<< @/../examples/twitter-card/src/TwitterCardParser.php

Put it after the built-in parsers so it only fills what they did not find:

<!-- snippet: skip -->
```php
$parsers = [new OgDomParser(), new TwitterCardParser()];
```

## Wrapping a built-in parser

To post-process the result of a built-in parser, compose it:

```php
final class AbsoluteImageParser implements ParserInterface
{
    public function __construct(
        private readonly string $pageUrl,
        private readonly ParserInterface $inner = new OgDomParser(),
    ) {}

    #[\Override]
    public function parse(string $content): Meta
    {
        $meta = $this->inner->parse($content);
        $image = $meta->getOgImage();
        if ($image !== null && str_starts_with($image, '/') && !str_starts_with($image, '//')) {
            $parts = parse_url($this->pageUrl);
            $meta->setOgImage($parts['scheme'] . '://' . $parts['host'] . $image);
        }

        return $meta;
    }
}

$meta = new AbsoluteImageParser('https://shop.example/p/1')->parse('<meta property="og:image" content="/img/1.jpg">');
echo $meta->getOgImage(), "\n";
```

<!-- snippet: output -->
```text
https://shop.example/img/1.jpg
```

## Testing a parser

A parser is a pure function of a string, so test it with HTML fixtures and no network:

```php
final class TwitterTitleParser implements ParserInterface
{
    #[\Override]
    public function parse(string $content): Meta
    {
        $meta = new Meta();
        if (preg_match('/<meta\s+name="twitter:title"\s+content="([^"]*)"/i', $content, $m) === 1) {
            $meta->setOgTitle(html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5));
        }

        return $meta;
    }
}

$parser = new TwitterTitleParser();
var_dump($parser->parse('<meta name="twitter:title" content="Tom &amp; Jerry">')->getOgTitle() === 'Tom & Jerry');
var_dump($parser->parse('')->getOgTitle() === null);
var_dump($parser->parse('<meta name="twitter:title" content="A">') !== $parser->parse('<meta name="twitter:title" content="A">'));
```

<!-- snippet: output -->
```text
bool(true)
bool(true)
bool(true)
```

Test it together with the built-in parsers too, through `Scraper::parse()`, to see the merged result your application gets. See [Testing your code](./testing.md).

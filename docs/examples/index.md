---
description: Seven runnable meta-scraper examples - Open Graph, JSON-LD @graph, a Twitter card parser, broken pages, fetch errors, an SSRF-safe link preview and testing - each tested in CI.
---

# Examples

Every example is a small Composer project in [`examples/`](https://github.com/tomaj/meta-scraper/tree/master/examples) of the repository. It scrapes fixture pages served by PHP's built-in web server (`php -S`) on `127.0.0.1`, so it needs no internet and gives the same output every time. The pages on this site include the code straight from those files.

| Example | What it shows |
| --- | --- |
| [Open Graph article](./open-graph-article.md) | Every meta tag and Open Graph field of a news article, `OgDomParser` and `OgParser` side by side |
| [JSON-LD @graph](./json-ld-graph.md) | A Yoast-style `@graph` with `@id` references; how the parser order decides which value wins |
| [Twitter card parser](./twitter-card.md) | A custom `ParserInterface` that reads `twitter:*` tags as a fallback |
| [Broken pages](./broken-pages.md) | Reversed attributes, unquoted tags, invalid JSON-LD, UTF-8 without a declaration, Windows-1250, an empty page |
| [Fetch errors](./fetch-errors.md) | 404, 500, timeout, redirect loop, refused connection, `http_errors` and `allow_redirects` |
| [Link preview (SSRF-safe)](./link-preview.md) | Preview cards for user-submitted URLs: URL guard, pinned DNS, checked redirects, escaped output |
| [Testing with a mock](./testing.md) | Unit-testing code that calls `parseUrl()` with a Guzzle `MockHandler` |

## How they are tested

Each example has:

- `composer.json` requiring `tomaj/meta-scraper`,
- `run.php`, the example itself, which prints what it found,
- `site/` with the fixture pages (and a `router.php` where a page needs a special status, header or delay),
- `e2e.sh`, which starts `php -S` for `site/`, runs `run.php` and compares its output with
- `expected-output.txt`.

The [Examples workflow](https://github.com/tomaj/meta-scraper/actions/workflows/examples.yml) runs every `e2e.sh` on every push and pull request. [`examples/ci/use-local-meta-scraper.sh`](https://github.com/tomaj/meta-scraper/blob/master/examples/ci/use-local-meta-scraper.sh) installs the package from the checkout through a Composer path repository, so the examples always run against the code of the pull request, not a released version. When a change to the library changes an example's output, the workflow fails and shows the difference.

## Run one yourself

```bash
git clone https://github.com/tomaj/meta-scraper.git
cd meta-scraper
examples/ci/recipe.sh link-preview
```

`recipe.sh` copies the example to a temporary directory, installs it against your checkout and runs its `e2e.sh`. It needs PHP 8.4+, Composer and free ports 8401 to 8406.

# Contributing

Contributions are welcome. The full rules are in [CONTRIBUTING.md](https://github.com/tomaj/meta-scraper/blob/master/CONTRIBUTING.md); this project follows the [Code of Conduct](https://github.com/tomaj/meta-scraper/blob/master/CODE_OF_CONDUCT.md).

## Reporting bugs

Open an [issue](https://github.com/tomaj/meta-scraper/issues/new/choose) with the smallest HTML that shows the problem, the parser you use and the expected and actual values.

Security vulnerabilities are reported in private: see the [security policy](https://github.com/tomaj/meta-scraper/blob/master/SECURITY.md).

## Development

meta-scraper supports PHP 8.4, 8.5 and 8.6.

```bash
git clone https://github.com/tomaj/meta-scraper.git
cd meta-scraper
composer install
composer check      # code style, PHPStan, Rector, tests
composer infection  # mutation testing (needs Xdebug or PCOV)
```

Every behaviour change needs a test and a `CHANGELOG.md` entry. The tests never use the network.

## Documentation

This site is built with [VitePress](https://vitepress.dev/) from the [`docs/`](https://github.com/tomaj/meta-scraper/tree/master/docs) folder. English pages are at the root of `docs/`, Slovak ones in `docs/sk/` with the same paths; a CI check (`npm run docs:locales`) fails when a page has no twin in the other language.

```bash
cd docs
npm ci
npm run docs:dev    # local preview
npm run docs:links  # build, check the SVGs, the illustrations and every link
```

Code on the pages comes from three places, and all of it is tested:

| Where | How it gets onto a page | Tested by |
| --- | --- | --- |
| `examples/<name>/` | `<<< @/../examples/<name>/run.php` | the Examples workflow: `examples/ci/recipe.sh <name>` runs the example against the checkout and compares its output with `expected-output.txt` |
| `docs/snippets/*.php` | `<<< @/snippets/<file>.php` | `DocsSnippetsTest`: snippets with a `.out` file must print exactly that; PHPStan analyses all of them |
| PHP code blocks in `README.md`, `UPGRADE-5.0.md` and `docs/**/*.md` | inline | `DocsSnippetsTest`: every block runs (with `$html`, `$url`, `$scraper` and `$parsers` predefined) unless it follows `<!-- snippet: skip -->`; a `text` code block after `<!-- snippet: output -->` is its expected output |

When you change an example's output on purpose, update its `expected-output.txt` in the same commit.

### Illustrations and brand assets

- The hero illustration and the diagrams in `docs/public/illustrations/` and `docs/public/diagrams/` are generated in a light and a dark variant, in English and in Slovak, by `python3 docs/scripts/illustrations.py` from the templates in that script; `npm run docs:links` fails when a generated file is out of date.
- The logo (`logo.svg`, `logo-dark.svg`) and `favicon.svg` are drawn by hand. `npm run brand` renders `og.png`, the PNG icons and `favicon.ico` from them with headless Chrome (`CHROME_PATH` if Chrome is not in a standard location); commit the results.
- `scripts/check-svg.py` checks that every SVG is well-formed.

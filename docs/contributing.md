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

This site is built with [VitePress](https://vitepress.dev/) from the [`docs/`](https://github.com/tomaj/meta-scraper/tree/master/docs) folder. PHP code on the pages is imported from `docs/snippets/`; the test suite runs every snippet that does not download a page and compares its output with the `.out` file next to it, and PHPStan analyses all of them.

```bash
cd docs
npm ci
npm run docs:dev    # local preview
npm run docs:links  # build and check links
```

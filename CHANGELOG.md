# Change Log
All notable changes to this project will be documented in this file.
Updates should follow the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

## [Unreleased][unreleased]

### Added
- `LICENSE.md` (MIT), `SECURITY.md` with the supported versions and a note on fetching untrusted URLs, `CONTRIBUTING.md`, `CODE_OF_CONDUCT.md` and `RELEASING.md` ([#13](https://github.com/tomaj/meta-scraper/issues/13)).

### Changed
- **BREAKING CHANGE**: PHP 8.4 or newer is required (was PHP 7.1) ([#10](https://github.com/tomaj/meta-scraper/pull/10), [#16](https://github.com/tomaj/meta-scraper/issues/16)).
- **BREAKING CHANGE**: `guzzlehttp/guzzle` 6 is no longer supported, `^7.9` is required ([#10](https://github.com/tomaj/meta-scraper/pull/10)).
- **BREAKING CHANGE**: properties are typed and `Meta::setPublishedTime()` / `Meta::setModifiedTime()` accept `DateTime|string` (`setModifiedTime()` now declares the `Meta` return type). Subclasses overriding these members must be updated ([#10](https://github.com/tomaj/meta-scraper/pull/10)).
- `SchemaParser` ignores schema values of unexpected types instead of failing (a TypeError before), accepts a single `author` object (it used to produce one empty author per key), authors given as plain strings, and numeric `@id` values everywhere; an empty `about` list no longer adds an empty tag ([#10](https://github.com/tomaj/meta-scraper/pull/10)).
- A published/modified time of exactly `1970-01-01T00:00:00Z` is no longer ignored ([#10](https://github.com/tomaj/meta-scraper/pull/10)).
- Parsers decode `&#039;` / `&#x27;` to `'`: since PHP 8.1 `htmlspecialchars_decode()` decodes single quotes by default, so this differs from running 1.x on PHP 7 ([#10](https://github.com/tomaj/meta-scraper/pull/10)).
- CI moved from Travis CI to GitHub Actions (PHP 8.4 - 8.6, nightly 8.7), with PHP CS Fixer, PHPStan (level max) and `composer audit` ([#10](https://github.com/tomaj/meta-scraper/pull/10)).
- The Composer dist archive contains only the library (`src/`, `composer.json`, README, LICENSE, CHANGELOG and UPGRADE files); tests and tool configuration are export-ignored ([#13](https://github.com/tomaj/meta-scraper/issues/13)).
- PHPUnit 13; `codeclimate/php-test-reporter` and PHP_CodeSniffer removed in favour of PHP CS Fixer ([#10](https://github.com/tomaj/meta-scraper/pull/10)).
- Development tooling at the shared baseline: PHPStan with bleedingEdge and deprecation rules, Rector, Infection, and the `rector`, `rector-fix`, `coverage` and `infection` composer scripts ([#15](https://github.com/tomaj/meta-scraper/issues/15)).

## [4.3.0] - 2020-11-23

### Added
- `Tomaj\Scraper\Parser\OgDomParser`: parses the same meta and Open Graph tags as `OgParser` with the DOM extension instead of regular expressions (needs `ext-dom` and `ext-libxml`) ([#7](https://github.com/tomaj/meta-scraper/pull/7)).
- `Meta::addAuthorByName()` and `Meta::addSectionByName()`.

## [4.2.0] - 2020-11-21

### Added
- Guzzle 7 support (`guzzlehttp/guzzle` `^6.0 | ^7.0`).
- `OgParser` reads meta tags whose attributes use single quotes ([#6](https://github.com/tomaj/meta-scraper/pull/6)).

### Changed
- Autoloading switched from a classmap to PSR-4 (`Tomaj\Scraper\` → `src/`).

## [4.1.0] - 2020-06-05

### Added
- Tags: `Tomaj\Scraper\Tag`, `Meta::addTag()` / `getTags()`. `SchemaParser` fills them from the schema `about` field; `Meta::merge()` merges them ([#5](https://github.com/tomaj/meta-scraper/pull/5)).

## [4.0.0] - 2020-05-06

### Added
- `Scraper::__construct(array $guzzleOptions = [])` passes options to the Guzzle client (merged with the defaults: 5 second connect timeout and the `Tomaj\Scraper` User-Agent) ([#4](https://github.com/tomaj/meta-scraper/pull/4)).

### Changed
- **BREAKING CHANGE**: the `$timeout` parameter of `Scraper::parseUrl()` was removed; pass `new Scraper(['connect_timeout' => 10])` instead.
- `OgParser` also matches meta tags whose `content` attribute is not the last one.

## [3.0.0] - 2019-08-13

### Added
- `Tomaj\Scraper\Author` and `Tomaj\Scraper\Section` value objects with `id()` and `name()`.
- `SchemaParser` reads sections (with their ids) from `articleTerms` entries of type `Category`; `articleSection` is used when `articleTerms` is missing.

### Changed
- **BREAKING CHANGE**: `Meta::addAuthor()` takes an `Author` and `Meta::addSection()` takes a `Section`; `getAuthors()` / `getSections()` return these objects instead of arrays / strings. Replace `$author['name']` with `$author->name()` and `$author['id']` with `$author->id()`.

## [2.0.1] - 2019-07-22

### Fixed
- `SchemaParser` no longer raises an "Undefined offset" notice on pages without a schema block ([#3](https://github.com/tomaj/meta-scraper/pull/3)).

## [2.0.0] - 2019-07-19

Pluggable parsers: the scraper now combines the results of one or more parsers.

### Added
- `Tomaj\Scraper\Parser\ParserInterface` and two parsers: `OgParser` (meta and Open Graph tags) and `SchemaParser` (the JSON-LD `<script id="schema" type="application/ld+json">` block: headline, description, image, url, authors, sections, published/modified dates).
- `Meta::merge()`: values missing in the first parser's result are filled from the next parsers.
- Multiple authors and sections: `Meta::addAuthor()` / `getAuthors()` and `Meta::addSection()` / `getSections()` ([#2](https://github.com/tomaj/meta-scraper/pull/2)).

### Changed
- **BREAKING CHANGE**: PHP 7.1 or newer is required.
- **BREAKING CHANGE**: `Scraper::parse($content, array $parsers)` and `Scraper::parseUrl($url, array $parsers, $timeout = 5)` require the list of parsers to use; pass `[new OgParser()]` for the 1.x behaviour.
- **BREAKING CHANGE**: `Meta::setAuthor()` / `getAuthor()` and `Meta::setSection()` / `getSection()` were replaced by `addAuthor()` / `getAuthors()` (each author is an array with `id` and `name`) and `addSection()` / `getSections()`.
- **BREAKING CHANGE**: `Meta` and `Scraper` methods declare scalar parameter and return types (`declare(strict_types=1)`).

## [1.5.0] - 2018-05-18

### Changed
- Meta tags are parsed in one pass: every `<meta>` tag is read once and its `name` or `property` attribute (case-insensitive) is mapped to the `Meta` fields, so attribute order no longer matters.

## [1.4.0] - 2018-05-18

### Changed
- `<title>` tags with attributes and meta tags with additional attributes (e.g. `<meta itemprop="..." name="description" content="...">`) are now recognised.

## [1.3.0] - 2017-01-23

### Fixed
- An `article:published_time` / `article:modified_time` value that `strtotime()` cannot parse is ignored instead of being stored as `1970-01-01`.

## [1.2.1] - 2016-07-28

### Fixed
- Syntax error in `Scraper::parseUrl()` introduced in 1.2.0.

## [1.2.0] - 2016-07-28

### Added
- `Scraper::getBody()` returns the body of the page downloaded by the last `parseUrl()` call.

**Note:** this release contains a syntax error in `Scraper::parseUrl()`; use 1.2.1 instead.

## [1.1.0] - 2015-10-12

### Added
- `Meta::getSection()`, `Meta::getPublishedTime()` and `Meta::getModifiedTime()`, filled from the `article:section`, `article:published_time` and `article:modified_time` meta properties. Times are returned as `DateTime`.
- Installation instructions in the README.

## [1.0.0] - 2015-07-28

Initial release.

### Added
- `Scraper::parse($content)` extracts page meta information from an HTML string and returns a `Meta` object.
- `Scraper::parseUrl($url, $timeout = 5)` downloads the page with Guzzle 6 (5 second connect timeout) and parses it.
- Parsed fields: `<title>`, `description`, `keywords` and `author` meta tags, and the Open Graph properties `og:title`, `og:description`, `og:type`, `og:url`, `og:site_name` and `og:image` (HTML entities are decoded).
- Requires PHP 5.5 or newer.

[unreleased]: https://github.com/tomaj/meta-scraper/compare/4.3.0...HEAD
[4.3.0]: https://github.com/tomaj/meta-scraper/compare/4.2.0...4.3.0
[4.2.0]: https://github.com/tomaj/meta-scraper/compare/4.1.0...4.2.0
[4.1.0]: https://github.com/tomaj/meta-scraper/compare/4.0.0...4.1.0
[4.0.0]: https://github.com/tomaj/meta-scraper/compare/3.0.0...4.0.0
[3.0.0]: https://github.com/tomaj/meta-scraper/compare/2.0.1...3.0.0
[2.0.1]: https://github.com/tomaj/meta-scraper/compare/2.0.0...2.0.1
[2.0.0]: https://github.com/tomaj/meta-scraper/compare/1.5.0...2.0.0
[1.5.0]: https://github.com/tomaj/meta-scraper/compare/1.4.0...1.5.0
[1.4.0]: https://github.com/tomaj/meta-scraper/compare/1.3.0...1.4.0
[1.3.0]: https://github.com/tomaj/meta-scraper/compare/1.2.1...1.3.0
[1.2.1]: https://github.com/tomaj/meta-scraper/compare/1.2.0...1.2.1
[1.2.0]: https://github.com/tomaj/meta-scraper/compare/1.1.0...1.2.0
[1.1.0]: https://github.com/tomaj/meta-scraper/compare/1.0.0...1.1.0
[1.0.0]: https://github.com/tomaj/meta-scraper/releases/tag/1.0.0

# Changelog

All notable changes to this project will be documented in this file.

## [Unreleased]

### Changed
- **BC break:** PHP 8.3 or newer is required (was PHP 7.1).
- **BC break:** `guzzlehttp/guzzle` 6 is no longer supported, `^7.9` is required.
- **BC break:** properties are typed and `Meta::setPublishedTime()` / `Meta::setModifiedTime()` accept `DateTime|string` (`setModifiedTime()` now declares the `Meta` return type). Subclasses overriding these members must be updated.
- `SchemaParser` ignores schema values of unexpected types instead of failing (a TypeError before), accepts a single `author` object (it used to produce one empty author per key), authors given as plain strings, and numeric `@id` values everywhere; an empty `about` list no longer adds an empty tag.
- A published/modified time of exactly `1970-01-01T00:00:00Z` is no longer ignored.
- Parsers decode `&#039;` / `&#x27;` to `'`: since PHP 8.1 `htmlspecialchars_decode()` decodes single quotes by default, so this differs from running 1.x on PHP 7.
- CI moved from Travis CI to GitHub Actions (PHP 8.3 - 8.6, nightly 8.7), with PHP CS Fixer, PHPStan (level max) and `composer audit`.
- PHPUnit 12; `codeclimate/php-test-reporter` and PHP_CodeSniffer removed in favour of PHP CS Fixer.

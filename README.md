# Meta Scraper

[![Build Status](https://travis-ci.org/tomaj/meta-scraper.svg?branch=master)](https://travis-ci.org/tomaj/meta-scraper)
[![Code Climate](https://codeclimate.com/github/tomaj/meta-scraper/badges/gpa.svg)](https://codeclimate.com/github/tomaj/meta-scraper)
[![Test Coverage](https://codeclimate.com/github/tomaj/meta-scraper/badges/coverage.svg)](https://codeclimate.com/github/tomaj/meta-scraper/coverage)

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/abee19ff-2c5b-443d-ae84-04537b155287/big.png)](https://insight.sensiolabs.com/projects/abee19ff-2c5b-443d-ae84-04537b155287)

Page meta scraper parse meta information from page.

## Instalation

via composer:

```bash
composer require tomaj/meta-scraper
```

## How to use

Example:

```php
use Tomaj\Scraper\Scraper;
use Tomaj\Scrapper\Parser\OgParser;

$scraper = new Scraper();
$parsers = [new OgParser()];
$meta = $scraper->parse(file_get_contents('http://www.google.com/'), $parsers);

var_dump($meta);
```

or you can use ```parseUrl``` method (internaly use [Guzzle library](https://guzzle.readthedocs.org/en/latest/))

```php
use Tomaj\Scraper\Scraper;
use Tomaj\Scrapper\Parser\OgParser;

$scraper = new Scraper();
$parsers = [new OgParser()];
$meta = $scraper->parseUrl('http://www.google.com/', $parsers);

var_dump($meta);
```

## Parsers

There are 2 parsers included in package and you can crate new implementing interface `Tomaj\Scraper\Parser\ParserInterface`.

2 parsers:
 - `Tomaj\Scraper\Parser\OgParsers` - based on og meta attributes in html
 - `Tomaj\Scraper\Parser\SchemaParser` - based on schema json structure

You can combine these parsers. Data that will not fe found in first parser will be replaced with data from second parser.

```php
use Tomaj\Scraper\Scraper;
use Tomaj\Scrapper\Parser\SchemaParser;
use Tomaj\Scrapper\Parser\OgParser;

$scraper = new Scraper();
$parsers = [new SchemaParser(), new OgParser()];
$meta = $scraper->parseUrl('http://www.google.com/', $parsers);

var_dump($meta);
```

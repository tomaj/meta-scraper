# Meta Scraper

[![Build Status](https://travis-ci.org/tomaj/meta-scraper.svg?branch=master)](https://travis-ci.org/tomaj/meta-scraper)
[![Code Climate](https://codeclimate.com/github/tomaj/meta-scraper/badges/gpa.svg)](https://codeclimate.com/github/tomaj/meta-scraper)
[![Test Coverage](https://codeclimate.com/github/tomaj/meta-scraper/badges/coverage.svg)](https://codeclimate.com/github/tomaj/meta-scraper/coverage)

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/abee19ff-2c5b-443d-ae84-04537b155287/big.png)](https://insight.sensiolabs.com/projects/abee19ff-2c5b-443d-ae84-04537b155287)

Page meta scraper parse meta information from page.

## Installation

via composer:

```bash
composer require tomaj/meta-scraper
```

## How to use

Example:

```php
use Tomaj\Scraper\Scraper;
use Tomaj\Scraper\Parser\OgParser;

$scraper = new Scraper();
$parsers = [new OgParser()];
$meta = $scraper->parse(file_get_contents('http://www.google.com/'), $parsers);

var_dump($meta);
```

or you can use ```parseUrl``` method (internally use [Guzzle library](https://guzzle.readthedocs.org/en/latest/))

```php
use Tomaj\Scraper\Scraper;
use Tomaj\Scraper\Parser\OgParser;

$scraper = new Scraper();
$parsers = [new OgParser()];
$meta = $scraper->parseUrl('http://www.google.com/', $parsers);

var_dump($meta);
```

## Parsers

There are 3 parsers included in package and you can create new implementing interface `Tomaj\Scraper\Parser\ParserInterface`.

3 parsers:
 - `Tomaj\Scraper\Parser\OgParser` - based on og (Open Graph) meta attributes in html (built on regular expressions)
 - `Tomaj\Scraper\Parser\OgDomParser` - also based on og (Open Graph) meta attributes in html (built on php DOM extension)
 - `Tomaj\Scraper\Parser\SchemaParser` - based on schema json structure

You can combine these parsers. Data that will not be found in first parser will be replaced with data from second parser.

```php
use Tomaj\Scraper\Scraper;
use Tomaj\Scraper\Parser\SchemaParser;
use Tomaj\Scraper\Parser\OgParser;

$scraper = new Scraper();
$parsers = [new SchemaParser(), new OgParser()];
$meta = $scraper->parseUrl('http://www.google.com/', $parsers);

var_dump($meta);
```

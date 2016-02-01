# Meta Scraper

[![Build Status](https://travis-ci.org/tomaj/meta-scraper.svg?branch=master)](https://travis-ci.org/tomaj/meta-scraper)
[![Dependency Status](https://www.versioneye.com/user/projects/5623500236d0ab0016000bdd/badge.svg?style=flat)](https://www.versioneye.com/user/projects/5623500236d0ab0016000bdd)
[![Code Climate](https://codeclimate.com/github/tomaj/meta-scraper/badges/gpa.svg)](https://codeclimate.com/github/tomaj/meta-scraper)
[![Test Coverage](https://codeclimate.com/github/tomaj/meta-scraper/badges/coverage.svg)](https://codeclimate.com/github/tomaj/meta-scraper/coverage)

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/abee19ff-2c5b-443d-ae84-04537b155287/big.png)](https://insight.sensiolabs.com/projects/abee19ff-2c5b-443d-ae84-04537b155287)

Page meta scraper parse meta information from page.

## Instalation

via composer:

```
composer require tomaj/meta-scraper
```

## How to use

Example:

```
use Tomaj\Scraper\Scraper;
$scraper = new Scraper();
$meta = $scraper->parse(file_get_contents('http://www.google.com/'));
var_dump($meta);
```

or you can use ```parseUrl``` method (internaly use [Guzzle library](https://guzzle.readthedocs.org/en/latest/))

```
use Tomaj\Scraper\Scraper;
$scraper = new Scraper();
$meta = $scraper->parseUrl('http://www.google.com/');
var_dump($meta);
```

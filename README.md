# Meta Scraper

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

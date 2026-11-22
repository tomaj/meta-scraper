<?php

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use Tomaj\Scraper\Exception\FetchException;
use Tomaj\Scraper\Parser\OgDomParser;
use Tomaj\Scraper\Parser\SchemaParser;
use Tomaj\Scraper\Scraper;

$scraper = new Scraper([
    'connect_timeout' => 3,
    'timeout' => 10,
    'headers' => ['User-Agent' => 'MyBot/1.0 (+https://example.com/bot)'],
    'allow_redirects' => ['max' => 3, 'protocols' => ['https']],
]);

try {
    $meta = $scraper->parseUrl('https://example.com/article', [new SchemaParser(), new OgDomParser()]);
    echo $meta->getTitle(), "\n";

    // the downloaded HTML, for example to store it or run other parsers on it
    $html = $scraper->getBody();
} catch (FetchException $e) {
    // connection error, timeout, TLS error, too many redirects, 4xx or 5xx response
    error_log(sprintf('Cannot scrape %s: %s', $e->url, $e->getMessage()));
}

<?php

declare(strict_types=1);

// Every way parseUrl() can fail, against a fixture server (site/router.php):
// HTTP errors, a timeout, too many redirects and a refused connection.

require __DIR__ . '/vendor/autoload.php';

use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\TooManyRedirectsException;
use Tomaj\Scraper\Exception\FetchException;
use Tomaj\Scraper\Exception\ScraperException;
use Tomaj\Scraper\Parser\OgParser;
use Tomaj\Scraper\Scraper;

$base = $argv[1] ?? 'http://127.0.0.1:8405';

$scraper = new Scraper([
    'connect_timeout' => 2,
    'timeout' => 1,
    'allow_redirects' => ['max' => 3],
    'headers' => ['User-Agent' => 'ExampleBot/1.0'],
]);
$parsers = [new OgParser()];

function fetch(Scraper $scraper, string $url, array $parsers): void
{
    $path = parse_url($url, PHP_URL_PATH) ?? $url;
    try {
        $meta = $scraper->parseUrl($url, $parsers);
        printf("%-18s ok, title \"%s\"\n", $path, $meta->getTitle());
    } catch (FetchException $e) {
        // $e->url is the URL you passed, getPrevious() the Guzzle exception
        printf("%-18s FetchException: %s\n", $path, reason($e->getPrevious()));
    }
}

// The Guzzle exception classes differ between Guzzle 7 and 8; these checks work with both.
function reason(?Throwable $previous): string
{
    return match (true) {
        $previous instanceof TooManyRedirectsException => 'too many redirects',
        $previous instanceof BadResponseException => 'HTTP ' . $previous->getResponse()->getStatusCode(),
        $previous !== null && str_contains($previous->getMessage(), 'timed out') => 'timed out',
        $previous !== null && str_contains($previous->getMessage(), 'cURL error 7:') => 'connection refused',
        default => 'other error',
    };
}

echo "== parseUrl() with a 1 s timeout and at most 3 redirects\n";
foreach (['/ok', '/moved', '/echo-user-agent', '/missing', '/error', '/slow', '/loop'] as $path) {
    fetch($scraper, $base . $path, $parsers);
}

// nothing listens on this port: the connection is refused
fetch($scraper, 'http://127.0.0.1:9/closed-port', $parsers);


echo "\n== http_errors: false parses error pages instead of throwing\n";
$lenient = new Scraper(['timeout' => 1, 'http_errors' => false]);
fetch($lenient, $base . '/missing', $parsers);

echo "\n== allow_redirects: false returns the redirect response itself\n";
$noRedirects = new Scraper(['timeout' => 1, 'allow_redirects' => false]);
$meta = $noRedirects->parseUrl($base . '/moved', $parsers);
printf("%-18s ok, title %s, body %d bytes\n", '/moved', var_export($meta->getTitle(), true), strlen($noRedirects->getBody()));

echo "\n== catch every exception of the package with ScraperException\n";
try {
    $scraper->parseUrl($base . '/error', $parsers);
} catch (ScraperException $e) {
    // the message is "Cannot fetch <url>: " followed by the Guzzle message
    echo get_class($e), "\n", explode(' resulted in', $e->getMessage())[0], "\n";
}

<?php

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Tomaj\Scraper\Parser\OgParser;
use Tomaj\Scraper\Scraper;

// retry connection errors and 5xx responses twice
$stack = HandlerStack::create();
$stack->push(Middleware::retry(
    static fn(int $retries, RequestInterface $request, ?ResponseInterface $response = null): bool
        => $retries < 2 && (!$response instanceof ResponseInterface || $response->getStatusCode() >= 500),
));

$client = new Client([
    'handler' => $stack,
    'connect_timeout' => 5,
    'timeout' => 15,
    'headers' => ['User-Agent' => 'MyBot/1.0'],
]);

// the defaults (5 s connect timeout, "Tomaj\Scraper" User-Agent) are not applied to your client;
// the array is sent as request options with every request
$scraper = new Scraper(['timeout' => 30], $client);

$meta = $scraper->parseUrl('https://example.com/', [new OgParser()]);

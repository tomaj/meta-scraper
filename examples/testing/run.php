<?php

declare(strict_types=1);

// How to test code that calls parseUrl(): give the Scraper a Guzzle client whose handler is a
// MockHandler with canned responses, and record the requests with the history middleware.
// The same setup works inside PHPUnit tests; here plain checks keep the example small.

require __DIR__ . '/vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Tomaj\Scraper\Exception\FetchException;
use Tomaj\Scraper\Parser\OgDomParser;
use Tomaj\Scraper\Scraper;

// The code under test: something in your application that uses the scraper.
final readonly class ArticleImporter
{
    public function __construct(private Scraper $scraper) {}

    /**
     * @return array{title: ?string, image: ?string}|null null when the page cannot be downloaded
     */
    public function import(string $url): ?array
    {
        try {
            $meta = $this->scraper->parseUrl($url, [new OgDomParser()]);
        } catch (FetchException) {
            return null;
        }

        return ['title' => $meta->getOgTitle() ?? $meta->getTitle(), 'image' => $meta->getOgImage()];
    }
}

function check(string $name, bool $passed): void
{
    echo ($passed ? 'ok    ' : 'FAIL  '), $name, "\n";
    if (!$passed) {
        exit(1);
    }
}

// canned responses, returned in this order
$mock = new MockHandler([
    new Response(200, ['Content-Type' => 'text/html'], '<title>Fallback</title><meta property="og:title" content="Mocked article"><meta property="og:image" content="https://cdn.example/a.jpg">'),
    new Response(404, [], '<title>Not found</title>'),
    new ConnectException('Connection refused', new Request('GET', 'https://down.example/')),
]);
$history = [];
$stack = HandlerStack::create($mock);
$stack->push(Middleware::history($history));

// your own client: the scraper's defaults are not applied, the array becomes request options
$scraper = new Scraper(['headers' => ['User-Agent' => 'ImporterBot/2.0'], 'timeout' => 5], new Client(['handler' => $stack]));
$importer = new ArticleImporter($scraper);

$article = $importer->import('https://news.example/article');
check('Open Graph title is preferred', $article['title'] === 'Mocked article');
check('image is read', $article['image'] === 'https://cdn.example/a.jpg');
check('a 404 response gives null', $importer->import('https://news.example/missing') === null);
check('a connection error gives null', $importer->import('https://down.example/') === null);

// what was sent
check('three requests were made', count($history) === 3);
$request = $history[0]['request'];
check('the request is a GET', $request->getMethod() === 'GET');
check('to the right URL', (string) $request->getUri() === 'https://news.example/article');
check('with the User-Agent from the request options', $request->getHeaderLine('User-Agent') === 'ImporterBot/2.0');
check('with the timeout from the request options', $history[0]['options']['timeout'] === 5);
check('every canned response was used', $mock->count() === 0);

// getBody() keeps the body of the last successful download
check('getBody() returns the downloaded HTML', str_contains($scraper->getBody(), 'Mocked article'));

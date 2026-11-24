---
description: Test code that uses meta-scraper without network - Guzzle MockHandler, the history middleware, HTML fixtures and a local php -S server.
---

# Testing your code

Code that calls `parse()` needs nothing special: give it HTML fixtures. Code that calls `parseUrl()` downloads pages; in tests, give the `Scraper` a Guzzle client that answers from a list of prepared responses instead.

## Guzzle MockHandler

Pass a client whose handler is a `MockHandler` as the second constructor argument. Add the `history` middleware to check what was sent:

```php
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;

$mock = new MockHandler([
    new Response(200, [], '<title>Test page</title>'),
    new Response(500),
]);
$history = [];
$stack = HandlerStack::create($mock);
$stack->push(Middleware::history($history));

$scraper = new Scraper(['headers' => ['User-Agent' => 'TestBot']], new Client(['handler' => $stack]));

echo $scraper->parseUrl('https://example.com/', [new OgParser()])->getTitle(), "\n";
try {
    $scraper->parseUrl('https://example.com/broken', [new OgParser()]);
} catch (FetchException $e) {
    echo 'FetchException for ', $e->url, "\n";
}
echo $history[0]['request']->getHeaderLine('User-Agent'), "\n";
```

<!-- snippet: output -->
```text
Test page
FetchException for https://example.com/broken
TestBot
```

Inject the `Scraper` into your services (constructor injection) instead of creating it inside them, so tests can pass this one.

The [testing example](../examples/testing.md) tests a small importer service this way: success, a 404, a connection error, the request method, URL, headers and options.

## In PHPUnit

The same setup in a test case:

<!-- snippet: skip -->
```php
final class ArticleImporterTest extends TestCase
{
    public function testImportsTheOpenGraphTitle(): void
    {
        $mock = new MockHandler([new Response(200, [], file_get_contents(__DIR__ . '/fixtures/article.html'))]);
        $scraper = new Scraper([], new Client(['handler' => HandlerStack::create($mock)]));

        $article = new ArticleImporter($scraper)->import('https://news.example/a');

        self::assertSame('Expected title', $article['title']);
    }
}
```

Keep real pages you had problems with as fixtures (save them with `getBody()`), so a parser change that breaks them shows up in your tests.

## A local web server

For end-to-end tests that should go through real HTTP (redirects, timeouts, headers), serve fixture pages with PHP's built-in server and point `parseUrl()` at it:

```bash
php -S 127.0.0.1:8080 -t tests/fixtures/site tests/fixtures/site/router.php
```

All examples of this documentation work this way: the `e2e.sh` of each example starts `php -S` with the example's `site/` directory, runs `run.php` and compares the output with `expected-output.txt`. A `router.php` can answer with any status, header or delay (see [`fetch-errors/site/router.php`](../examples/fetch-errors.md#the-fixture-server)).

## Testing your own parser

A parser is a function of a string: test it with HTML strings, including the empty string and broken markup, and check that two calls return two different `Meta` objects. See [Writing a parser](./custom-parser.md#testing-a-parser).

<?php

declare(strict_types=1);

namespace Tomaj\Scraper\Test;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Tomaj\Scraper\Exception\FetchException;
use Tomaj\Scraper\Exception\ScraperException;
use Tomaj\Scraper\Meta;
use Tomaj\Scraper\Parser\OgParser;
use Tomaj\Scraper\Parser\ParserInterface;
use Tomaj\Scraper\Scraper;

final class ScraperTest extends TestCase
{
    private const string PAGE = '<html><head><title>Fetched page</title><meta property="og:title" content="OG title"></head></html>';

    /** @var list<RequestInterface> requests sent through handler() */
    private array $requests = [];

    /** @var list<array<mixed>> request options of those requests */
    private array $options = [];

    private function scraperWith(Response|\Throwable ...$responses): Scraper
    {
        return new Scraper([], new Client(['handler' => $this->handler(...$responses)]));
    }

    private function handler(Response|\Throwable ...$responses): callable
    {
        $stack = HandlerStack::create(new MockHandler(array_values($responses)));
        $stack->push(Middleware::tap(function (RequestInterface $request, array $options): void {
            $this->requests[] = $request;
            $this->options[] = $options;
        }));

        return $stack;
    }

    public function testParseUrlDownloadsAndParsesThePage(): void
    {
        $scraper = $this->scraperWith(new Response(200, [], self::PAGE));

        $meta = $scraper->parseUrl('https://example.com/article', [new OgParser()]);

        self::assertSame('Fetched page', $meta->getTitle());
        self::assertSame('OG title', $meta->getOgTitle());
        self::assertSame(self::PAGE, $scraper->getBody());
        self::assertCount(1, $this->requests);
        self::assertSame('GET', $this->requests[0]->getMethod());
        self::assertSame('https://example.com/article', (string) $this->requests[0]->getUri());
    }

    public function testBodyIsEmptyBeforeTheFirstDownload(): void
    {
        self::assertSame('', new Scraper()->getBody());
    }

    public function testOwnClientUsesTheDefaultOptions(): void
    {
        // @phpstan-ignore argument.type ("handler" is not part of GuzzleOptions; the test replaces the transport of the client Scraper creates)
        $scraper = new Scraper(['handler' => $this->handler(new Response(200, [], self::PAGE))]);

        $scraper->parseUrl('https://example.com/', []);

        self::assertSame('Tomaj\Scraper', $this->requests[0]->getHeaderLine('User-Agent'));
        self::assertSame(5, $this->options[0]['connect_timeout']);
    }

    public function testOptionsOverrideTheDefaults(): void
    {
        // @phpstan-ignore argument.type ("handler" is not part of GuzzleOptions; the test replaces the transport of the client Scraper creates)
        $scraper = new Scraper([
            'handler' => $this->handler(new Response(200, [], self::PAGE)),
            'connect_timeout' => 2,
            'timeout' => 10,
            'headers' => ['User-Agent' => 'MyBot/1.0'],
        ]);

        $scraper->parseUrl('https://example.com/', []);

        self::assertSame('MyBot/1.0', $this->requests[0]->getHeaderLine('User-Agent'));
        self::assertSame(2, $this->options[0]['connect_timeout']);
        self::assertSame(10, $this->options[0]['timeout']);
    }

    public function testInjectedClientGetsTheOptionsAsRequestOptionsAndNoDefaults(): void
    {
        $client = new Client(['handler' => $this->handler(new Response(200, [], self::PAGE), new Response(200, [], self::PAGE))]);

        new Scraper([], $client)->parseUrl('https://example.com/', []);
        self::assertNotSame('Tomaj\Scraper', $this->requests[0]->getHeaderLine('User-Agent'));
        self::assertArrayNotHasKey('connect_timeout', $this->options[0]);

        new Scraper(['timeout' => 3, 'headers' => ['X-Test' => 'yes']], $client)->parseUrl('https://example.com/', []);
        self::assertSame(3, $this->options[1]['timeout']);
        self::assertSame('yes', $this->requests[1]->getHeaderLine('X-Test'));
    }

    public function testConnectionErrorIsWrappedInAPackageException(): void
    {
        $previous = new ConnectException('Connection refused', new Request('GET', 'https://example.com/'));
        $scraper = $this->scraperWith($previous);

        try {
            $scraper->parseUrl('https://example.com/', [new OgParser()]);
            self::fail('FetchException expected');
        } catch (FetchException $e) {
            self::assertSame($previous, $e->getPrevious());
            self::assertSame('https://example.com/', $e->url);
            self::assertSame(0, $e->getCode());
            self::assertSame('Cannot fetch "https://example.com/": Connection refused', $e->getMessage());
        }
        self::assertSame('', $scraper->getBody());
    }

    public function testErrorResponseIsWrappedInAPackageException(): void
    {
        $scraper = $this->scraperWith(new Response(404, [], 'Not found'));

        try {
            $scraper->parseUrl('https://example.com/missing', [new OgParser()]);
            self::fail('FetchException expected');
        } catch (ScraperException $e) {
            self::assertInstanceOf(FetchException::class, $e);
            self::assertInstanceOf(GuzzleException::class, $e->getPrevious());
            self::assertStringContainsString('404', $e->getMessage());
        }
    }

    public function testParseRunsTheParsersInOrderAndTheFirstValueWins(): void
    {
        $first = $this->parserReturning(new Meta()->setTitle('First')->setDescription(''));
        $second = $this->parserReturning(new Meta()->setTitle('Second')->setDescription('From second')->setOgType('article'));

        $meta = new Scraper()->parse('<html></html>', [$first, $second]);

        self::assertSame('First', $meta->getTitle());
        self::assertSame('From second', $meta->getDescription());
        self::assertSame('article', $meta->getOgType());
    }

    public function testParseWithoutParsersReturnsAnEmptyMeta(): void
    {
        self::assertEquals(new Meta(), new Scraper()->parse('<title>x</title>', []));
    }

    private function parserReturning(Meta $meta): ParserInterface
    {
        return new readonly class ($meta) implements ParserInterface {
            public function __construct(private Meta $meta) {}

            #[\Override]
            public function parse(string $content): Meta
            {
                return $this->meta;
            }
        };
    }
}

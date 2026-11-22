<?php

declare(strict_types=1);

namespace Tomaj\Scraper;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Tomaj\Scraper\Exception\FetchException;
use Tomaj\Scraper\Parser\ParserInterface;

/**
 * Runs parsers over an HTML document and merges their results.
 *
 * GuzzleOptions lists the Guzzle options the scraper accepts (typed the same for Guzzle 7 and 8). For anything else
 * (handler stack, cookies, auth, ...) configure a Guzzle client yourself and pass it as $httpClient.
 *
 * @phpstan-type GuzzleOptions array{
 *     connect_timeout?: int|float,
 *     timeout?: int|float,
 *     read_timeout?: int|float,
 *     headers?: array<string, string|non-empty-list<string>>,
 *     proxy?: string|array{http?: string, https?: string, no?: list<string>},
 *     allow_redirects?: bool|array{max?: int, strict?: bool, referer?: bool, protocols?: non-empty-list<string>, track_redirects?: bool},
 *     verify?: bool|string,
 *     http_errors?: bool,
 *     decode_content?: bool|string,
 *     force_ip_resolve?: string,
 *     version?: string|float
 * }
 */
final class Scraper
{
    private const array DEFAULT_OPTIONS = [
        'connect_timeout' => 5,
        'headers' => [
            'User-Agent' => 'Tomaj\Scraper',
        ],
    ];

    private readonly ClientInterface $httpClient;

    /** @var GuzzleOptions */
    private readonly array $requestOptions;

    private string $body = '';

    /**
     * @param GuzzleOptions $guzzleOptions Without $httpClient: configuration of the Guzzle client the scraper creates,
     *                                     merged over the defaults (5 s connect timeout, "Tomaj\Scraper" User-Agent).
     *                                     With $httpClient: request options sent with every request.
     * @param ClientInterface|null $httpClient Your own configured Guzzle client (defaults are not applied to it).
     */
    public function __construct(array $guzzleOptions = [], ?ClientInterface $httpClient = null)
    {
        if ($httpClient instanceof ClientInterface) {
            $this->httpClient = $httpClient;
            $this->requestOptions = $guzzleOptions;
        } else {
            $this->httpClient = new Client(array_merge(self::DEFAULT_OPTIONS, $guzzleOptions));
            $this->requestOptions = [];
        }
    }

    /**
     * Downloads the page with a GET request and parses it.
     *
     * Do not pass untrusted URLs without SSRF protection (see the security notes in the documentation).
     *
     * @param array<ParserInterface> $parsers
     * @throws FetchException when the request fails or the server responds with 4xx/5xx
     */
    public function parseUrl(string $url, array $parsers): Meta
    {
        try {
            $response = $this->httpClient->request('GET', $url, $this->requestOptions);
        } catch (GuzzleException $e) {
            throw new FetchException($url, $e);
        }

        $this->body = (string) $response->getBody();

        return $this->parse($this->body, $parsers);
    }

    /**
     * Runs the parsers in order; a value found by an earlier parser is kept.
     *
     * @param array<ParserInterface> $parsers
     */
    public function parse(string $content, array $parsers): Meta
    {
        $meta = new Meta();
        foreach ($parsers as $parser) {
            $meta->merge($parser->parse($content));
        }
        return $meta;
    }

    /**
     * The body downloaded by the last parseUrl() call ('' before the first call).
     */
    public function getBody(): string
    {
        return $this->body;
    }
}

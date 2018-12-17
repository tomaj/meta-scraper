<?php
declare(strict_types=1);

namespace Tomaj\Scraper;

use GuzzleHttp\Client;

class Scraper
{
    protected $userAgent = 'Tomaj\Scraper';

    private $body;

    /**
     * @throws \GuzzleHttp\Exception\RequestException
     */
    public function parseUrl(string $url, array $parsers, int $timeout = 5): Meta
    {
        $client = new Client();
        $res = $client->get($url, ['connect_timeout' => $timeout]);

        $this->body = $res->getBody();

        return $this->parse($this->body, $parsers);
    }

    public function parse(string $content, array $parsers): Meta
    {
        $meta = new Meta();
        foreach ($parsers as $parser) {
            $newMeta = $parser->parse($content);
            $meta->merge($newMeta);
        }
        return $meta;
    }

    public function getBody(): string
    {
        return $this->body;
    }
}

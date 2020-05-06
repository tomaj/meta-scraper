<?php
declare(strict_types=1);

namespace Tomaj\Scraper;

use GuzzleHttp\Client;

class Scraper
{
    protected $guzzleOptions = [
        'connect_timeout' => 5,
        'headers' => [
            'User-Agent' => 'Tomaj\Scraper'
        ]
    ];
    
    private $body;
    
    public function __construct(array $guzzleOptions = [])
    {
        $this->guzzleOptions = array_merge($this->guzzleOptions, $guzzleOptions);
    }

    /**
     * @throws \GuzzleHttp\Exception\RequestException
     */
    public function parseUrl(string $url, array $parsers): Meta
    {
        $client = new Client($this->guzzleOptions);
        $res = $client->get($url);

        $this->body = (string) $res->getBody();

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

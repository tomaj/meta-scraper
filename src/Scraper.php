<?php

namespace Tomaj\Scraper;

use GuzzleHttp\Client;

class Scraper
{
    protected $userAgent = 'Tomaj\Scraper';

    private $body;

    /**
     * @throws \GuzzleHttp\Exception\RequestException
     */
    public function parseUrl($url, $timeout = 5)
    {
        $client = new Client();
        $res = $client->get($url, ['connect_timeout' => $timeout]);

        $this->body = $res->getBody();

        return $this->parse($this->body);
    }

    public function parse($content)
    {
        $meta = new Meta();

        $matches = [];

        if (!$content) {
            return $meta;
        }

        preg_match('/<title.*>(.+)<\/title\>/Uis', $content, $matches);
        if ($matches) {
            $meta->setTitle(htmlspecialchars_decode($matches[1]));
        }

        $parsedMeta = [];

        preg_match_all('/<meta.*>/Uis', $content, $matches);
        foreach ($matches[0] as $match) {
            $propertyMatch = [];
            preg_match('/property=\"(.*)\"/Uis', $match, $propertyMatch);

            $nameMatch = [];
            preg_match('/name=\"(.*)\"/Uis', $match, $nameMatch);

            $contentMatch = [];
            preg_match('/content=\"(.*)\"/Uis', $match, $contentMatch);

            if (isset($nameMatch[1]) && isset($contentMatch[1])) {
                $parsedMeta[strtolower($nameMatch[1])] = htmlspecialchars_decode($contentMatch[1]);
            } else if (isset($propertyMatch[1]) && isset($contentMatch[1])) {
                $parsedMeta[strtolower($propertyMatch[1])] = htmlspecialchars_decode($contentMatch[1]);
            }
        }

        if (isset($parsedMeta['description'])) {
            $meta->setDescription($parsedMeta['description']);
        }

        if (isset($parsedMeta['keywords'])) {
            $meta->setKeywords($parsedMeta['keywords']);
        }

        if (isset($parsedMeta['author'])) {
            $meta->setAuthor($parsedMeta['author']);
        }

        if (isset($parsedMeta['og:title'])) {
            $meta->setOgTitle($parsedMeta['og:title']);
        }

        if (isset($parsedMeta['article:section'])) {
            $meta->setSection($parsedMeta['article:section']);
        }

        if (isset($parsedMeta['article:published_time'])) {
            $meta->setPublishedTime($parsedMeta['article:published_time']);
        }

        if (isset($parsedMeta['article:modified_time'])) {
            $meta->setModifiedTime($parsedMeta['article:modified_time']);
        }

        if (isset($parsedMeta['og:description'])) {
            $meta->setOgDescription($parsedMeta['og:description']);
        }

        if (isset($parsedMeta['og:type'])) {
            $meta->setOgType($parsedMeta['og:type']);
        }

        if (isset($parsedMeta['og:url'])) {
            $meta->setOgUrl($parsedMeta['og:url']);
        }

        if (isset($parsedMeta['og:site_name'])) {
            $meta->setOgSiteName($parsedMeta['og:site_name']);
        }

        if (isset($parsedMeta['og:image'])) {
            $meta->setOgImage($parsedMeta['og:image']);
        }

        return $meta;
    }

    public function getBody()
    {
        return $this->body;
    }
}

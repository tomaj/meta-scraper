<?php

namespace Tomaj\Scraper;

use GuzzleHttp\Client;

class Scraper
{
    /**
     * @throws \GuzzleHttp\Exception\RequestException
     */
    public function parseUrl($url)
    {
        $client = new Client();
        $res = $client->get($url);

        return $this->parse($res->getBody());
    }

    public function parse($content)
    {
        $meta = new Meta();

        $matches = [];

        if (!$content) {
            return $meta;
        }

        preg_match('/<title>(.+)<\/title\>/Ui', $content, $matches);
        if ($matches) {
            $meta->setTitle($matches[1]);
        }

        preg_match('/<meta\s*name=\"description\"\s*content=\"(.+)\"\s*[\/]*\>/Ui', $content, $matches);
        if ($matches) {
            $meta->setDescription($matches[1]);
        }

        preg_match('/<meta\s*name=\"keywords\"\s*content=\"(.+)\"\s*[\/]*\>/Ui', $content, $matches);
        if ($matches) {
            $meta->setKeywords($matches[1]);
        }

        preg_match('/<meta\s*name=\"author\"\s*content=\"(.+)\"\s*[\/]*\>/Ui', $content, $matches);
        if ($matches) {
            $meta->setAuthor($matches[1]);
        }

        // todo - optimalize to one preg_match for all og:*
        preg_match('/<meta\s*property=\"og:title\"\s*content=\"(.+)\"\s*[\/]*\>/Ui', $content, $matches);
        if ($matches) {
            $meta->setOgTitle($matches[1]);
        }

        preg_match('/<meta\s*property=\"og:description\"\s*content=\"(.+)\"\s*[\/]*\>/Ui', $content, $matches);
        if ($matches) {
            $meta->setOgDescription($matches[1]);
        }

        preg_match('/<meta\s*property=\"og:type\"\s*content=\"(.+)\"\s*[\/]*\>/Ui', $content, $matches);
        if ($matches) {
            $meta->setOgType($matches[1]);
        }

        preg_match('/<meta\s*property=\"og:url\"\s*content=\"(.+)\"\s*[\/]*\>/Ui', $content, $matches);
        if ($matches) {
            $meta->setOgUrl($matches[1]);
        }

        preg_match('/<meta\s*property=\"og:site_name\"\s*content=\"(.+)\"\s*[\/]*\>/Ui', $content, $matches);
        if ($matches) {
            $meta->setOgSiteName($matches[1]);
        }

        preg_match('/<meta\s*property=\"og:image\"\s*content=\"(.+)\"\s*[\/]*\>/Ui', $content, $matches);
        if ($matches) {
            $meta->setOgImage($matches[1]);
        }

        return $meta;
    }
}

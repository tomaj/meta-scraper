<?php
declare(strict_types=1);

namespace Tomaj\Scraper\Parser;

use Tomaj\Scraper\Meta;

class OgParser implements ParserInterface
{
    public function parse(string $content): Meta
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

        preg_match('/<meta.*name=\"description\".*content=\"(.+)\"\s*[\/]*\>/Uis', $content, $matches);
        if ($matches) {
            $meta->setDescription(htmlspecialchars_decode($matches[1]));
        }

        preg_match('/<meta.*name=\"keywords\".*content=\"(.+)\"\s*[\/]*\>/Uis', $content, $matches);
        if ($matches) {
            $meta->setKeywords(htmlspecialchars_decode($matches[1]));
        }

        preg_match('/<meta.*name=\"author\".*content=\"(.+)\"\s*[\/]*\>/Uis', $content, $matches);
        if ($matches) {
            $meta->addAuthor([
                'id' => null,
                'name' => htmlspecialchars_decode($matches[1]),
            ]);
        }

        // maybe in future - optimalize to one preg_match for all og:*

        preg_match('/<meta.*property=\"og:title\".*content=\"(.+)\"\s*[\/]*\>/Uis', $content, $matches);
        if ($matches) {
            $meta->setOgTitle(htmlspecialchars_decode($matches[1]));
        }

        preg_match('/<meta.*property=\"article:section\".*content=\"(.+)\"\s*[\/]*\>/Uis', $content, $matches);
        if ($matches) {
            $meta->addSection(htmlspecialchars_decode($matches[1]));
        }

        preg_match('/<meta.*property=\"article:published_time\".*content=\"(.+)\"\s*[\/]*\>/Uis', $content, $matches);
        if ($matches) {
            $meta->setPublishedTime(htmlspecialchars_decode($matches[1]));
        }

        preg_match('/<meta.*property=\"article:modified_time\".*content=\"(.+)\"\s*[\/]*\>/Uis', $content, $matches);
        if ($matches) {
            $meta->setModifiedTime(htmlspecialchars_decode($matches[1]));
        }

        preg_match('/<meta.*property=\"og:description\".*content=\"(.+)\"\s*[\/]*\>/Uis', $content, $matches);
        if ($matches) {
            $meta->setOgDescription(htmlspecialchars_decode($matches[1]));
        }

        preg_match('/<meta.*property=\"og:type\".*content=\"(.+)\"\s*[\/]*\>/Uis', $content, $matches);
        if ($matches) {
            $meta->setOgType(htmlspecialchars_decode($matches[1]));
        }

        preg_match('/<meta.*property=\"og:url\".*content=\"(.+)\"\s*[\/]*\>/Uis', $content, $matches);
        if ($matches) {
            $meta->setOgUrl(htmlspecialchars_decode($matches[1]));
        }

        preg_match('/<meta.*property=\"og:site_name\".*content=\"(.+)\"\s*[\/]*\>/Uis', $content, $matches);
        if ($matches) {
            $meta->setOgSiteName(htmlspecialchars_decode($matches[1]));
        }

        preg_match('/<meta.*property=\"og:image\".*content=\"(.+)\"\s*[\/]*\>/Uis', $content, $matches);
        if ($matches) {
            $meta->setOgImage(htmlspecialchars_decode($matches[1]));
        }

        return $meta;
    }
}

<?php
declare(strict_types=1);

namespace Tomaj\Scraper\Parser;

use Tomaj\Scraper\Meta;
use Tomaj\Scraper\Section;
use Tomaj\Scraper\Author;

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

        preg_match('/<meta.*name=\"description\".*content=\"(.+)\"/Uis', $content, $matches);
        if ($matches) {
            $meta->setDescription(htmlspecialchars_decode($matches[1]));
        }

        preg_match('/<meta.*name=\"keywords\".*content=\"(.+)\"/Uis', $content, $matches);
        if ($matches) {
            $meta->setKeywords(htmlspecialchars_decode($matches[1]));
        }

        preg_match('/<meta.*name=\"author\".*content=\"(.+)\"/Uis', $content, $matches);
        if ($matches) {
            $meta->addAuthor(new Author(null, htmlspecialchars_decode($matches[1])));
        }

        // maybe in future - optimalize to one preg_match for all og:*

        preg_match('/<meta.*property=\"og:title\".*content=\"(.+)\"/Uis', $content, $matches);
        if ($matches) {
            $meta->setOgTitle(htmlspecialchars_decode($matches[1]));
        }

        preg_match('/<meta.*property=\"article:section\".*content=\"(.+)\"/Uis', $content, $matches);
        if ($matches) {
            $meta->addSection(new Section(null, htmlspecialchars_decode($matches[1])));
        }

        preg_match('/<meta.*property=\"article:published_time\".*content=\"(.+)\"/Uis', $content, $matches);
        if ($matches) {
            $meta->setPublishedTime(htmlspecialchars_decode($matches[1]));
        }

        preg_match('/<meta.*property=\"article:modified_time\".*content=\"(.+)\"/Uis', $content, $matches);
        if ($matches) {
            $meta->setModifiedTime(htmlspecialchars_decode($matches[1]));
        }

        preg_match('/<meta.*property=\"og:description\".*content=\"(.+)\"/Uis', $content, $matches);
        if ($matches) {
            $meta->setOgDescription(htmlspecialchars_decode($matches[1]));
        }

        preg_match('/<meta.*property=\"og:type\".*content=\"(.+)\"/Uis', $content, $matches);
        if ($matches) {
            $meta->setOgType(htmlspecialchars_decode($matches[1]));
        }

        preg_match('/<meta.*property=\"og:url\".*content=\"(.+)\"/Uis', $content, $matches);
        if ($matches) {
            $meta->setOgUrl(htmlspecialchars_decode($matches[1]));
        }

        preg_match('/<meta.*property=\"og:site_name\".*content=\"(.+)\"/Uis', $content, $matches);
        if ($matches) {
            $meta->setOgSiteName(htmlspecialchars_decode($matches[1]));
        }

        preg_match('/<meta.*property=\"og:image\".*content=\"(.+)\"/Uis', $content, $matches);
        if ($matches) {
            $meta->setOgImage(htmlspecialchars_decode($matches[1]));
        }

        return $meta;
    }
}

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

        preg_match('/<meta.*name=[\"\']{1}description[\"\']{1}.*content=[\"\']{1}(.+)[\"\']{1}/Uis', $content, $matches);
        if ($matches) {
            $meta->setDescription(htmlspecialchars_decode($matches[1]));
        }

        preg_match('/<meta.*name=[\"\']{1}keywords[\"\']{1}.*content=[\"\']{1}(.+)[\"\']{1}/Uis', $content, $matches);
        if ($matches) {
            $meta->setKeywords(htmlspecialchars_decode($matches[1]));
        }

        preg_match('/<meta.*name=[\"\']{1}author[\"\']{1}.*content=[\"\']{1}(.+)[\"\']{1}/Uis', $content, $matches);
        if ($matches) {
            $meta->addAuthor(new Author(null, htmlspecialchars_decode($matches[1])));
        }

        // maybe in future - optimalize to one preg_match for all og:*

        preg_match('/<meta.*property=[\"\']{1}og:title[\"\']{1}.*content=[\"\']{1}(.+)[\"\']{1}/Uis', $content, $matches);
        if ($matches) {
            $meta->setOgTitle(htmlspecialchars_decode($matches[1]));
        }

        preg_match('/<meta.*property=[\"\']{1}article:section[\"\']{1}.*content=[\"\']{1}(.+)[\"\']{1}/Uis', $content, $matches);
        if ($matches) {
            $meta->addSection(new Section(null, htmlspecialchars_decode($matches[1])));
        }

        preg_match('/<meta.*property=[\"\']{1}article:published_time[\"\']{1}.*content=[\"\']{1}(.+)[\"\']{1}/Uis', $content, $matches);
        if ($matches) {
            $meta->setPublishedTime(htmlspecialchars_decode($matches[1]));
        }

        preg_match('/<meta.*property=[\"\']{1}article:modified_time[\"\']{1}.*content=[\"\']{1}(.+)[\"\']{1}/Uis', $content, $matches);
        if ($matches) {
            $meta->setModifiedTime(htmlspecialchars_decode($matches[1]));
        }

        preg_match('/<meta.*property=[\"\']{1}og:description[\"\']{1}.*content=[\"\']{1}(.+)[\"\']{1}/Uis', $content, $matches);
        if ($matches) {
            $meta->setOgDescription(htmlspecialchars_decode($matches[1]));
        }

        preg_match('/<meta.*property=[\"\']{1}og:type[\"\']{1}.*content=[\"\']{1}(.+)[\"\']{1}/Uis', $content, $matches);
        if ($matches) {
            $meta->setOgType(htmlspecialchars_decode($matches[1]));
        }

        preg_match('/<meta.*property=[\"\']{1}og:url[\"\']{1}.*content=[\"\']{1}(.+)[\"\']{1}/Uis', $content, $matches);
        if ($matches) {
            $meta->setOgUrl(htmlspecialchars_decode($matches[1]));
        }

        preg_match('/<meta.*property=[\"\']{1}og:site_name[\"\']{1}.*content=[\"\']{1}(.+)[\"\']{1}/Uis', $content, $matches);
        if ($matches) {
            $meta->setOgSiteName(htmlspecialchars_decode($matches[1]));
        }

        preg_match('/<meta.*property=[\"\']{1}og:image[\"\']{1}.*content=[\"\']{1}(.+)[\"\']{1}/Uis', $content, $matches);
        if ($matches) {
            $meta->setOgImage(htmlspecialchars_decode($matches[1]));
        }

        return $meta;
    }
}

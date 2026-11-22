<?php

declare(strict_types=1);

namespace Tomaj\Scraper\Parser;

use Tomaj\Scraper\Author;
use Tomaj\Scraper\Meta;
use Tomaj\Scraper\Section;

final class OgParser implements ParserInterface
{
    #[\Override]
    public function parse(string $content): Meta
    {
        $meta = new Meta();

        $matches = [];

        if (preg_match('/<title.*>(.+)<\/title\>/Uis', $content, $matches) === 1) {
            $meta->setTitle(htmlspecialchars_decode($matches[1]));
        }

        if (preg_match('/<meta.*name=[\"\']{1}description[\"\']{1}.*content=[\"\']{1}(.+)[\"\']{1}/Uis', $content, $matches) === 1) {
            $meta->setDescription(htmlspecialchars_decode($matches[1]));
        }

        if (preg_match('/<meta.*name=[\"\']{1}keywords[\"\']{1}.*content=[\"\']{1}(.+)[\"\']{1}/Uis', $content, $matches) === 1) {
            $meta->setKeywords(htmlspecialchars_decode($matches[1]));
        }

        if (preg_match('/<meta.*name=[\"\']{1}author[\"\']{1}.*content=[\"\']{1}(.+)[\"\']{1}/Uis', $content, $matches) === 1) {
            $meta->addAuthor(new Author(null, htmlspecialchars_decode($matches[1])));
        }

        // maybe in future - optimalize to one preg_match for all og:*

        if (preg_match('/<meta.*property=[\"\']{1}og:title[\"\']{1}.*content=[\"\']{1}(.+)[\"\']{1}/Uis', $content, $matches) === 1) {
            $meta->setOgTitle(htmlspecialchars_decode($matches[1]));
        }

        if (preg_match('/<meta.*property=[\"\']{1}article:section[\"\']{1}.*content=[\"\']{1}(.+)[\"\']{1}/Uis', $content, $matches) === 1) {
            $meta->addSection(new Section(null, htmlspecialchars_decode($matches[1])));
        }

        if (preg_match('/<meta.*property=[\"\']{1}article:published_time[\"\']{1}.*content=[\"\']{1}(.+)[\"\']{1}/Uis', $content, $matches) === 1) {
            $meta->setPublishedTime(htmlspecialchars_decode($matches[1]));
        }

        if (preg_match('/<meta.*property=[\"\']{1}article:modified_time[\"\']{1}.*content=[\"\']{1}(.+)[\"\']{1}/Uis', $content, $matches) === 1) {
            $meta->setModifiedTime(htmlspecialchars_decode($matches[1]));
        }

        if (preg_match('/<meta.*property=[\"\']{1}og:description[\"\']{1}.*content=[\"\']{1}(.+)[\"\']{1}/Uis', $content, $matches) === 1) {
            $meta->setOgDescription(htmlspecialchars_decode($matches[1]));
        }

        if (preg_match('/<meta.*property=[\"\']{1}og:type[\"\']{1}.*content=[\"\']{1}(.+)[\"\']{1}/Uis', $content, $matches) === 1) {
            $meta->setOgType(htmlspecialchars_decode($matches[1]));
        }

        if (preg_match('/<meta.*property=[\"\']{1}og:url[\"\']{1}.*content=[\"\']{1}(.+)[\"\']{1}/Uis', $content, $matches) === 1) {
            $meta->setOgUrl(htmlspecialchars_decode($matches[1]));
        }

        if (preg_match('/<meta.*property=[\"\']{1}og:site_name[\"\']{1}.*content=[\"\']{1}(.+)[\"\']{1}/Uis', $content, $matches) === 1) {
            $meta->setOgSiteName(htmlspecialchars_decode($matches[1]));
        }

        if (preg_match('/<meta.*property=[\"\']{1}og:image[\"\']{1}.*content=[\"\']{1}(.+)[\"\']{1}/Uis', $content, $matches) === 1) {
            $meta->setOgImage(htmlspecialchars_decode($matches[1]));
        }

        return $meta;
    }
}

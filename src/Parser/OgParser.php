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

        if (!$content) {
            return $meta;
        }

        $patterns = [
            'title' => '/<title.*>(.+)<\/title\>/Uis',
            'description' => '/<meta.*name=[\"\']{1}description[\"\']{1}.*content=[\"\']{1}(.+)[\"\']{1}/Uis',
            'keywords' => '/<meta.*name=[\"\']{1}keywords[\"\']{1}.*content=[\"\']{1}(.+)[\"\']{1}/Uis',
            'author' => '/<meta.*name=[\"\']{1}author[\"\']{1}.*content=[\"\']{1}(.+)[\"\']{1}/Uis',
            'og_title' => '/<meta.*property=[\"\']{1}og:title[\"\']{1}.*content=[\"\']{1}(.+)[\"\']{1}/Uis',
            'article_section' => '/<meta.*property=[\"\']{1}article:section[\"\']{1}.*content=[\"\']{1}(.+)[\"\']{1}/Uis',
            'published_time' => '/<meta.*property=[\"\']{1}article:published_time[\"\']{1}.*content=[\"\']{1}(.+)[\"\']{1}/Uis',
            'modified_time' => '/<meta.*property=[\"\']{1}article:modified_time[\"\']{1}.*content=[\"\']{1}(.+)[\"\']{1}/Uis',
            'og_description' => '/<meta.*property=[\"\']{1}og:description[\"\']{1}.*content=[\"\']{1}(.+)[\"\']{1}/Uis',
            'og_type' => '/<meta.*property=[\"\']{1}og:type[\"\']{1}.*content=[\"\']{1}(.+)[\"\']{1}/Uis',
            'og_url' => '/<meta.*property=[\"\']{1}og:url[\"\']{1}.*content=[\"\']{1}(.+)[\"\']{1}/Uis',
            'og_site_name' => '/<meta.*property=[\"\']{1}og:site_name[\"\']{1}.*content=[\"\']{1}(.+)[\"\']{1}/Uis',
            'og_image' => '/<meta.*property=[\"\']{1}og:image[\"\']{1}.*content=[\"\']{1}(.+)[\"\']{1}/Uis'
        ];

        $matches = [];
        foreach ($patterns as $key => $pattern) {
            if (preg_match($pattern, $content, $match)) {
                $matches[$key] = $match[1];
            }
        }

        if (!empty($matches['title'])) {
            $meta->setTitle(htmlspecialchars_decode($matches['title'], ENT_NOQUOTES | ENT_HTML401));
        }
        if (!empty($matches['description'])) {
            $meta->setDescription(htmlspecialchars_decode($matches['description'], ENT_NOQUOTES | ENT_HTML401));
        }
        if (!empty($matches['keywords'])) {
            $meta->setKeywords(htmlspecialchars_decode($matches['keywords'], ENT_NOQUOTES | ENT_HTML401));
        }
        if (!empty($matches['author'])) {
            $meta->addAuthor(new Author(null, htmlspecialchars_decode($matches['author'], ENT_NOQUOTES | ENT_HTML401)));
        }
        if (!empty($matches['og_title'])) {
            $meta->setOgTitle(htmlspecialchars_decode($matches['og_title'], ENT_NOQUOTES | ENT_HTML401));
        }
        if (!empty($matches['article_section'])) {
            $meta->addSection(new Section(null, htmlspecialchars_decode($matches['article_section'], ENT_NOQUOTES | ENT_HTML401)));
        }
        if (!empty($matches['published_time'])) {
            $meta->setPublishedTime(htmlspecialchars_decode($matches['published_time'], ENT_NOQUOTES | ENT_HTML401));
        }
        if (!empty($matches['modified_time'])) {
            $meta->setModifiedTime(htmlspecialchars_decode($matches['modified_time'], ENT_NOQUOTES | ENT_HTML401));
        }
        if (!empty($matches['og_description'])) {
            $meta->setOgDescription(htmlspecialchars_decode($matches['og_description'], ENT_NOQUOTES | ENT_HTML401));
        }
        if (!empty($matches['og_type'])) {
            $meta->setOgType(htmlspecialchars_decode($matches['og_type'], ENT_NOQUOTES | ENT_HTML401));
        }
        if (!empty($matches['og_url'])) {
            $meta->setOgUrl(htmlspecialchars_decode($matches['og_url'], ENT_NOQUOTES | ENT_HTML401));
        }
        if (!empty($matches['og_site_name'])) {
            $meta->setOgSiteName(htmlspecialchars_decode($matches['og_site_name'], ENT_NOQUOTES | ENT_HTML401));
        }
        if (!empty($matches['og_image'])) {
            $meta->setOgImage(htmlspecialchars_decode($matches['og_image'], ENT_NOQUOTES | ENT_HTML401));
        }

        return $meta;
    }
}

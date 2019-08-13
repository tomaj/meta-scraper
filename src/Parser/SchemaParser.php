<?php
declare(strict_types=1);

namespace Tomaj\Scraper\Parser;

use Tomaj\Scraper\Meta;
use Tomaj\Scraper\Section;
use Tomaj\Scraper\Author;

class SchemaParser implements ParserInterface
{
    public function parse(string $content): Meta
    {
        $meta = new Meta();

        $matches = [];
        preg_match_all('/<script id="schema" type="application\/ld\+json">(.*?)<\/script>/', $content, $matches);

        if (!isset($matches[1][0])) {
            return $meta;
        }
        
        $schema = json_decode($matches[1][0], true);
        if (!$schema) {
            return $meta;
        }

        // author
        if (isset($schema['author']) && !is_array($schema['author'])) {
            $schema['author'] = [$schema['author']];
        }
        foreach ($schema['author'] ?? [] as $author) {
            $meta->addAuthor(new Author($author['@id'] ?? null, $author['name'] ?? null));
        }

        // section
        if (isset($schema['articleTerms']) && is_array($schema['articleTerms'])) {
            foreach ($schema['articleTerms'] as $articleTerm) {
                if ($articleTerm['@type'] == 'Category') {
                    if (is_int($articleTerm['@id'])) {
                        $articleTerm['@id'] = (string) $articleTerm['@id'];
                    }
                    $meta->addSection(new Section($articleTerm['@id'] ?? null, $articleTerm['name'] ?? null));
                }
            }
        } else {
            if (isset($schema['articleSection']) && !is_array($schema['articleSection'])) {
                $schema['articleSection'] = [$schema['articleSection']];
            }
            foreach ($schema['articleSection'] ?? [] as $section) {
                $meta->addSection(new Section(null, $section));
            }
        }

        if (isset($schema['headline'])) {
            $meta->setTitle($schema['headline']);
        }

        if (isset($schema['description'])) {
            $meta->setDescription($schema['description']);
        }

        if (isset($schema['image']['url'])) {
            $meta->setOgImage($schema['image']['url']);
        }

        if (isset($schema['url'])) {
            $meta->setOgUrl($schema['url']);
        }

        if (isset($schema['datePublished'])) {
            $meta->setPublishedTime($schema['datePublished']);
        }

        if (isset($schema['dateModified'])) {
            $meta->setModifiedTime($schema['dateModified']);
        }

        return $meta;
    }
}

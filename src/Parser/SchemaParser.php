<?php
declare(strict_types=1);

namespace Tomaj\Scraper\Parser;

use Tomaj\Scraper\Meta;

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
            $meta->addAuthor([
                'id' => $author['@id'] ?? null,
                'name' => $author['name'] ?? null,
            ]);
        }

        // section
        if (isset($schema['articleSection']) && !is_array($schema['articleSection'])) {
            $schema['articleSection'] = [$schema['articleSection']];
        }
        foreach ($schema['articleSection'] ?? [] as $section) {
            $meta->addSection($section);
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

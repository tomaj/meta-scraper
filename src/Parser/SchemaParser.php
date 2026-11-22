<?php

declare(strict_types=1);

namespace Tomaj\Scraper\Parser;

use Tomaj\Scraper\Author;
use Tomaj\Scraper\Meta;
use Tomaj\Scraper\Section;
use Tomaj\Scraper\Tag;

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
        if (!is_array($schema) || $schema === []) {
            return $meta;
        }

        // author
        foreach (self::listOf($schema['author'] ?? null) as $author) {
            if (is_array($author)) {
                $meta->addAuthor(new Author(self::stringOrNull($author['@id'] ?? null), self::stringOrNull($author['name'] ?? null)));
            } else {
                $meta->addAuthor(new Author(null, self::stringOrNull($author)));
            }
        }

        // section
        if (isset($schema['articleTerms']) && is_array($schema['articleTerms'])) {
            foreach ($schema['articleTerms'] as $articleTerm) {
                if (is_array($articleTerm) && ($articleTerm['@type'] ?? null) === 'Category') {
                    $meta->addSection(new Section(self::stringOrNull($articleTerm['@id'] ?? null), self::stringOrNull($articleTerm['name'] ?? null)));
                }
            }
        } else {
            foreach (self::listOf($schema['articleSection'] ?? null) as $section) {
                $meta->addSection(new Section(null, self::stringOrNull($section)));
            }
        }

        // tags
        foreach (self::listOf($schema['about'] ?? null) as $about) {
            if (is_array($about)) {
                $meta->addTag(new Tag(self::stringOrNull($about['@id'] ?? null), self::stringOrNull($about['name'] ?? null)));
            }
        }

        $headline = self::stringOrNull($schema['headline'] ?? null);
        if ($headline !== null) {
            $meta->setTitle($headline);
        }

        $description = self::stringOrNull($schema['description'] ?? null);
        if ($description !== null) {
            $meta->setDescription($description);
        }

        $image = $schema['image'] ?? null;
        $imageUrl = is_array($image) ? self::stringOrNull($image['url'] ?? null) : null;
        if ($imageUrl !== null) {
            $meta->setOgImage($imageUrl);
        }

        $url = self::stringOrNull($schema['url'] ?? null);
        if ($url !== null) {
            $meta->setOgUrl($url);
        }

        $datePublished = self::stringOrNull($schema['datePublished'] ?? null);
        if ($datePublished !== null) {
            $meta->setPublishedTime($datePublished);
        }

        $dateModified = self::stringOrNull($schema['dateModified'] ?? null);
        if ($dateModified !== null) {
            $meta->setModifiedTime($dateModified);
        }

        return $meta;
    }

    /**
     * Schema.org values may be a single item or a list of items.
     *
     * @return list<mixed>
     */
    private static function listOf(mixed $value): array
    {
        if ($value === null) {
            return [];
        }
        if (is_array($value) && array_is_list($value)) {
            return $value;
        }

        return [$value];
    }

    private static function stringOrNull(mixed $value): ?string
    {
        if (is_string($value)) {
            return $value;
        }
        if (is_int($value)) {
            return (string) $value;
        }

        return null;
    }
}

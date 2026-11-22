<?php

declare(strict_types=1);

namespace Tomaj\Scraper\Parser;

use Tomaj\Scraper\Author;
use Tomaj\Scraper\Meta;
use Tomaj\Scraper\Section;
use Tomaj\Scraper\Tag;

class SchemaParser implements ParserInterface
{
    #[\Override]
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
        foreach ($this->listOf($schema['author'] ?? null) as $author) {
            if (is_array($author)) {
                $meta->addAuthor(new Author($this->stringOrNull($author['@id'] ?? null), $this->stringOrNull($author['name'] ?? null)));
            } else {
                $meta->addAuthor(new Author(null, $this->stringOrNull($author)));
            }
        }

        // section
        if (isset($schema['articleTerms']) && is_array($schema['articleTerms'])) {
            foreach ($schema['articleTerms'] as $articleTerm) {
                if (is_array($articleTerm) && ($articleTerm['@type'] ?? null) === 'Category') {
                    $meta->addSection(new Section($this->stringOrNull($articleTerm['@id'] ?? null), $this->stringOrNull($articleTerm['name'] ?? null)));
                }
            }
        } else {
            foreach ($this->listOf($schema['articleSection'] ?? null) as $section) {
                $meta->addSection(new Section(null, $this->stringOrNull($section)));
            }
        }

        // tags
        foreach ($this->listOf($schema['about'] ?? null) as $about) {
            if (is_array($about)) {
                $meta->addTag(new Tag($this->stringOrNull($about['@id'] ?? null), $this->stringOrNull($about['name'] ?? null)));
            }
        }

        $headline = $this->stringOrNull($schema['headline'] ?? null);
        if ($headline !== null) {
            $meta->setTitle($headline);
        }

        $description = $this->stringOrNull($schema['description'] ?? null);
        if ($description !== null) {
            $meta->setDescription($description);
        }

        $image = $schema['image'] ?? null;
        $imageUrl = is_array($image) ? $this->stringOrNull($image['url'] ?? null) : null;
        if ($imageUrl !== null) {
            $meta->setOgImage($imageUrl);
        }

        $url = $this->stringOrNull($schema['url'] ?? null);
        if ($url !== null) {
            $meta->setOgUrl($url);
        }

        $datePublished = $this->stringOrNull($schema['datePublished'] ?? null);
        if ($datePublished !== null) {
            $meta->setPublishedTime($datePublished);
        }

        $dateModified = $this->stringOrNull($schema['dateModified'] ?? null);
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
    private function listOf(mixed $value): array
    {
        if ($value === null) {
            return [];
        }
        if (is_array($value) && array_is_list($value)) {
            return $value;
        }

        return [$value];
    }

    private function stringOrNull(mixed $value): ?string
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

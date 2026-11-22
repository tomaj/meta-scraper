<?php

declare(strict_types=1);

namespace Tomaj\Scraper\Parser;

use Tomaj\Scraper\Author;
use Tomaj\Scraper\Meta;
use Tomaj\Scraper\Section;
use Tomaj\Scraper\Tag;

/**
 * Reads schema.org JSON-LD: every <script type="application/ld+json"> block, including @graph arrays.
 *
 * The first block marked id="schema" wins (the markup this parser originally supported). Otherwise the
 * first article-like object (Article, NewsArticle, BlogPosting, ...) is used, then the first web page
 * object (WebPage, CollectionPage, ...). Other objects (Organization, WebSite, ImageObject, ...) are ignored,
 * because their url, description or image do not describe the page.
 */
final class SchemaParser implements ParserInterface
{
    private const array ARTICLE_TYPES = [
        'Article', 'AdvertiserContentArticle', 'AnalysisNewsArticle', 'AskPublicNewsArticle',
        'BackgroundNewsArticle', 'BlogPosting', 'DiscussionForumPosting', 'LiveBlogPosting',
        'NewsArticle', 'OpinionNewsArticle', 'Report', 'ReportageNewsArticle', 'ReviewNewsArticle',
        'SatiricalArticle', 'ScholarlyArticle', 'SocialMediaPosting', 'TechArticle',
    ];

    private const array WEB_PAGE_TYPES = [
        'WebPage', 'AboutPage', 'CheckoutPage', 'CollectionPage', 'ContactPage', 'FAQPage',
        'ItemPage', 'MedicalWebPage', 'ProfilePage', 'QAPage', 'RealEstateListing', 'SearchResultsPage',
    ];

    #[\Override]
    public function parse(string $content): Meta
    {
        $meta = new Meta();

        $found = $this->findSchema($content);
        if ($found === null) {
            return $meta;
        }
        [$schema, $nodes] = $found;

        // author
        foreach ($this->listOf($schema['author'] ?? null) as $author) {
            $author = $this->resolve($author, $nodes);
            if (is_array($author)) {
                $meta->addAuthor(new Author($this->stringOrNull($author['@id'] ?? null), $this->stringOrNull($author['name'] ?? null)));
            } else {
                $meta->addAuthor(new Author(null, $this->stringOrNull($author)));
            }
        }

        // section
        if (isset($schema['articleTerms']) && is_array($schema['articleTerms'])) {
            foreach ($schema['articleTerms'] as $articleTerm) {
                $articleTerm = $this->resolve($articleTerm, $nodes);
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
            $about = $this->resolve($about, $nodes);
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

        $image = $this->resolve($schema['image'] ?? null, $nodes);
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
     * @return array{array<mixed>, array<string, array<mixed>>}|null the schema object and every object by its "@id"
     */
    private function findSchema(string $content): ?array
    {
        preg_match_all('/<script\b([^>]*)>(.*?)<\/script\s*>/is', $content, $scripts, PREG_SET_ORDER);

        $legacy = null;
        $objects = [];
        foreach ($scripts as [, $attributes, $json]) {
            if (preg_match('/\btype\s*=\s*["\']?\s*application\/ld\+json/i', $attributes) !== 1) {
                continue;
            }
            $decoded = json_decode($json, true);
            if (!is_array($decoded) || $decoded === []) {
                continue;
            }
            if ($legacy === null && preg_match('/\bid\s*=\s*(?:"schema"|\'schema\'|schema(?=\s|$))/i', $attributes) === 1) {
                $legacy = $decoded;
            }
            array_push($objects, ...$this->flatten($decoded));
        }

        // the first node with an "@id" wins, like the first matching block
        $nodes = [];
        foreach ($objects as $object) {
            $id = $object['@id'] ?? null;
            if (is_string($id) && !isset($nodes[$id])) {
                $nodes[$id] = $object;
            }
        }

        if ($legacy !== null) {
            return [$legacy, $nodes];
        }

        foreach ([self::ARTICLE_TYPES, self::WEB_PAGE_TYPES] as $types) {
            foreach ($objects as $object) {
                if (array_intersect($this->typesOf($object), $types) !== []) {
                    return [$object, $nodes];
                }
            }
        }

        return null;
    }

    /**
     * Replaces a node reference such as {"@id": "https://example.com/#author"} with the node it points to.
     *
     * @param array<string, array<mixed>> $nodes
     */
    private function resolve(mixed $value, array $nodes): mixed
    {
        if (!is_array($value) || count($value) !== 1 || !is_string($value['@id'] ?? null)) {
            return $value;
        }

        return $nodes[$value['@id']] ?? $value;
    }

    /**
     * A JSON-LD block holds one object, a list of objects, or an object with a "@graph" list.
     *
     * @param array<mixed> $decoded
     * @return list<array<mixed>>
     */
    private function flatten(array $decoded): array
    {
        $items = array_is_list($decoded) ? $decoded : [$decoded];
        $objects = [];
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            $graph = $item['@graph'] ?? null;
            if (is_array($graph)) {
                array_push($objects, ...$this->flatten($graph));
                continue;
            }
            $objects[] = $item;
        }

        return $objects;
    }

    /**
     * @param array<mixed> $object
     * @return array<string>
     */
    private function typesOf(array $object): array
    {
        return array_filter($this->listOf($object['@type'] ?? null), is_string(...));
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

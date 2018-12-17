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

        if (!$matches) {
            return $meta;
        }
        
        $schema = json_decode($matches[1][0]);
        if (!$schema) {
            return $meta;
        }

        // author
        $denniknAuthors = false;
        if (isset($schema->author) && !is_array($schema->author)) {
            $schema->author = [$schema->author];
        }
        foreach ($schema->author as $author) {
            if (isset($author->name)) {
                $meta->addAuthor($author->name);
            } else {
                $meta->addAuthor($author);
            }
        }

        if (isset($schema->headline)) {
            $meta->setTitle($schema->headline);
        }

        if (isset($schema->description)) {
            $meta->setDescription($schema->description);
        }

        if (isset($schema->image->url)) {
            $meta->setOgImage($schema->image->url);
        }

        if (isset($schema->url)) {
            $meta->setOgUrl($schema->url);
        }

        if (isset($schema->articleSection)) {
            $meta->setSection($schema->articleSection);
        }

        if (isset($schema->datePublished)) {
            $meta->setPublishedTime($schema->datePublished);
        }

        if (isset($schema->dateModified)) {
            $meta->setModifiedTime($schema->dateModified);
        }

        return $meta;
    }
}

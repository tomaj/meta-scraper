<?php

declare(strict_types=1);

// Reads the schema.org JSON-LD of a WordPress/Yoast style article (a @graph with @id references)
// and shows how the order of the parsers decides which value wins.

require __DIR__ . '/vendor/autoload.php';

use Tomaj\Scraper\Meta;
use Tomaj\Scraper\Parser\OgDomParser;
use Tomaj\Scraper\Parser\SchemaParser;
use Tomaj\Scraper\Scraper;

$base = $argv[1] ?? 'http://127.0.0.1:8402';

$scraper = new Scraper(['timeout' => 5]);

// 1. JSON-LD only. SchemaParser picks the NewsArticle node (not the Organization, WebSite,
//    ImageObject or WebPage nodes) and resolves the {"@id": ...} references of author and image.
$meta = $scraper->parseUrl($base . '/news.html', [new SchemaParser()]);

echo "== SchemaParser\n";
printf("%-12s %s\n", 'title', $meta->getTitle());
printf("%-12s %s\n", 'description', $meta->getDescription());
printf("%-12s %s\n", 'url', $meta->getOgUrl());
printf("%-12s %s\n", 'image', $meta->getOgImage());
printf("%-12s %s\n", 'published', $meta->getPublishedTime()?->format(DATE_ATOM));
printf("%-12s %s\n", 'modified', $meta->getModifiedTime()?->format(DATE_ATOM));
foreach ($meta->getAuthors() as $author) {
    printf("%-12s %s (%s)\n", 'author', $author->name(), $author->id() ?? 'no @id');
}
foreach ($meta->getSections() as $section) {
    printf("%-12s %s\n", 'section', $section->name());
}
foreach ($meta->getTags() as $tag) {
    printf("%-12s %s (%s)\n", 'tag', $tag->name(), $tag->id());
}
// Open Graph fields that JSON-LD has no equivalent for stay empty
printf("%-12s %s\n", 'og:title', $meta->getOgTitle() ?? '(null)');
printf("%-12s %s\n", 'site name', $meta->getOgSiteName() ?? '(null)');

// 2. Both parsers. The first one that finds a value wins, the second fills the gaps.
$html = $scraper->getBody();
$fields = static fn(Meta $meta): array => [
    'title' => $meta->getTitle(),
    'description' => $meta->getDescription(),
    'og:title' => $meta->getOgTitle(),
    'og:image' => $meta->getOgImage(),
    'site name' => $meta->getOgSiteName(),
    'authors' => implode(', ', array_map(static fn($author): ?string => $author->name(), $meta->getAuthors())),
];

foreach (['SchemaParser, OgDomParser' => [new SchemaParser(), new OgDomParser()], 'OgDomParser, SchemaParser' => [new OgDomParser(), new SchemaParser()]] as $order => $parsers) {
    echo "\n== {$order}\n";
    foreach ($fields($scraper->parse($html, $parsers)) as $name => $value) {
        printf("%-12s %s\n", $name, $value);
    }
}

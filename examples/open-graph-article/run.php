<?php

declare(strict_types=1);

// Downloads a news article and prints every field the meta tag parsers read from it.
// The page is a fixture (site/article.html) served by `php -S`, see e2e.sh.

require __DIR__ . '/vendor/autoload.php';

use Tomaj\Scraper\Meta;
use Tomaj\Scraper\Parser\OgDomParser;
use Tomaj\Scraper\Parser\OgParser;
use Tomaj\Scraper\Scraper;

$base = $argv[1] ?? 'http://127.0.0.1:8401';

$scraper = new Scraper(['timeout' => 5]);
$meta = $scraper->parseUrl($base . '/article.html', [new OgDomParser()]);

function show(Meta $meta): void
{
    $names = static fn(array $items): string => implode(', ', array_map(static fn($item): ?string => $item->name(), $items));

    printf("%-15s %s\n", 'title', $meta->getTitle());
    printf("%-15s %s\n", 'description', $meta->getDescription());
    printf("%-15s %s\n", 'keywords', $meta->getKeywords());
    printf("%-15s %s\n", 'authors', $names($meta->getAuthors()));
    printf("%-15s %s\n", 'og:type', $meta->getOgType());
    printf("%-15s %s\n", 'og:title', $meta->getOgTitle());
    printf("%-15s %s\n", 'og:description', $meta->getOgDescription());
    printf("%-15s %s\n", 'og:url', $meta->getOgUrl());
    printf("%-15s %s\n", 'og:site_name', $meta->getOgSiteName());
    printf("%-15s %s\n", 'og:image', $meta->getOgImage());
    printf("%-15s %s\n", 'sections', $names($meta->getSections()));
    // times are DateTimeImmutable objects with the offset the page wrote
    printf("%-15s %s\n", 'published', $meta->getPublishedTime()?->format(DATE_ATOM));
    printf("%-15s %s\n", 'modified', $meta->getModifiedTime()?->format(DATE_ATOM));
}

echo "== OgDomParser\n";
show($meta);

// The regular expression parser reads the same tags without ext-dom, but only the first
// occurrence of each one: the second article:section is missing.
echo "\n== OgParser (the same HTML, from getBody())\n";
show($scraper->parse($scraper->getBody(), [new OgParser()]));

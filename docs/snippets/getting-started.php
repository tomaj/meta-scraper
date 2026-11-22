<?php

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use Tomaj\Scraper\Parser\OgParser;
use Tomaj\Scraper\Parser\SchemaParser;
use Tomaj\Scraper\Scraper;

$html = <<<'HTML'
    <html>
    <head>
        <title>Ten tips for PHP 8.4 | Example Blog</title>
        <meta name="description" content="What changed in PHP 8.4 and how to use it.">
        <meta property="og:title" content="Ten tips for PHP 8.4">
        <meta property="og:image" content="https://blog.example/php84.jpg">
        <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "BlogPosting",
            "headline": "Ten tips for PHP 8.4",
            "author": {"@type": "Person", "name": "Jana Nováková"},
            "datePublished": "2024-11-21T08:00:00+00:00"
        }
        </script>
    </head>
    </html>
    HTML;

$meta = new Scraper()->parse($html, [new SchemaParser(), new OgParser()]);

echo $meta->getTitle(), "\n";
echo $meta->getDescription(), "\n";
echo $meta->getOgImage(), "\n";
foreach ($meta->getAuthors() as $author) {
    echo $author->name(), "\n";
}
echo $meta->getPublishedTime()?->format('Y-m-d'), "\n";

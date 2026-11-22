<?php

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use Tomaj\Scraper\Parser\OgDomParser;
use Tomaj\Scraper\Parser\SchemaParser;
use Tomaj\Scraper\Scraper;

$html = '<title>Page title | Site</title>'
    . '<meta property="og:title" content="Open Graph title">'
    . '<meta property="og:site_name" content="Site">'
    . '<script type="application/ld+json">{"@type":"NewsArticle","headline":"Schema headline"}</script>';

$scraper = new Scraper();

// SchemaParser first: its headline wins, OgDomParser fills what is still missing
$meta = $scraper->parse($html, [new SchemaParser(), new OgDomParser()]);
echo $meta->getTitle(), ' / ', $meta->getOgSiteName(), "\n";

// OgDomParser first: the <title> wins
$meta = $scraper->parse($html, [new OgDomParser(), new SchemaParser()]);
echo $meta->getTitle(), ' / ', $meta->getOgSiteName(), "\n";

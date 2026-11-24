<?php

declare(strict_types=1);

// A page that has only Twitter card tags, and one that has both Open Graph and a card.
// The custom TwitterCardParser (src/) fills the Open Graph fields only when they are missing.

require __DIR__ . '/vendor/autoload.php';

use App\TwitterCardParser;
use Tomaj\Scraper\Meta;
use Tomaj\Scraper\Parser\OgDomParser;
use Tomaj\Scraper\Scraper;

$base = $argv[1] ?? 'http://127.0.0.1:8403';

$scraper = new Scraper(['timeout' => 5]);
$show = static function (string $label, Meta $meta): void {
    echo "== {$label}\n";
    printf("%-15s %s\n", 'title', $meta->getTitle() ?? '(null)');
    printf("%-15s %s\n", 'og:title', $meta->getOgTitle() ?? '(null)');
    printf("%-15s %s\n", 'og:description', $meta->getOgDescription() ?? '(null)');
    printf("%-15s %s\n", 'og:image', $meta->getOgImage() ?? '(null)');
    echo "\n";
};

// the built-in parsers do not read twitter:* tags
$show('card-only.html, OgDomParser', $scraper->parseUrl($base . '/card-only.html', [new OgDomParser()]));

// the card fills the gaps
$parsers = [new OgDomParser(), new TwitterCardParser()];
$show('card-only.html, OgDomParser + TwitterCardParser', $scraper->parseUrl($base . '/card-only.html', $parsers));

// Open Graph wins where the page has it; the card adds only the description
$show('both.html, OgDomParser + TwitterCardParser', $scraper->parseUrl($base . '/both.html', $parsers));

<?php

declare(strict_types=1);

// Pages that are not well-formed, not UTF-8 or not there at all. The parsers never throw on
// bad input: they return what they could read, and a field they could not read is null.

require __DIR__ . '/vendor/autoload.php';

use Tomaj\Scraper\Meta;
use Tomaj\Scraper\Parser\OgDomParser;
use Tomaj\Scraper\Parser\OgParser;
use Tomaj\Scraper\Parser\SchemaParser;
use Tomaj\Scraper\Scraper;

$base = $argv[1] ?? 'http://127.0.0.1:8404';

$scraper = new Scraper(['timeout' => 5]);
$show = static function (string $label, Meta $meta, array $fields): void {
    echo "{$label}\n";
    foreach ($fields as $field) {
        $value = match ($field) {
            'authors' => implode(', ', array_map(static fn($author): ?string => $author->name(), $meta->getAuthors())),
            'published' => $meta->getPublishedTime()?->format(DATE_ATOM),
            default => $meta->{'get' . $field}(),
        };
        printf("  %-13s %s\n", $field, $value ?? '(null)');
    }
};

echo "== reversed.html: content before property, an empty content\n";
$scraper->parseUrl($base . '/reversed.html', []);
$fields = ['title', 'ogTitle', 'ogUrl', 'ogSiteName', 'ogDescription', 'description'];
$show('OgParser', $scraper->parse($scraper->getBody(), [new OgParser()]), $fields);
$show('OgDomParser', $scraper->parse($scraper->getBody(), [new OgDomParser()]), $fields);

echo "\n== unquoted.html: upper case tags, unquoted attributes, no closing tags\n";
$scraper->parseUrl($base . '/unquoted.html', []);
$fields = ['title', 'description', 'ogType'];
$show('OgParser', $scraper->parse($scraper->getBody(), [new OgParser()]), $fields);
$show('OgDomParser', $scraper->parse($scraper->getBody(), [new OgDomParser()]), $fields);

echo "\n== invalid-json-ld.html: the broken block is skipped\n";
$show('SchemaParser', $scraper->parseUrl($base . '/invalid-json-ld.html', [new SchemaParser()]), ['title', 'authors', 'published']);

echo "\n== no-charset.html: UTF-8 without a charset declaration\n";
$scraper->parseUrl($base . '/no-charset.html', []);
$show('OgParser', $scraper->parse($scraper->getBody(), [new OgParser()]), ['title']);
$show('OgDomParser', $scraper->parse($scraper->getBody(), [new OgDomParser()]), ['title']);

echo "\n== windows-1250.html: a declared legacy charset\n";
$scraper->parseUrl($base . '/windows-1250.html', []);
$html = $scraper->getBody();
// OgParser returns the bytes as they are: not valid UTF-8
$title = $scraper->parse($html, [new OgParser()])->getTitle() ?? '';
printf("OgParser\n  %-13s %s\n", 'valid UTF-8', preg_match('//u', $title) === 1 ? 'yes' : 'no');
// OgDomParser decodes the declared charset
$show('OgDomParser', $scraper->parse($html, [new OgDomParser()]), ['title', 'description']);
// convert the body yourself for OgParser and SchemaParser (mbstring has no Windows-1250, iconv has)
$utf8 = iconv('Windows-1250', 'UTF-8', $html);
$show('OgParser after iconv()', $scraper->parse((string) $utf8, [new OgParser()]), ['title', 'description']);

echo "\n== empty.html: an empty body\n";
$meta = $scraper->parseUrl($base . '/empty.html', [new SchemaParser(), new OgDomParser(), new OgParser()]);
$show('all three parsers', $meta, ['title', 'description', 'ogImage', 'authors']);

<?php

declare(strict_types=1);

namespace App;

use Tomaj\Scraper\Meta;
use Tomaj\Scraper\Parser\ParserInterface;

/**
 * Reads Twitter (X) card tags into the Open Graph fields of Meta.
 *
 * The built-in parsers do not read twitter:* tags. Put this parser after them, so the card is
 * only a fallback for pages without Open Graph:
 *
 *     $scraper->parse($html, [new OgDomParser(), new TwitterCardParser()]);
 */
final class TwitterCardParser implements ParserInterface
{
    #[\Override]
    public function parse(string $content): Meta
    {
        // a new Meta from every call: one parser instance is used for many pages
        $meta = new Meta();
        if ($content === '') {
            return $meta;
        }

        // the HTML5 parser of PHP 8.4: UTF-8 unless the page declares another charset,
        // LIBXML_NOERROR keeps parse errors of real-world markup from becoming warnings
        $dom = \Dom\HTMLDocument::createFromString($content, LIBXML_NOERROR);

        foreach ($dom->querySelectorAll('meta[name], meta[property]') as $tag) {
            // cards are written with name="twitter:…", some sites use property="twitter:…"
            $name = strtolower(($tag->getAttribute('name') ?: $tag->getAttribute('property')) ?? '');
            $value = $tag->getAttribute('content') ?? '';
            if ($value === '') {
                continue;
            }
            match ($name) {
                'twitter:title' => $meta->setOgTitle($value),
                'twitter:description' => $meta->setOgDescription($value),
                'twitter:image', 'twitter:image:src' => $meta->setOgImage($value),
                default => null,
            };
        }

        return $meta;
    }
}

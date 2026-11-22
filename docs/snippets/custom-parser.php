<?php

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use Tomaj\Scraper\Meta;
use Tomaj\Scraper\Parser\OgParser;
use Tomaj\Scraper\Parser\ParserInterface;
use Tomaj\Scraper\Scraper;

/**
 * Reads the Twitter card tags as a fallback for pages without Open Graph.
 */
final class TwitterCardParser implements ParserInterface
{
    #[\Override]
    public function parse(string $content): Meta
    {
        $meta = new Meta();
        $fields = [
            'twitter:title' => $meta->setOgTitle(...),
            'twitter:description' => $meta->setOgDescription(...),
            'twitter:image' => $meta->setOgImage(...),
        ];
        foreach ($fields as $name => $setter) {
            $pattern = '/<meta\s+name="' . preg_quote($name, '/') . '"\s+content="([^"]*)"/i';
            if (preg_match($pattern, $content, $matches) === 1) {
                $setter(html_entity_decode($matches[1], ENT_QUOTES | ENT_HTML5));
            }
        }

        return $meta;
    }
}

$html = '<meta name="twitter:title" content="Only a Twitter card">'
    . '<meta name="twitter:image" content="https://example.com/card.png">';

$meta = new Scraper()->parse($html, [new OgParser(), new TwitterCardParser()]);

echo $meta->getOgTitle(), "\n";
echo $meta->getOgImage(), "\n";

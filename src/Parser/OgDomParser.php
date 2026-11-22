<?php

declare(strict_types=1);

namespace Tomaj\Scraper\Parser;

use Tomaj\Scraper\Exception\MissingExtensionException;
use Tomaj\Scraper\Meta;

/**
 * Reads the same tags as OgParser, with the DOM extension instead of regular expressions.
 */
final class OgDomParser implements ParserInterface
{
    private const string ATTRIBUTE_NAME = 'name';
    private const string ATTRIBUTE_PROPERTY = 'property';
    private const string ATTRIBUTE_CONTENT = 'content';

    /**
     * Allowed attribute values per attribute name, mapped to the Meta setter that receives the content.
     */
    private const array ALLOWED_ATTRIBUTES = [
        self::ATTRIBUTE_NAME => [
            'description' => 'setDescription',
            'keywords' => 'setKeywords',
            'author' => 'addAuthorByName',
        ],
        self::ATTRIBUTE_PROPERTY => [
            'og:title' => 'setOgTitle',
            'article:section' => 'addSectionByName',
            'article:published_time' => 'setPublishedTime',
            'article:modified_time' => 'setModifiedTime',
            'og:description' => 'setOgDescription',
            'og:type' => 'setOgType',
            'og:url' => 'setOgUrl',
            'og:site_name' => 'setOgSiteName',
            'og:image' => 'setOgImage',
        ],
    ];

    /**
     * @throws MissingExtensionException when ext-dom or ext-libxml is not loaded
     */
    public function __construct()
    {
        foreach (['dom', 'libxml'] as $extension) {
            if (!extension_loaded($extension)) {
                throw new MissingExtensionException(self::class, $extension);
            }
        }
    }

    #[\Override]
    public function parse(string $content): Meta
    {
        $meta = new Meta();
        if ($content === '') {
            return $meta;
        }

        $dom = new \DOMDocument();
        // ignore warnings like "DOMDocument::loadHTML(): Unexpected end tag : head in Entity"
        $previous = libxml_use_internal_errors(true);
        $dom->loadHTML($this->withEncoding($content));
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        foreach ($dom->getElementsByTagName('title') as $titleTag) {
            $meta->setTitle(htmlspecialchars_decode($titleTag->nodeValue ?? ''));
            // only the first title tag
            break;
        }

        foreach ($dom->getElementsByTagName('meta') as $metaTag) {
            $this->processMetaTag($meta, $metaTag, self::ATTRIBUTE_NAME);
            $this->processMetaTag($meta, $metaTag, self::ATTRIBUTE_PROPERTY);
        }

        return $meta;
    }

    /**
     * libxml reads HTML as ISO-8859-1 unless the document declares its charset, which garbles UTF-8 text.
     * Documents without a declaration are read as UTF-8; a declared charset is left to libxml.
     *
     * @param non-empty-string $content
     * @return non-empty-string
     */
    private function withEncoding(string $content): string
    {
        if (preg_match('/<meta\b[^>]*\bcharset\s*=|<\?xml\b[^>]*\bencoding\s*=/i', $content) === 1) {
            return $content;
        }

        return '<?xml encoding="UTF-8">' . $content;
    }

    /**
     * @param self::ATTRIBUTE_NAME|self::ATTRIBUTE_PROPERTY $attributeName
     */
    private function processMetaTag(Meta $meta, \DOMElement $metaTag, string $attributeName): void
    {
        if (!$metaTag->hasAttribute($attributeName) || !$metaTag->hasAttribute(self::ATTRIBUTE_CONTENT)) {
            return;
        }

        // attribute values are matched case-insensitively ("OG:TITLE" is og:title)
        $attributeValue = strtolower($metaTag->getAttribute($attributeName));
        $setter = self::ALLOWED_ATTRIBUTES[$attributeName][$attributeValue] ?? null;
        if ($setter === null) {
            return;
        }

        $value = htmlspecialchars_decode($metaTag->getAttribute(self::ATTRIBUTE_CONTENT));
        match ($setter) {
            'setDescription' => $meta->setDescription($value),
            'setKeywords' => $meta->setKeywords($value),
            'addAuthorByName' => $meta->addAuthorByName($value),
            'setOgTitle' => $meta->setOgTitle($value),
            'addSectionByName' => $meta->addSectionByName($value),
            'setPublishedTime' => $meta->setPublishedTime($value),
            'setModifiedTime' => $meta->setModifiedTime($value),
            'setOgDescription' => $meta->setOgDescription($value),
            'setOgType' => $meta->setOgType($value),
            'setOgUrl' => $meta->setOgUrl($value),
            'setOgSiteName' => $meta->setOgSiteName($value),
            'setOgImage' => $meta->setOgImage($value),
        };
    }
}

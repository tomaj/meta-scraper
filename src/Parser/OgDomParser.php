<?php

declare(strict_types=1);

namespace Tomaj\Scraper\Parser;

use Dom\Element;
use Dom\HTMLDocument;
use Tomaj\Scraper\Exception\MissingExtensionException;
use Tomaj\Scraper\Meta;

/**
 * Reads the same tags as OgParser, with the HTML5 parser of PHP 8.4 (Dom\HTMLDocument) instead of regular expressions.
 */
final class OgDomParser implements ParserInterface
{
    private const string ATTRIBUTE_NAME = 'name';
    private const string ATTRIBUTE_PROPERTY = 'property';
    private const string ATTRIBUTE_CONTENT = 'content';

    private const string HTML_NAMESPACE = 'http://www.w3.org/1999/xhtml';

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

        $document = $this->createDocument($content);

        // the first HTML <title> (not an SVG <title>); text and attribute values are already entity-decoded
        $title = $document->getElementsByTagNameNS(self::HTML_NAMESPACE, 'title')->item(0);
        if ($title instanceof Element) {
            $meta->setTitle($title->textContent ?? '');
        }

        foreach ($document->querySelectorAll('meta[name], meta[property]') as $metaTag) {
            $this->processMetaTag($meta, $metaTag, self::ATTRIBUTE_NAME);
            $this->processMetaTag($meta, $metaTag, self::ATTRIBUTE_PROPERTY);
        }

        return $meta;
    }

    /**
     * HTML5 parsing: the charset comes from a byte order mark or a <meta charset> / http-equiv declaration in
     * the first 1024 bytes, otherwise it is UTF-8. LIBXML_NOERROR keeps the parse errors of real-world markup
     * from becoming PHP warnings.
     */
    private function createDocument(string $content): HTMLDocument
    {
        $charset = $this->declaredCharset($content);
        if ($charset !== null) {
            try {
                return HTMLDocument::createFromString($content, LIBXML_NOERROR, $charset);
            } catch (\ValueError) {
                // not a charset label PHP knows: parse as if there was no declaration
            }
        }

        return HTMLDocument::createFromString($content, LIBXML_NOERROR);
    }

    /**
     * The HTML5 parser only looks for a declaration in the first 1024 bytes (a browser reloads the page when it
     * finds one later) and ignores an XML declaration. For a page that is not valid UTF-8, the first <meta>
     * charset declaration anywhere in the page is used instead of decoding it as UTF-8, or, without one, the
     * encoding of an XML declaration (a browser ignores it, so a <meta> declaration wins).
     */
    private function declaredCharset(string $content): ?string
    {
        if (preg_match('//u', $content) === 1) {
            return null;
        }
        // <meta charset="…"> or a "charset=…" parameter in <meta http-equiv="Content-Type" content="…">
        if (preg_match('/<meta\b[^>]*?[\s;]charset\s*=\s*["\']?\s*([\w.:-]+)/i', $content, $matches) === 1) {
            return $matches[1];
        }
        if (preg_match('/<\?xml\b[^>]*?\bencoding\s*=\s*["\']([\w.:-]+)/i', $content, $matches) === 1) {
            return $matches[1];
        }

        return null;
    }

    /**
     * @param self::ATTRIBUTE_NAME|self::ATTRIBUTE_PROPERTY $attributeName
     */
    private function processMetaTag(Meta $meta, Element $metaTag, string $attributeName): void
    {
        $content = $metaTag->getAttribute(self::ATTRIBUTE_CONTENT);
        $attributeValue = $metaTag->getAttribute($attributeName);
        if ($content === null || $attributeValue === null) {
            return;
        }

        // attribute values are matched case-insensitively ("OG:TITLE" is og:title)
        $setter = self::ALLOWED_ATTRIBUTES[$attributeName][strtolower($attributeValue)] ?? null;
        match ($setter) {
            null => null,
            'setDescription' => $meta->setDescription($content),
            'setKeywords' => $meta->setKeywords($content),
            'addAuthorByName' => $meta->addAuthorByName($content),
            'setOgTitle' => $meta->setOgTitle($content),
            'addSectionByName' => $meta->addSectionByName($content),
            'setPublishedTime' => $meta->setPublishedTime($content),
            'setModifiedTime' => $meta->setModifiedTime($content),
            'setOgDescription' => $meta->setOgDescription($content),
            'setOgType' => $meta->setOgType($content),
            'setOgUrl' => $meta->setOgUrl($content),
            'setOgSiteName' => $meta->setOgSiteName($content),
            'setOgImage' => $meta->setOgImage($content),
        };
    }
}

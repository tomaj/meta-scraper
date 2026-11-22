<?php

declare(strict_types=1);

namespace Tomaj\Scraper\Parser;

use Tomaj\Scraper\Meta;

class OgDomParser implements ParserInterface
{
    protected Meta $meta;

    /**
     * Allowed attribute values per attribute name, mapped to the Meta setter that receives the content.
     *
     * @var array<string, array<string, string>>
     */
    protected array $allowedAttributes = [];

    private const ATTRIBUTE_NAME = 'name';
    private const ATTRIBUTE_PROPERTY = 'property';
    private const ATTRIBUTE_CONTENT = 'content';

    public function __construct()
    {
        $this->meta = new Meta();
        if (!extension_loaded('dom') || !extension_loaded('libxml')) {
            return;
        }

        $this->allowedAttributes = [
            self::ATTRIBUTE_NAME     => [
                'description' => 'setDescription',
                'keywords'    => 'setKeywords',
                'author'      => 'addAuthorByName',
            ],
            self::ATTRIBUTE_PROPERTY => [
                'og:title'               => 'setOgTitle',
                'article:section'        => 'addSectionByName',
                'article:published_time' => 'setPublishedTime',
                'article:modified_time'  => 'setModifiedTime',
                'og:description'         => 'setOgDescription',
                'og:type'                => 'setOgType',
                'og:url'                 => 'setOgUrl',
                'og:site_name'           => 'setOgSiteName',
                'og:image'               => 'setOgImage',
            ],
        ];
    }


    public function parse(string $content): Meta
    {
        if ($content === '') {
            return $this->meta;
        }

        $dom = new \DOMDocument();
        // it is used to ignore warnings like "DOMDocument::loadHTML(): Unexpected end tag : head in Entity"
        libxml_use_internal_errors(true);
        $dom->loadHTML($content);

        /** @var \DOMElement $titleTag */
        foreach ($dom->getElementsByTagName('title') as $titleTag) {
            $this->meta->setTitle(htmlspecialchars_decode($titleTag->nodeValue ?? ''));
            // iterate only over first title tag
            break;
        }

        /** @var \DOMElement $metaTag */
        foreach ($dom->getElementsByTagName('meta') as $metaTag) {
            $this->fixMetaTag($metaTag, [self::ATTRIBUTE_PROPERTY, self::ATTRIBUTE_NAME,]);
            $this->processMetaTag($metaTag, self::ATTRIBUTE_NAME);
            $this->processMetaTag($metaTag, self::ATTRIBUTE_PROPERTY);
        }

        return $this->meta;
    }

    /**
     * @param \DOMElement $metaTag
     * @param string      $attributeName
     */
    protected function processMetaTag(\DOMElement $metaTag, string $attributeName): void
    {
        if (!$metaTag->hasAttribute($attributeName)) {
            return;
        }

        $attributeValue    = $metaTag->getAttribute($attributeName);
        $allowedAttributes = $this->allowedAttributes[$attributeName] ?? [];
        if (!array_key_exists($attributeValue, $allowedAttributes)) {
            return;
        }

        if (!$metaTag->hasAttribute(self::ATTRIBUTE_CONTENT)) {
            return;
        }

        $setter = [$this->meta, $allowedAttributes[$attributeValue]];
        if (!is_callable($setter)) {
            return;
        }

        $setter(htmlspecialchars_decode($metaTag->getAttribute(self::ATTRIBUTE_CONTENT)));
    }

    /**
     * @param list<string> $allowedAttributeNames
     */
    protected function fixMetaTag(\DOMElement &$metaTag, array $allowedAttributeNames): void
    {
        if ($metaTag->attributes->length === 0) {
            return;
        }

        foreach ($metaTag->attributes as $attributeNode) {
            $name = $attributeNode->nodeName;
            if (!in_array($name, $allowedAttributeNames, true)) {
                continue;
            }

            $value          = $metaTag->getAttribute($name);
            $lowerCaseValue = strtolower($value);
            if ($lowerCaseValue === $value) {
                continue;
            }
            if (!array_key_exists($lowerCaseValue, $this->allowedAttributes[$name] ?? [])) {
                continue;
            }

            $metaTag->setAttribute($name, $lowerCaseValue);
        }
    }
}

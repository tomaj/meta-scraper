<?php

declare(strict_types=1);

namespace Tomaj\Scraper\Parser;

use Tomaj\Scraper\Meta;

class OgDomParser implements ParserInterface
{
    /** @var Meta */
    protected $meta;

    /**
     * @var \string[][]
     */
    protected $allowedAttributes;

    private const ATTRIBUTE_NAME = 'name';
    private const ATTRIBUTE_PROPERTY = 'property';
    private const ATTRIBUTE_CONTENT = 'content';

    public function __construct()
    {
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
        $this->meta = new Meta();
        if (!$content) {
            return $this->meta;
        }

        $dom = new \DOMDocument();
        $dom->loadHTML($content);

        /** @var \DOMElement $titleTag */
        foreach ($dom->getElementsByTagName('title') as $titleTag) {
            $this->meta->setTitle(htmlspecialchars_decode($titleTag->nodeValue));
            // iterate only over first title tag
            break;
        }

        /** @var \DOMElement $metaTag */
        foreach ($dom->getElementsByTagName('meta') as $metaTag) {
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
        if (!in_array($attributeValue, array_keys($allowedAttributes))) {
            return;
        }

        if (!$metaTag->hasAttribute(self::ATTRIBUTE_CONTENT)) {
            return;
        }

        call_user_func(
            [$this->meta, $allowedAttributes[$attributeValue]],
            htmlspecialchars_decode($metaTag->getAttribute(self::ATTRIBUTE_CONTENT))
        );
    }
}

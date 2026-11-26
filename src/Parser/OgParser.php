<?php

declare(strict_types=1);

namespace Tomaj\Scraper\Parser;

use Tomaj\Scraper\Author;
use Tomaj\Scraper\Meta;
use Tomaj\Scraper\Section;

/**
 * Reads the title, meta and Open Graph tags with regular expressions; needs no extension.
 *
 * Every expression stays inside one tag: a tag whose content cannot be read gives no value instead of
 * the value of a later tag.
 */
final class OgParser implements ParserInterface
{
    /**
     * Anything inside a tag up to its closing ">". As in the HTML tokenizer, a quote starts a value only after
     * "="; such a value (which may contain ">") is skipped whole and possessively, while a stray quote elsewhere
     * is an ordinary character, so a malformed tag cannot make the expression run on into the rest of the page.
     */
    private const string IN_TAG = '(?:[^>=\s]++|\s|=\s*+(?:"[^"]*+"|\'[^\']*+\')?+)*?';

    #[\Override]
    public function parse(string $content): Meta
    {
        $meta = new Meta();

        if (preg_match('/<title\b[^>]*>(.*?)<\/title\s*>/is', $content, $matches) === 1) {
            $meta->setTitle(htmlspecialchars_decode($matches[1]));
        }

        $value = $this->metaContent($content, 'name', 'description');
        if ($value !== null) {
            $meta->setDescription($value);
        }

        $value = $this->metaContent($content, 'name', 'keywords');
        if ($value !== null) {
            $meta->setKeywords($value);
        }

        $value = $this->metaContent($content, 'name', 'author');
        if ($value !== null) {
            $meta->addAuthor(new Author(null, $value));
        }

        $value = $this->metaContent($content, 'property', 'og:title');
        if ($value !== null) {
            $meta->setOgTitle($value);
        }

        $value = $this->metaContent($content, 'property', 'article:section');
        if ($value !== null) {
            $meta->addSection(new Section(null, $value));
        }

        $value = $this->metaContent($content, 'property', 'article:published_time');
        if ($value !== null) {
            $meta->setPublishedTime($value);
        }

        $value = $this->metaContent($content, 'property', 'article:modified_time');
        if ($value !== null) {
            $meta->setModifiedTime($value);
        }

        $value = $this->metaContent($content, 'property', 'og:description');
        if ($value !== null) {
            $meta->setOgDescription($value);
        }

        $value = $this->metaContent($content, 'property', 'og:type');
        if ($value !== null) {
            $meta->setOgType($value);
        }

        $value = $this->metaContent($content, 'property', 'og:url');
        if ($value !== null) {
            $meta->setOgUrl($value);
        }

        $value = $this->metaContent($content, 'property', 'og:site_name');
        if ($value !== null) {
            $meta->setOgSiteName($value);
        }

        $value = $this->metaContent($content, 'property', 'og:image');
        if ($value !== null) {
            $meta->setOgImage($value);
        }

        return $meta;
    }

    /**
     * The content of the first <meta> tag whose $attribute is $value and which has a quoted content
     * attribute after it, within the same tag.
     *
     * @param 'name'|'property' $attribute
     */
    private function metaContent(string $content, string $attribute, string $value): ?string
    {
        $pattern = '/<meta\b' . self::IN_TAG . '\s' . $attribute . '\s*=\s*(["\'])' . preg_quote($value, '/') . '\1'
            . self::IN_TAG . '\scontent\s*=\s*(?:"([^"]*)"|\'([^\']*)\')/i';

        if (preg_match($pattern, $content, $matches) !== 1) {
            return null;
        }

        // group 2 holds a double-quoted value, group 3 a single-quoted one
        return htmlspecialchars_decode($matches[3] ?? $matches[2]);
    }
}

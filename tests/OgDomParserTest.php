<?php

declare(strict_types=1);

namespace Tomaj\Scraper\Test;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tomaj\Scraper\Author;
use Tomaj\Scraper\Meta;
use Tomaj\Scraper\Parser\OgDomParser;
use Tomaj\Scraper\Parser\OgParser;
use Tomaj\Scraper\Scraper;
use Tomaj\Scraper\Section;

final class OgDomParserTest extends TestCase
{
    public function testOgDomParser(): void
    {
        $data = <<<EOT
                        <title>Page title</title>
                        <meta name="description" content="Default page description"/>
                        <meta name="KEYWORDS" content="Keyword1,Keyword2">
                        <META NAME="author" CONTENT="Jozko Pucik"  >
                        <meta property="og:type" content="article" />
                        <meta property="og:description" content="Silny popis" />
                        <meta property="og:title" content="Og title nadpis"/>
                        <meta property="og:url" content="https://web.sk/stranka.html"/>
                        <meta property="og:site_name" content="Mega site name" />
                        <meta property="article:section" content="Ekonomika" />
                        <meta property="og:image" content="https://obrazok.jpg">
                        <meta property="article:published_time" content="2015-10-12T12:40:27+00:00" />
                        <meta property="article:modified_time" content="2016-11-13T13:21:42+00:00" />
            EOT;

        $meta = new Scraper()->parse($data, [new OgDomParser()]);

        self::assertSame('Page title', $meta->getTitle());

        self::assertSame('Default page description', $meta->getDescription());
        self::assertSame('Keyword1,Keyword2', $meta->getKeywords());
        self::assertEquals([new Author(null, 'Jozko Pucik')], $meta->getAuthors());

        self::assertSame('article', $meta->getOgType());
        self::assertSame('Silny popis', $meta->getOgDescription());
        self::assertSame('https://web.sk/stranka.html', $meta->getOgUrl());
        self::assertSame('Mega site name', $meta->getOgSiteName());
        self::assertSame('Og title nadpis', $meta->getOgTitle());
        self::assertSame('https://obrazok.jpg', $meta->getOgImage());
        self::assertEquals([new Section(null, 'Ekonomika')], $meta->getSections());
        self::assertEquals('12.10.2015 12:40:27', $meta->getPublishedTime()?->format('d.m.Y H:i:s'));
        self::assertEquals('13.11.2016 13:21:42', $meta->getModifiedTime()?->format('d.m.Y H:i:s'));
    }

    public function testReverseAttrPositionOgDomParser(): void
    {
        $data = <<<EOT
                        <title>Page title</title>
                        <meta content="Default page description" name="description"/>
                        <meta content="Keyword1,Keyword2" name="KEYWORDS">
                        <META CONTENT="Jozko Pucik"   NAME="author">
                        <meta content="article" property="og:type" />
                        <meta content="Silny popis" property="og:description" />
                        <meta content="Og title nadpis" property="og:title" />
                        <meta content="https://web.sk/stranka.html" property="og:url" />
                        <meta content="Mega site name" property="og:site_name" />
                        <meta content="Ekonomika" property="article:section" />
                        <meta content="https://obrazok.jpg" property="og:image" />
                        <meta content="2015-10-12T12:40:27+00:00" property="article:published_time" />
                        <meta content="2016-11-13T13:21:42+00:00" property="article:modified_time" />
            EOT;

        $meta = new Scraper()->parse($data, [new OgDomParser()]);

        self::assertSame('Page title', $meta->getTitle());

        self::assertSame('Default page description', $meta->getDescription());
        self::assertSame('Keyword1,Keyword2', $meta->getKeywords());
        self::assertEquals([new Author(null, 'Jozko Pucik')], $meta->getAuthors());

        self::assertSame('article', $meta->getOgType());
        self::assertSame('Silny popis', $meta->getOgDescription());
        self::assertSame('https://web.sk/stranka.html', $meta->getOgUrl());
        self::assertSame('Mega site name', $meta->getOgSiteName());
        self::assertSame('Og title nadpis', $meta->getOgTitle());
        self::assertSame('https://obrazok.jpg', $meta->getOgImage());
        self::assertEquals([new Section(null, 'Ekonomika')], $meta->getSections());
        self::assertEquals('12.10.2015 12:40:27', $meta->getPublishedTime()?->format('d.m.Y H:i:s'));
        self::assertEquals('13.11.2016 13:21:42', $meta->getModifiedTime()?->format('d.m.Y H:i:s'));
    }

    public function testEmptyOgDomParser(): void
    {
        $meta = new Scraper()->parse('empty ', [new OgDomParser()]);

        self::assertNull($meta->getTitle());

        self::assertNull($meta->getDescription());
        self::assertNull($meta->getKeywords());
        self::assertCount(0, $meta->getAuthors());

        self::assertNull($meta->getOgType());
        self::assertNull($meta->getOgDescription());
        self::assertNull($meta->getOgUrl());
        self::assertNull($meta->getOgSiteName());
        self::assertNull($meta->getOgTitle());
        self::assertNull($meta->getOgImage());
        self::assertCount(0, $meta->getSections());
        self::assertNull($meta->getPublishedTime());
        self::assertNull($meta->getModifiedTime());
    }

    public function testEveryParseReturnsANewMetaWithOnlyItsOwnPage(): void
    {
        $parser = new OgDomParser();

        $a = $parser->parse('<html><head><title>A</title><meta name="author" content="Jano"><meta property="og:type" content="article"></head></html>');
        $b = $parser->parse('<html><head><title>B</title><meta name="author" content="Fero"></head></html>');

        self::assertNotSame($a, $b);
        self::assertSame('A', $a->getTitle());
        self::assertEquals([new Author(null, 'Jano')], $a->getAuthors());
        self::assertSame('B', $b->getTitle());
        self::assertEquals([new Author(null, 'Fero')], $b->getAuthors());
        self::assertNull($b->getOgType());
    }

    public function testEmptyContentReturnsAnEmptyMetaAfterAnotherPage(): void
    {
        $parser = new OgDomParser();
        $parser->parse('<title>Filled</title>');

        self::assertEquals(new Meta(), $parser->parse(''));
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function utf8Pages(): iterable
    {
        $head = '<meta property="og:title" content="Ťažký"><meta name="description" content="Príliš žltučký kôň úpel ďábelské ódy"><title>Žltý kôň</title>';

        yield 'fragment without charset' => [$head];
        yield 'document without charset' => ['<!DOCTYPE html><html><head>' . $head . '</head><body>ä</body></html>'];
        yield 'meta charset' => ['<html><head><meta charset="utf-8">' . $head . '</head></html>'];
        yield 'http-equiv charset' => ['<html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">' . $head . '</head></html>'];
    }

    #[DataProvider('utf8Pages')]
    public function testUtf8TextIsReadAsUtf8(string $html): void
    {
        $dom = new OgDomParser()->parse($html);
        $regex = new OgParser()->parse($html);

        self::assertSame('Ťažký', $dom->getOgTitle());
        self::assertSame('Žltý kôň', $dom->getTitle());
        self::assertSame('Príliš žltučký kôň úpel ďábelské ódy', $dom->getDescription());
        self::assertSame($regex->getOgTitle(), $dom->getOgTitle());
        self::assertSame($regex->getTitle(), $dom->getTitle());
        self::assertSame($regex->getDescription(), $dom->getDescription());
    }

    public function testDeclaredCharsetIsDecoded(): void
    {
        $html = '<html><head><META CHARSET="windows-1250"><title>' . "\x8eltu\xe8k\xfd" . '</title>'
            . '<meta property="og:title" content="' . "\x8da\x9ek\xfd" . '"></head></html>';

        $meta = new OgDomParser()->parse($html);

        self::assertSame('Žltučký', $meta->getTitle());
        self::assertSame('Ťažký', $meta->getOgTitle());
    }

    public function testAttributeValuesAreCaseInsensitive(): void
    {
        $meta = new OgDomParser()->parse('<meta property="OG:Title" content="Upper"><meta name="Description" content="Desc"><meta property="og:unknown" content="x"><meta name="author">');

        self::assertSame('Upper', $meta->getOgTitle());
        self::assertSame('Desc', $meta->getDescription());
        self::assertSame([], $meta->getAuthors());
    }

    public function testOnlyTheFirstTitleIsUsedAndEntitiesAreDecoded(): void
    {
        $meta = new OgDomParser()->parse('<title>First &amp; best</title><title>Second</title><meta property="og:site_name" content="Tom &amp; Jerry">');

        self::assertSame('First & best', $meta->getTitle());
        self::assertSame('Tom & Jerry', $meta->getOgSiteName());
    }

    public function testLibxmlErrorHandlingIsRestored(): void
    {
        $previous = libxml_use_internal_errors(false);
        try {
            new OgDomParser()->parse('<html><head></body></head><p><p></html>');

            self::assertFalse(libxml_use_internal_errors());
            self::assertSame([], libxml_get_errors());
        } finally {
            libxml_use_internal_errors($previous);
        }
    }

    public function testOwnLibxmlErrorsAreClearedWhenTheCallerCollectsErrors(): void
    {
        $previous = libxml_use_internal_errors(true);
        try {
            new OgDomParser()->parse('<html><head></body></head><p><p></html>');

            self::assertTrue(libxml_use_internal_errors());
            self::assertSame([], libxml_get_errors());
        } finally {
            libxml_use_internal_errors($previous);
        }
    }
}

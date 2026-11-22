<?php

declare(strict_types=1);

namespace Tomaj\Scraper\Test;

use PHPUnit\Framework\TestCase;
use Tomaj\Scraper\Author;
use Tomaj\Scraper\Meta;
use Tomaj\Scraper\Parser\OgParser;
use Tomaj\Scraper\Parser\SchemaParser;
use Tomaj\Scraper\Scraper;
use Tomaj\Scraper\Section;
use Tomaj\Scraper\Tag;

final class MetaTest extends TestCase
{
    public function testMerge(): void
    {
        $data = <<<EOT
                    <meta charset="utf-8" />

                    <title>New title</title>
                    <meta property="og:description" content="Čo spája miesta na Zemi, kde ľudia žijú najdlhšie? OG">
                    <meta property="og:title" content="Veľa pohybu a&nbsp;málo mäsa. Päť miest, kde ľudia žijú najdlhšie, ohrozuje westernizácia OG">

                    <script id="schema" type="application/ld+json">{"@context":"http://schema.org","@type":"NewsArticle","url":"https://dennikn.sk/1325802/vela-pohybu-a-malo-masa-pat-miest-kde-ludia-ziju-najdlhsie-ohrozuje-westernizacia/","position":1325802,"headline":"Veľa pohybu a málo mäsa. Päť miest, kde ľudia žijú najdlhšie, ohrozuje westernizácia","description":"Čo spája miesta na Zemi, kde ľudia žijú najdlhšie?","articleSection":["Svet","Veda"],"datePublished":"2018-12-15T19:00:26+00:00","dateModified":"2018-12-17T00:28:43+00:00","wordCount":1650,"mainEntityOfPage":{"@type":"WebPage","@id":"https://dennikn.sk/1325802/vela-pohybu-a-malo-masa-pat-miest-kde-ludia-ziju-najdlhsie-ohrozuje-westernizacia/"},"author":[{"@type":"Person","@id":"495","name":"Tomáš Vasilko"}],"publisher":{"@type":"Organization","name":"Denník N","logo":{"@type":"ImageObject","url":"https://dennikn.sk/wp-content/themes/dn-2-sk/dennikn-60x60.png","width":60,"height":60}},"image":{"@type":"ImageObject","url":"https://img.projektn.sk/wp-static/2018/12/XkJ05Z9kQFZoy6hlKrEcj8U3Aevn1wfu-6r8OMz0Ah4.jpg","width":756,"height":427,"thumbnail":{"@type":"ImageObject","url":"https://img.projektn.sk/wp-static/2018/12/XkJ05Z9kQFZoy6hlKrEcj8U3Aevn1wfu-6r8OMz0Ah4.jpg?fm=jpg&q=80&w=360&h=200&fit=crop","width":360,"height":200}},"isAccessibleForFree":false,"hasPart":[{"@type":"WebPageElement","isAccessibleForFree":"False","cssSelector":".a_single"}]}</script>
            EOT;

        $scraper = new Scraper();
        $meta = $scraper->parse($data, [
            new SchemaParser(),
            new OgParser(),
        ]);

        self::assertSame('Veľa pohybu a málo mäsa. Päť miest, kde ľudia žijú najdlhšie, ohrozuje westernizácia', $meta->getTitle());

        self::assertSame('Čo spája miesta na Zemi, kde ľudia žijú najdlhšie?', $meta->getDescription());

        self::assertSame('Čo spája miesta na Zemi, kde ľudia žijú najdlhšie? OG', $meta->getOgDescription());

        self::assertSame('https://dennikn.sk/1325802/vela-pohybu-a-malo-masa-pat-miest-kde-ludia-ziju-najdlhsie-ohrozuje-westernizacia/', $meta->getOgUrl());

        self::assertEquals([new Section(null, 'Svet'), new Section(null, 'Veda')], $meta->getSections());

        self::assertSame('Veľa pohybu a&nbsp;málo mäsa. Päť miest, kde ľudia žijú najdlhšie, ohrozuje westernizácia OG', $meta->getOgTitle());

        self::assertSame('https://img.projektn.sk/wp-static/2018/12/XkJ05Z9kQFZoy6hlKrEcj8U3Aevn1wfu-6r8OMz0Ah4.jpg', $meta->getOgImage());

        self::assertEquals([new Author('495', 'Tomáš Vasilko')], $meta->getAuthors());

        self::assertEquals(new \DateTime('@' . strtotime('2018-12-15T19:00:26+00:00')), $meta->getPublishedTime());

        self::assertEquals(new \DateTime('@' . strtotime('2018-12-17T00:28:43+00:00')), $meta->getModifiedTime());

    }

    public function testSettersAndGetters(): void
    {
        $published = new \DateTime('2020-01-02 03:04:05');
        $meta = new Meta()
            ->setTitle('Title')
            ->setDescription('Description')
            ->setKeywords('a,b')
            ->setOgType('article')
            ->setOgTitle('OG title')
            ->setOgDescription('OG description')
            ->setOgUrl('https://example.com/')
            ->setOgSiteName('Example')
            ->setOgImage('https://example.com/i.jpg')
            ->addAuthor(new Author('1', 'Jana'))
            ->addAuthorByName('Fero')
            ->addSection(new Section('2', 'Sport'))
            ->addSectionByName('News')
            ->addTag(new Tag('3', 'PHP'))
            ->setPublishedTime($published)
            ->setModifiedTime('2021-06-07T08:09:10+00:00');

        self::assertSame('Title', $meta->getTitle());
        self::assertSame('Description', $meta->getDescription());
        self::assertSame('a,b', $meta->getKeywords());
        self::assertSame('article', $meta->getOgType());
        self::assertSame('OG title', $meta->getOgTitle());
        self::assertSame('OG description', $meta->getOgDescription());
        self::assertSame('https://example.com/', $meta->getOgUrl());
        self::assertSame('Example', $meta->getOgSiteName());
        self::assertSame('https://example.com/i.jpg', $meta->getOgImage());
        self::assertEquals([new Author('1', 'Jana'), new Author(null, 'Fero')], $meta->getAuthors());
        self::assertEquals([new Section('2', 'Sport'), new Section(null, 'News')], $meta->getSections());
        self::assertEquals([new Tag('3', 'PHP')], $meta->getTags());
        self::assertSame($published, $meta->getPublishedTime());
        self::assertSame('2021-06-07T08:09:10+00:00', $meta->getModifiedTime()?->format(DATE_ATOM));
    }

    public function testInvalidDateStringsAreIgnored(): void
    {
        $meta = new Meta()->setPublishedTime('2020-01-01')->setPublishedTime('not a date')->setModifiedTime('not a date');

        self::assertSame('2020-01-01', $meta->getPublishedTime()?->format('Y-m-d'));
        self::assertNull($meta->getModifiedTime());
    }

    public function testMergeFillsOnlyMissingValues(): void
    {
        $date = new \DateTime('@0');
        $other = new Meta()
            ->setTitle('Other title')->setDescription('Other description')->setKeywords('k')
            ->setOgType('t')->setOgTitle('ot')->setOgDescription('od')->setOgUrl('u')->setOgSiteName('s')->setOgImage('i')
            ->addAuthorByName('A')->addSectionByName('S')->addTag(new Tag(null, 'T'))
            ->setPublishedTime($date)->setModifiedTime($date);

        $empty = new Meta()->merge($other);
        self::assertEquals($other, $empty);

        $filled = new Meta()
            ->setTitle('Mine')->setDescription('My description')->setKeywords('mine')
            ->setOgType('mt')->setOgTitle('mot')->setOgDescription('mod')->setOgUrl('mu')->setOgSiteName('ms')->setOgImage('mi')
            ->addAuthorByName('Me')->addSectionByName('Mine')->addTag(new Tag(null, 'Mine'))
            ->setPublishedTime('2001-01-01T00:00:00+00:00')->setModifiedTime('2002-01-01T00:00:00+00:00');
        $copy = clone $filled;

        self::assertSame($filled, $filled->merge($other));
        self::assertEquals($copy, $filled);
    }

    public function testMergeTreatsEmptyStringAndZeroAsMissing(): void
    {
        $meta = new Meta()->setTitle('')->setDescription('0')->merge(new Meta()->setTitle('Filled')->setDescription('Also filled')->setOgTitle('0'));

        self::assertSame('Filled', $meta->getTitle());
        self::assertSame('Also filled', $meta->getDescription());
        self::assertNull($meta->getOgTitle());
    }

    public function testValueObjects(): void
    {
        self::assertSame('1', new Author('1', 'Jana')->id());
        self::assertSame('Jana', new Author('1', 'Jana')->name());
        self::assertSame('2', new Section('2', 'Sport')->id());
        self::assertSame('Sport', new Section('2', 'Sport')->name());
        self::assertSame('3', new Tag('3', 'PHP')->id());
        self::assertSame('PHP', new Tag('3', 'PHP')->name());
        self::assertNull(new Tag(null, null)->id());
        self::assertNull(new Tag(null, null)->name());
    }
}

<?php

declare(strict_types=1);

namespace Tomaj\Scraper\Test;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tomaj\Scraper\Author;
use Tomaj\Scraper\Meta;
use Tomaj\Scraper\Parser\SchemaParser;
use Tomaj\Scraper\Scraper;
use Tomaj\Scraper\Section;
use Tomaj\Scraper\Tag;

final class SchemaParserTest extends TestCase
{
    public function testSchemaParser(): void
    {
        $data = <<<EOT
                    text
                    <script id="schema" type="application/ld+json">{"@context":"http://schema.org","@type":"NewsArticle","url":"https://dennikn.sk/1325802/vela-pohybu-a-malo-masa-pat-miest-kde-ludia-ziju-najdlhsie-ohrozuje-westernizacia/","position":1325802,"headline":"Veľa pohybu a málo mäsa. Päť miest, kde ľudia žijú najdlhšie, ohrozuje westernizácia","description":"Čo spája miesta na Zemi, kde ľudia žijú najdlhšie?","articleSection":["Svet","Veda"],"datePublished":"2018-12-15T19:00:26+00:00","dateModified":"2018-12-17T00:28:43+00:00","wordCount":1650,"mainEntityOfPage":{"@type":"WebPage","@id":"https://dennikn.sk/1325802/vela-pohybu-a-malo-masa-pat-miest-kde-ludia-ziju-najdlhsie-ohrozuje-westernizacia/"},"author":[{"@type":"Person","@id":"495","name":"Tomáš Vasilko"}],"publisher":{"@type":"Organization","name":"Denník N","logo":{"@type":"ImageObject","url":"https://dennikn.sk/wp-content/themes/dn-2-sk/dennikn-60x60.png","width":60,"height":60}},"image":{"@type":"ImageObject","url":"https://img.projektn.sk/wp-static/2018/12/XkJ05Z9kQFZoy6hlKrEcj8U3Aevn1wfu-6r8OMz0Ah4.jpg","width":756,"height":427,"thumbnail":{"@type":"ImageObject","url":"https://img.projektn.sk/wp-static/2018/12/XkJ05Z9kQFZoy6hlKrEcj8U3Aevn1wfu-6r8OMz0Ah4.jpg?fm=jpg&q=80&w=360&h=200&fit=crop","width":360,"height":200}},"isAccessibleForFree":false,"hasPart":[{"@type":"WebPageElement","isAccessibleForFree":"False","cssSelector":".a_single"}]}</script>
                    text
            EOT;

        $scraper = new Scraper();
        $meta = $scraper->parse($data, [new SchemaParser()]);

        self::assertSame('Veľa pohybu a málo mäsa. Päť miest, kde ľudia žijú najdlhšie, ohrozuje westernizácia', $meta->getTitle());

        self::assertSame('Čo spája miesta na Zemi, kde ľudia žijú najdlhšie?', $meta->getDescription());

        self::assertEquals(null, $meta->getOgDescription());

        self::assertSame('https://dennikn.sk/1325802/vela-pohybu-a-malo-masa-pat-miest-kde-ludia-ziju-najdlhsie-ohrozuje-westernizacia/', $meta->getOgUrl());

        self::assertEquals([new Section(null, 'Svet'), new Section(null, 'Veda')], $meta->getSections());

        self::assertEquals(null, $meta->getOgTitle());

        self::assertSame('https://img.projektn.sk/wp-static/2018/12/XkJ05Z9kQFZoy6hlKrEcj8U3Aevn1wfu-6r8OMz0Ah4.jpg', $meta->getOgImage());

        self::assertEquals([new Author('495', 'Tomáš Vasilko')], $meta->getAuthors());

        self::assertEquals(new \DateTime('@' . strtotime('2018-12-15T19:00:26+00:00')), $meta->getPublishedTime());

        self::assertEquals(new \DateTime('@' . strtotime('2018-12-17T00:28:43+00:00')), $meta->getModifiedTime());
    }

    public function testSchemaParserSections(): void
    {
        $data = <<<EOT
                    text
                    <script id="schema" type="application/ld+json">{"@context":"http://schema.org","@type":"NewsArticle","url":"https://dennikn.sk/1551857/osobne-bankroty-rychlo-pribudaju-nik-nevie-kolko-si-z-toho-odpisuju-podvodnici/","position":1551857,"headline":"Osobné bankroty rýchlo pribúdajú, nik nevie, koľko si z toho odpisujú podvodníci","description":"Len na daniach z&nbsp;príjmu a&nbsp;zdravotnom poistení bez započítania sociálnych odvodov odpustil štát za posledné dva roky ľuďom, ktorí vyhlásili bankrot, najmenej 40 miliónov eur. Možno až tretina z&nbsp;nich však splácať vedela, len využila, že to pred odpustením dlhov nikto poriadne nepreveruje. ","datePublished":"2019-08-12T14:53:54+00:00","dateModified":"2019-08-13T01:21:36+00:00","wordCount":176,"mainEntityOfPage":{"@type":"WebPage","@id":"https://dennikn.sk/1551857/osobne-bankroty-rychlo-pribudaju-nik-nevie-kolko-si-z-toho-odpisuju-podvodnici/"},"author":[{"@type":"Person","@id":"5331","name":"Martina Kláseková"}],"publisher":{"@type":"Organization","name":"Denník N","logo":{"@type":"ImageObject","url":"https://dennikn.sk/wp-content/themes/dn-2-sk/dennikn-60x60.png","width":60,"height":60}},"articleSection":["Ekonomika","Hlavná"],"articleTerms":[{"@id":432,"@type":"Category","name":"Ekonomika"},{"@id":2386,"@type":"Category","name":"Hlavná"},{"@id":9226,"@type":"Tag","name":"Osobné bankroty"}],"image":{"@type":"ImageObject","url":"https://img.projektn.sk/wp-static/2019/08/AdobeStock_242090991-bankrot.jpg","width":1200,"height":628,"thumbnail":{"@type":"ImageObject","url":"https://img.projektn.sk/wp-static/2019/08/AdobeStock_242090991-bankrot.jpg?fm=jpg&q=85&w=360&h=200&fit=crop","width":360,"height":200}},"isAccessibleForFree":false,"hasPart":[{"@type":"WebPageElement","isAccessibleForFree":"False","cssSelector":".a_single"}]}</script>
                    text
            EOT;

        $scraper = new Scraper();
        $meta = $scraper->parse($data, [new SchemaParser()]);

        self::assertEquals([new Section('432', 'Ekonomika'), new Section('2386', 'Hlavná')], $meta->getSections());
    }

    public function testSchemaParserTags(): void
    {
        $scraper = new Scraper();

        // array of about
        $data = <<<EOT
                    text
                    <script id="schema" type="application/ld+json">{ "@context": "http://schema.org", "@type": "NewsArticle", "url": "https://dennikn.sk/1917569/prezidentka-svojich-partnerov-nezapiera-a-tak-je-to-spravne/", "position": 1917569, "headline": "Prezidentka svojich partnerov nezapiera a tak je to správne", "description": "&#8222;Pani prezidentka, priznám sa, že aj ja sa cítim tak trocha v&nbsp;rozpakoch, ale napriek tomu, už niekoľko dní je v&nbsp;médiách informácia, že sa vám darí aj v&nbsp;súkromnom živote. Takže, to je dobrá správa,&#8220; prihovorila sa moderátorka televízie Markíza Zlatica Puškárová k&nbsp;Zuzane Čaputovej. To, že sa trochu rozpakuje pýtať sa na nového partnera, bolo zrejmé. Prezidentka [&hellip;]", "datePublished": "2020-06-04T14:49:22+02:00", "dateModified": "2020-06-04T22:03:12+02:00", "wordCount": 3031, "mainEntityOfPage": { "@type": "WebPage", "@id": "https://dennikn.sk/1917569/prezidentka-svojich-partnerov-nezapiera-a-tak-je-to-spravne/" }, "author": [{ "@type": "Person", "@id": "663", "name": "Ria Gehrerová" }], "publisher": { "@type": "Organization", "name": "Denník N", "logo": { "@type": "ImageObject", "url": "https://dennikn.sk/wp-content/themes/dn-2-sk/dennikn-60x60.png", "width": 60, "height": 60 } }, "articleSection": ["Rodina a vzťahy", "Slovensko"], "articleTerms": [{ "@id": "7657", "@type": "Category", "name": "Rodina a vzťahy" }, { "@id": "430", "@type": "Category", "name": "Slovensko" }, { "@id": "7678", "@type": "Tag", "name": "Zuzana Čaputová" }], "about": [{ "@id": "7678", "name": "Zuzana Čaputová" }, { "@id": "9999", "name": "Foo" }], "image": { "@type": "ImageObject", "url": "https://img.projektn.sk/wp-static/2020/06/laska-par-Zuzana-Caputova-Juraj-Rizman-Prezidentka.jpg", "width": 500, "height": 375, "thumbnail": { "@type": "ImageObject", "url": "https://img.projektn.sk/wp-static/2020/06/laska-par-Zuzana-Caputova-Juraj-Rizman-Prezidentka.jpg?fm=jpg&q=85&w=360&h=200&fit=crop", "width": 360, "height": 200 } }, "isAccessibleForFree": false, "hasPart": [{ "@type": "WebPageElement", "isAccessibleForFree": "False", "cssSelector": ".a_single" }] }</script>
                    text
            EOT;
        $meta = $scraper->parse($data, [new SchemaParser()]);
        self::assertEquals([new Tag('7678', 'Zuzana Čaputová'), new Tag('9999', 'Foo')], $meta->getTags());

        // single non-array about
        $data = <<<EOT
                    text
                    <script id="schema" type="application/ld+json">{ "@context": "http://schema.org", "@type": "NewsArticle", "url": "https://dennikn.sk/1917569/prezidentka-svojich-partnerov-nezapiera-a-tak-je-to-spravne/", "position": 1917569, "headline": "Prezidentka svojich partnerov nezapiera a tak je to správne", "description": "&#8222;Pani prezidentka, priznám sa, že aj ja sa cítim tak trocha v&nbsp;rozpakoch, ale napriek tomu, už niekoľko dní je v&nbsp;médiách informácia, že sa vám darí aj v&nbsp;súkromnom živote. Takže, to je dobrá správa,&#8220; prihovorila sa moderátorka televízie Markíza Zlatica Puškárová k&nbsp;Zuzane Čaputovej. To, že sa trochu rozpakuje pýtať sa na nového partnera, bolo zrejmé. Prezidentka [&hellip;]", "datePublished": "2020-06-04T14:49:22+02:00", "dateModified": "2020-06-04T22:03:12+02:00", "wordCount": 3031, "mainEntityOfPage": { "@type": "WebPage", "@id": "https://dennikn.sk/1917569/prezidentka-svojich-partnerov-nezapiera-a-tak-je-to-spravne/" }, "author": [{ "@type": "Person", "@id": "663", "name": "Ria Gehrerová" }], "publisher": { "@type": "Organization", "name": "Denník N", "logo": { "@type": "ImageObject", "url": "https://dennikn.sk/wp-content/themes/dn-2-sk/dennikn-60x60.png", "width": 60, "height": 60 } }, "articleSection": ["Rodina a vzťahy", "Slovensko"], "articleTerms": [{ "@id": "7657", "@type": "Category", "name": "Rodina a vzťahy" }, { "@id": "430", "@type": "Category", "name": "Slovensko" }, { "@id": "7678", "@type": "Tag", "name": "Zuzana Čaputová" }], "about": { "@id": "7678", "name": "Zuzana Čaputová" }, "image": { "@type": "ImageObject", "url": "https://img.projektn.sk/wp-static/2020/06/laska-par-Zuzana-Caputova-Juraj-Rizman-Prezidentka.jpg", "width": 500, "height": 375, "thumbnail": { "@type": "ImageObject", "url": "https://img.projektn.sk/wp-static/2020/06/laska-par-Zuzana-Caputova-Juraj-Rizman-Prezidentka.jpg?fm=jpg&q=85&w=360&h=200&fit=crop", "width": 360, "height": 200 } }, "isAccessibleForFree": false, "hasPart": [{ "@type": "WebPageElement", "isAccessibleForFree": "False", "cssSelector": ".a_single" }] }</script>
                    text
            EOT;
        $meta = $scraper->parse($data, [new SchemaParser()]);
        self::assertEquals([new Tag('7678', 'Zuzana Čaputová')], $meta->getTags());
    }

    public function testSchemaParserSingleAuthorObject(): void
    {
        $data = '<script id="schema" type="application/ld+json">{"author":{"@type":"Person","@id":"a1","name":"Jana"},"about":{"@id":5,"name":"T"}}</script>';

        $meta = new Scraper()->parse($data, [new SchemaParser()]);

        self::assertEquals([new Author('a1', 'Jana')], $meta->getAuthors());
        self::assertEquals([new Tag('5', 'T')], $meta->getTags());
    }

    public function testSchemaParserUnexpectedTypes(): void
    {
        $data = '<script id="schema" type="application/ld+json">{"author":["A","B"],"headline":123,"description":["x"],"articleSection":["S",7],"about":[],"image":"https://i/y.jpg","url":{"x":1}}</script>';

        $meta = new Scraper()->parse($data, [new SchemaParser()]);

        self::assertEquals([new Author(null, 'A'), new Author(null, 'B')], $meta->getAuthors());
        self::assertSame('123', $meta->getTitle());
        self::assertNull($meta->getDescription());
        self::assertEquals([new Section(null, 'S'), new Section(null, '7')], $meta->getSections());
        self::assertSame([], $meta->getTags());
        self::assertSame('https://i/y.jpg', $meta->getOgImage());
        self::assertNull($meta->getOgUrl());
    }

    public function testSchemaParserIgnoresInvalidJson(): void
    {
        $meta = new Scraper()->parse('<script id="schema" type="application/ld+json">{headline:</script>', [new SchemaParser()]);

        self::assertNull($meta->getTitle());
        self::assertSame([], $meta->getAuthors());
    }

    public function testStandardBlockWithoutId(): void
    {
        $meta = new SchemaParser()->parse('<script type="application/ld+json">{"@context":"https://schema.org","@type":"NewsArticle","headline":"Standard","author":{"@type":"Person","name":"Jana"}}</script>');

        self::assertSame('Standard', $meta->getTitle());
        self::assertEquals([new Author(null, 'Jana')], $meta->getAuthors());
    }

    public function testAttributeOrderAndQuotingDoNotMatter(): void
    {
        $blocks = [
            "<script data-x='1' type='application/ld+json'>{\"@type\":\"Article\",\"headline\":\"Single quotes\"}</script>",
            '<script type=application/ld+json class="yoast-schema-graph">{"@type":"Article","headline":"Unquoted"}</script>',
            '<SCRIPT TYPE="application/ld+json" ID="other">{"@type":"Article","headline":"Upper case"}</SCRIPT >',
        ];

        self::assertSame('Single quotes', new SchemaParser()->parse($blocks[0])->getTitle());
        self::assertSame('Unquoted', new SchemaParser()->parse($blocks[1])->getTitle());
        self::assertSame('Upper case', new SchemaParser()->parse($blocks[2])->getTitle());
    }

    public function testMultilineJson(): void
    {
        $html = <<<'HTML'
            <script type="application/ld+json">
            {
                "@context": "https://schema.org",
                "@type": "BlogPosting",
                "headline": "Multiline",
                "datePublished": "2024-05-01T10:00:00+02:00"
            }
            </script>
            HTML;

        $meta = new SchemaParser()->parse($html);

        self::assertSame('Multiline', $meta->getTitle());
        self::assertSame('2024-05-01T08:00:00+00:00', $meta->getPublishedTime()?->format(DATE_ATOM));
    }

    public function testArticleFromLaterBlockWins(): void
    {
        $html = '<script type="application/ld+json">{"@type":"BreadcrumbList","itemListElement":[]}</script>'
            . '<script type="application/ld+json">{"@type":"Organization","name":"Publisher","url":"https://publisher.example/","description":"Org"}</script>'
            . '<script type="application/ld+json">{"@type":"WebPage","name":"Page","description":"Page description"}</script>'
            . '<script type="application/ld+json">{"@type":"NewsArticle","headline":"The article","url":"https://publisher.example/a"}</script>';

        $meta = new SchemaParser()->parse($html);

        self::assertSame('The article', $meta->getTitle());
        self::assertSame('https://publisher.example/a', $meta->getOgUrl());
        self::assertNull($meta->getDescription());
    }

    public function testWebPageIsUsedWithoutAnArticle(): void
    {
        $html = '<script type="application/ld+json">[{"@type":"Organization","url":"https://publisher.example/"},{"@type":["CollectionPage","WebPage"],"url":"https://publisher.example/news","description":"News"}]</script>';

        $meta = new SchemaParser()->parse($html);

        self::assertSame('https://publisher.example/news', $meta->getOgUrl());
        self::assertSame('News', $meta->getDescription());
    }

    public function testOtherTypesAreIgnored(): void
    {
        $html = '<script type="application/ld+json">{"@type":"Organization","name":"Publisher","url":"https://publisher.example/","description":"Org"}</script>'
            . '<script type="application/ld+json">{"@type":"ImageObject","url":"https://publisher.example/logo.png"}</script>'
            . '<script type="application/ld+json">{"headline":"No type"}</script>';

        self::assertEquals(new Meta(), new SchemaParser()->parse($html));
    }

    public function testGraphWithReferences(): void
    {
        $html = <<<'HTML'
            <script type="application/ld+json" class="yoast-schema-graph">{"@context":"https://schema.org","@graph":[
                {"@type":"WebPage","@id":"https://example.com/a/","url":"https://example.com/a/","name":"Page"},
                {"@type":"NewsArticle","@id":"https://example.com/a/#article","headline":"Graph article",
                 "author":[{"@id":"https://example.com/#/person/1"},{"@id":"https://example.com/#/person/missing"}],
                 "image":{"@id":"https://example.com/a/#primaryimage"},
                 "about":{"@id":"https://example.com/#organization"},
                 "articleSection":["AI","Hardware"],"datePublished":"2026-11-21T21:07:42+00:00"},
                {"@type":"ImageObject","@id":"https://example.com/a/#primaryimage","url":"https://example.com/a.jpg"},
                {"@type":"Person","@id":"https://example.com/#/person/1","name":"Anthony"},
                {"@type":"Organization","@id":"https://example.com/#organization","name":"Example"}
            ]}</script>
            HTML;

        $meta = new SchemaParser()->parse($html);

        self::assertSame('Graph article', $meta->getTitle());
        self::assertEquals([new Author('https://example.com/#/person/1', 'Anthony'), new Author('https://example.com/#/person/missing', null)], $meta->getAuthors());
        self::assertSame('https://example.com/a.jpg', $meta->getOgImage());
        self::assertEquals([new Tag('https://example.com/#organization', 'Example')], $meta->getTags());
        self::assertEquals([new Section(null, 'AI'), new Section(null, 'Hardware')], $meta->getSections());
        self::assertNull($meta->getOgUrl());
    }

    public function testNestedGraphAndListBlocksAreFlattened(): void
    {
        $html = '<script type="application/ld+json">[{"@graph":[{"@graph":[{"@type":"Article","headline":"Deep"}]}]}, 5, "x"]</script>';

        self::assertSame('Deep', new SchemaParser()->parse($html)->getTitle());
    }

    public function testFirstArticleWins(): void
    {
        $html = '<script type="application/ld+json">{"@graph":[{"@type":"WebPage","headline":"Page"},{"@type":"Article","headline":"First"},{"@type":"BlogPosting","headline":"Second"}]}</script>';

        self::assertSame('First', new SchemaParser()->parse($html)->getTitle());
    }

    public function testLegacyBlockWinsOverStandardBlocks(): void
    {
        $html = '<script type="application/ld+json">{"@type":"NewsArticle","headline":"Standard"}</script>'
            . '<script id="schema" type="application/ld+json">{"headline":"Legacy"}</script>'
            . "<script type='application/ld+json' id='schema'>{\"headline\":\"Second legacy\"}</script>";

        self::assertSame('Legacy', new SchemaParser()->parse($html)->getTitle());
    }

    public function testLegacyBlockMayUseOtherAttributeOrder(): void
    {
        $html = "<script type='application/ld+json' id=schema>{\"headline\":\"Legacy\"}</script>"
            . '<script type="application/ld+json">{"@type":"NewsArticle","headline":"Standard"}</script>';

        self::assertSame('Legacy', new SchemaParser()->parse($html)->getTitle());
    }

    public function testIdThatOnlyStartsWithSchemaIsNotLegacy(): void
    {
        $html = '<script id="schema-org" type="application/ld+json">{"headline":"Untyped"}</script>'
            . '<script type="application/ld+json" id=schemas>{"headline":"Untyped too"}</script>'
            . '<script type="application/ld+json">{"@type":"NewsArticle","headline":"Standard"}</script>';

        self::assertSame('Standard', new SchemaParser()->parse($html)->getTitle());
    }

    public function testInvalidAndEmptyBlocksAreSkipped(): void
    {
        $html = '<script id="schema" type="application/ld+json">{broken</script>'
            . '<script type="application/ld+json">[]</script>'
            . '<script type="application/ld+json">"text"</script>'
            . '<script type="text/javascript">{"@type":"Article","headline":"Not JSON-LD"}</script>'
            . '<script type="application/ld+json">  {"@type":"Article","headline":"Valid"}  </script>';

        self::assertSame('Valid', new SchemaParser()->parse($html)->getTitle());
    }

    public function testNoScriptReturnsAnEmptyMeta(): void
    {
        self::assertEquals(new Meta(), new SchemaParser()->parse('<html><head><title>x</title></head></html>'));
        self::assertEquals(new Meta(), new SchemaParser()->parse(''));
    }

    public function testArticleTermsCategoriesBecomeSections(): void
    {
        $html = '<script type="application/ld+json">{"@type":"Article","articleSection":"Ignored","articleTerms":[{"@type":"Category","@id":7,"name":"Sport"},{"@type":"Tag","name":"x"},{"@id":"#cat"}]}</script>'
            . '<script type="application/ld+json">{"@type":"Category","@id":"#cat","name":"Referenced"}</script>';

        $meta = new SchemaParser()->parse($html);

        self::assertEquals([new Section('7', 'Sport'), new Section('#cat', 'Referenced')], $meta->getSections());
    }

    public function testLegacyBlockReferencesNodesOfOtherBlocks(): void
    {
        $html = '<script ID="SCHEMA" type="application/ld+json">{"headline":"Legacy","author":{"@id":"#p"}}</script>'
            . '<script type="application/ld+json">{"@type":"Person","@id":"#p","name":"Referenced"}</script>';

        $meta = new SchemaParser()->parse($html);

        self::assertSame('Legacy', $meta->getTitle());
        self::assertEquals([new Author('#p', 'Referenced')], $meta->getAuthors());
    }

    public function testFirstNodeWithAnIdWinsAndInlineValuesAreKept(): void
    {
        $html = '<script type="application/ld+json">{"@graph":['
            . '{"@type":"Article","author":[{"@id":"#p"},{"@id":"#q","name":"Inline"}]},'
            . '{"@type":"Person","@id":"#p","name":"First"},{"@type":"Person","@id":"#p","name":"Second"},'
            . '{"@type":"Person","@id":"#q","name":"Node"},{"@type":"Person","name":"No id"}]}</script>';

        $meta = new SchemaParser()->parse($html);

        self::assertEquals([new Author('#p', 'First'), new Author('#q', 'Inline')], $meta->getAuthors());
    }

    public function testScalarsAndGraphsBeforeTheArticleAreSkipped(): void
    {
        $html = '<script type="application/ld+json">[5, "x", {"@graph":[{"@type":"WebSite"}]}, {"@type":"Article","headline":"After"}]</script>';

        self::assertSame('After', new SchemaParser()->parse($html)->getTitle());
    }

    public function testTypeListMayContainNonStrings(): void
    {
        $html = '<script type="application/ld+json">{"@type":[["Nested"], 5, "Article"],"headline":"Typed"}</script>';

        self::assertSame('Typed', new SchemaParser()->parse($html)->getTitle());
    }

    public function testMissingFieldsStayEmpty(): void
    {
        $meta = new SchemaParser()->parse('<script type="application/ld+json">{"@type":"Article","headline":"Only headline"}</script>');

        self::assertSame('Only headline', $meta->getTitle());
        self::assertSame([], $meta->getAuthors());
        self::assertSame([], $meta->getSections());
        self::assertSame([], $meta->getTags());
        self::assertNull($meta->getDescription());
        self::assertNull($meta->getOgImage());
        self::assertNull($meta->getPublishedTime());
    }

    /**
     * @return iterable<string, array{string, ?string}>
     */
    public static function imageForms(): iterable
    {
        yield 'a URL string' => ['image-string.html', 'https://news.example/a.jpg'];
        yield 'a list of URL strings: the first one' => ['image-list-of-strings.html', 'https://news.example/1x1/a.jpg'];
        yield 'a list of ImageObjects: the first one' => ['image-list-of-objects.html', 'https://blog.example/first.jpg'];
        yield 'a list of references: the first one that resolves' => ['image-list-of-references.html', 'https://news.example/primary.jpg'];
        yield 'entries without a URL are skipped' => ['image-unusable-entries-first.html', 'https://news.example/usable.jpg'];
        yield 'no entry with a URL' => ['image-unusable-only.html', null];
    }

    #[DataProvider('imageForms')]
    public function testImageForms(string $fixture, ?string $expected): void
    {
        $meta = new SchemaParser()->parse((string) file_get_contents(__DIR__ . '/fixtures/schema/' . $fixture));

        self::assertNotNull($meta->getTitle());
        self::assertSame($expected, $meta->getOgImage());
    }
}

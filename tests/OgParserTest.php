<?php

declare(strict_types=1);

namespace Tomaj\Scraper\Test;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tomaj\Scraper\Author;
use Tomaj\Scraper\Meta;
use Tomaj\Scraper\Parser\OgParser;
use Tomaj\Scraper\Parser\SchemaParser;
use Tomaj\Scraper\Scraper;
use Tomaj\Scraper\Section;

final class OgParserTest extends TestCase
{
    public function testBasicInformation(): void
    {
        $data = <<<EOT
                        <title>Page title</title>
                        <meta name="description" content="Default page description"/>
                        <meta name="KEYWORDS"     content="Keyword1,Keyword2">
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

        $scraper = new Scraper();
        $meta = $scraper->parse($data, [new OgParser()]);

        self::assertSame('Page title', $meta->getTitle());
        self::assertSame('Default page description', $meta->getDescription());
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
        self::assertSame('Keyword1,Keyword2', $meta->getKeywords());
    }

    public function testEmpty(): void
    {
        $scraper = new Scraper();
        $meta = $scraper->parse('sdsadipojhafidsjf dsf ', [
            new OgParser(),
            new SchemaParser(),
        ]);
        self::assertNull($meta->getTitle());
        self::assertNull($meta->getDescription());
        self::assertCount(0, $meta->getAuthors());
        self::assertNull($meta->getOgType());
        self::assertNull($meta->getOgDescription());
        self::assertNull($meta->getOgUrl());
        self::assertNull($meta->getOgSiteName());
        self::assertNull($meta->getOgImage());
        self::assertCount(0, $meta->getSections());
    }

    public function testMoreAttributes(): void
    {
        $data = <<<EOT
                    <meta charset="utf-8" />
            <meta http-equiv="X-UA-Compatible" content="IE=edge" />
            <meta http-equiv="Content-Language" content="en" />
            <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
            <link rel="shortcut icon" href="/vi-assets/static-assets/favicon-4bf96cb6a1093748bf5b3c429accb9b4.ico" />
            <link rel="apple-touch-icon" href="/vi-assets/static-assets/apple-touch-icon-319373aaf4524d94d38aa599c56b8655.png" />
            <link rel="apple-touch-icon-precomposed" sizes="144×144" href="/vi-assets/static-assets/ios-ipad-144x144-319373aaf4524d94d38aa599c56b8655.png" />
            <link rel="apple-touch-icon-precomposed" sizes="114×114" href="/vi-assets/static-assets/ios-iphone-114x144-61d373c43aa8365d3940c5f1135f4597.png" />
            <link rel="apple-touch-icon-precomposed" href="/vi-assets/static-assets/ios-default-homescreen-57x57-7cccbfb151c7db793e92ea58c30b9e72.png" />
            <meta property="fb:app_id" content="9869919170" />
            <meta name="twitter:site" value="@nytimes" />
                <title data-rh="true">Spanish Court Blocks Election of Separatist Ex-Catalan Chief - The New York Times</title><meta data-rh="true" itemprop="inLanguage" content="en-US"/><meta data-rh="true" name="robots" content="noarchive"/><meta data-rh="true" name="articleid" itemprop="identifier" content="100000005891767"/><meta data-rh="true" name="description" itemprop="description" content="Plans by Catalan separatists to re-elect their region&#x27;s former president in absentia were blocked Wednesday by Spain&#x27;s Constitutional Court."/><meta data-rh="true" name="image" itemprop="image" content="https://static01.nyt.com/images/icons/t_logo_291_black.png"/><meta data-rh="true" name="byl" content="By The Associated Press"/><meta data-rh="true" name="thumbnail" itemprop="thumbnailUrl" content="https://static01.nyt.com/images/icons/t_logo_150_black.png"/><meta data-rh="true" name="news_keywords" content=""/><meta data-rh="true" name="usageTerms" itemprop="usageTerms" content="http://www.nytimes.com/content/help/rights/sale/terms-of-sale.html"/><meta data-rh="true" name="pdate" content="20180509"/><meta data-rh="true" name="utime" content="20180509153746"/><meta data-rh="true" name="ptime" content="20180509074352"/><meta data-rh="true" property="og:url" content="https://www.nytimes.com/aponline/2018/05/09/world/europe/ap-eu-spain-catalonia.html"/><meta data-rh="true" property="og:type" content="article"/><meta data-rh="true" property="og:title" content="Spanish Court Blocks Election of Separatist Ex-Catalan Chief OG"/><meta data-rh="true" property="og:image" content="https://static01.nyt.com/images/icons/t_logo_291_black.png"/><meta data-rh="true" property="og:description" content="Plans by Catalan separatists to re-elect their region&#x27;s former president in absentia were blocked Wednesday by Spain&#x27;s Constitutional Court. OG"/><meta data-rh="true" property="twitter:url" content="https://www.nytimes.com/aponline/2018/05/09/world/europe/ap-eu-spain-catalonia.html"/><meta data-rh="true" property="twitter:title" content="Spanish Court Blocks Election of Separatist Ex-Catalan Chief"/><meta data-rh="true" property="twitter:description" content="Plans by Catalan separatists to re-elect their region&#x27;s former president in absentia were blocked Wednesday by Spain&#x27;s Constitutional Court."/><meta data-rh="true" property="twitter:image" content="https://static01.nyt.com/images/icons/t_logo_150_black.png"/><meta data-rh="true" property="twitter:image:alt" content=""/><meta data-rh="true" property="twitter:card" content="summary_large_image"/><meta data-rh="true" property="article:section" itemprop="articleSection" content="World"/><meta data-rh="true" property="article:section-taxonomy-id" itemprop="articleSection" content="9A43D8FC-F4CF-44D9-9B34-138D30468F8F"/><meta data-rh="true" property="article:published" itemprop="datePublished" content="2018-05-09T11:43:52.000Z"/><meta data-rh="true" property="article:modified" itemprop="dateModified" content="2018-05-09T19:37:46.216Z"/><meta data-rh="true" name="CG" content="world"/><meta data-rh="true" name="SCG" content="europe"/><meta data-rh="true" name="CN" content=""/><meta data-rh="true" name="CT" content=""/><meta data-rh="true" name="PT" content="article"/><meta data-rh="true" name="PST" content="News"/><meta data-rh="true" name="genre" itemprop="genre" content="News"/><meta data-rh="true" name="msapplication-starturl" content="https://www.nytimes.com"/><meta data-rh="true" property="al:android:url" content="nytimes://reader/id/100000005891767"/><meta data-rh="true" property="al:android:package" content="com.nytimes.android"/><meta data-rh="true" property="al:android:app_name" content="NYTimes"/><meta data-rh="true" name="twitter:app:name:googleplay" content="NYTimes"/><meta data-rh="true" name="twitter:app:id:googleplay" content="com.nytimes.android"/><meta data-rh="true" name="twitter:app:url:googleplay" content="nytimes://reader/id/100000005891767"/><meta data-rh="true" property="al:iphone:url" content="nytimes://www.nytimes.com/aponline/2018/05/09/world/europe/ap-eu-spain-catalonia.html"/><meta data-rh="true" property="al:iphone:app_store_id" content="284862083"/><meta data-rh="true" property="al:iphone:app_name" content="NYTimes"/><meta data-rh="true" property="al:ipad:url" content="nytimes://www.nytimes.com/aponline/2018/05/09/world/europe/ap-eu-spain-catalonia.html"/><meta data-rh="true" property="al:ipad:app_store_id" content="357066198"/><meta data-rh="true" property="al:ipad:app_name" content="NYTimes"/>
            EOT;

        $scraper = new Scraper();
        $meta = $scraper->parse($data, [new OgParser()]);

        self::assertSame('Spanish Court Blocks Election of Separatist Ex-Catalan Chief - The New York Times', $meta->getTitle());


        self::assertSame("Plans by Catalan separatists to re-elect their region's former president in absentia were blocked Wednesday by Spain's Constitutional Court.", $meta->getDescription());

        self::assertSame("Plans by Catalan separatists to re-elect their region's former president in absentia were blocked Wednesday by Spain's Constitutional Court. OG", $meta->getOgDescription());

        self::assertSame('https://www.nytimes.com/aponline/2018/05/09/world/europe/ap-eu-spain-catalonia.html', $meta->getOgUrl());

        self::assertSame('Spanish Court Blocks Election of Separatist Ex-Catalan Chief OG', $meta->getOgTitle());

        self::assertSame('https://static01.nyt.com/images/icons/t_logo_291_black.png', $meta->getOgImage());
    }

    public function testNonStandardAttributes(): void
    {
        $data = <<<EOT
                    <html lang="en"><head><meta charSet="utf-8" class="next-head" /><meta http-equiv="x-ua-compatible" content="ie=edge" class="next-head" /><meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" class="next-head" /><link rel="preconnect" href="https://files.testsite.com" class="next-head" /><link rel="preconnect" href="//www.googletagmanager.com" class="next-head" /><meta property="og:image" content="https://testsite.com/static/cloud/img/splash_600x315_1.png?_=7f9e1cc" class="next-head" /><meta property="og:image:type" content="image/png" class="next-head" /><meta property="og:image:width" content="600" class="next-head" /><meta property="og:image:height" content="315" class="next-head" /><meta property="og:site_name" content="TestSite" class="next-head" /><meta property="og:type" content="website" class="next-head" /><meta name="twitter:card" content="summary_large_image" class="next-head" /><meta name="apple-itunes-app" content="app-id=128210709876" class="next-head" /><meta property="og:url" content="https://testsite.com/" class="next-head" /><link rel="canonical" href="https://testsite.com/" class="next-head" /><link rel="manifest" href="/manifest.json" class="next-head" /><meta name="description" content="Description for TestSite" class="next-head" /><meta property="og:description" my-attribute="foo" content="Description for TestSite" data-attribute="test" class="next-head" />
            EOT;

        $scraper = new Scraper();
        $meta = $scraper->parse($data, [new OgParser()]);
        self::assertSame('website', $meta->getOgType());
        self::assertSame('Description for TestSite', $meta->getOgDescription());
        self::assertSame('https://testsite.com/', $meta->getOgUrl());
        self::assertSame('https://testsite.com/static/cloud/img/splash_600x315_1.png?_=7f9e1cc', $meta->getOgImage());
    }

    public function testSingleQuotedMetaProperty(): void
    {
        $data = <<<EOT
                        <title>Page title</title>
                        <meta name='description' content='Default page description'/>
                        <meta name='KEYWORDS'     content='Keyword1,Keyword2'>
                        <META NAME='author' CONTENT='Jozko Pucik'  >
                        <meta property='og:type' content='article' />
                        <meta property='og:description' content='Silny popis' />
                        <meta property='og:title' content='Og title nadpis'/>
                        <meta property='og:url' content='https://web.sk/stranka.html'/>
                        <meta property='og:site_name' content='Mega site name' />
                        <meta property='article:section' content='Ekonomika' />
                        <meta property='og:image' content='https://obrazok.jpg'>
                        <meta property='article:published_time' content='2015-10-12T12:40:27+00:00' />
                        <meta property='article:modified_time' content='2016-11-13T13:21:42+00:00' />
            EOT;

        $scraper = new Scraper();
        $meta = $scraper->parse($data, [new OgParser()]);

        self::assertSame('Default page description', $meta->getDescription());
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
        self::assertSame('Keyword1,Keyword2', $meta->getKeywords());
    }

    public function testEmptyContent(): void
    {
        self::assertEquals(new Meta(), new OgParser()->parse(''));
    }

    /**
     * @return iterable<string, array{string, string, \Closure(Meta): ?string, string}>
     */
    public static function fields(): iterable
    {
        yield 'description' => ['NAME', 'description', static fn(Meta $m): ?string => $m->getDescription(), 'Value'];
        yield 'keywords' => ['NAME', 'keywords', static fn(Meta $m): ?string => $m->getKeywords(), 'Value'];
        yield 'author' => ['NAME', 'author', static fn(Meta $m): ?string => $m->getAuthors()[0]->name(), 'Value'];
        yield 'og:title' => ['PROPERTY', 'og:title', static fn(Meta $m): ?string => $m->getOgTitle(), 'Value'];
        yield 'og:description' => ['PROPERTY', 'og:description', static fn(Meta $m): ?string => $m->getOgDescription(), 'Value'];
        yield 'og:type' => ['PROPERTY', 'og:type', static fn(Meta $m): ?string => $m->getOgType(), 'Value'];
        yield 'og:url' => ['PROPERTY', 'og:url', static fn(Meta $m): ?string => $m->getOgUrl(), 'Value'];
        yield 'og:site_name' => ['PROPERTY', 'og:site_name', static fn(Meta $m): ?string => $m->getOgSiteName(), 'Value'];
        yield 'og:image' => ['PROPERTY', 'og:image', static fn(Meta $m): ?string => $m->getOgImage(), 'Value'];
        yield 'article:section' => ['PROPERTY', 'article:section', static fn(Meta $m): ?string => $m->getSections()[0]->name(), 'Value'];
        yield 'article:published_time' => ['PROPERTY', 'article:published_time', static fn(Meta $m): ?string => $m->getPublishedTime()?->format('Y-m-d'), '2020-02-03'];
        yield 'article:modified_time' => ['PROPERTY', 'article:modified_time', static fn(Meta $m): ?string => $m->getModifiedTime()?->format('Y-m-d'), '2020-02-03'];
    }

    /**
     * Upper-case tag and attribute names, a line break inside the tag and a following tag:
     * the patterns must be case-insensitive, match across lines and stop at the first closing quote.
     *
     * @param \Closure(Meta): ?string $getter
     */
    #[DataProvider('fields')]
    public function testFieldInUpperCaseMultilineTagFollowedByAnotherTag(string $attribute, string $value, \Closure $getter, string $expected): void
    {
        $html = "<HEAD>\n<META\n  {$attribute}=\"{$value}\"\n  CONTENT=\"{$expected}\">\n<meta name=\"other\" content=\"Other\">\n</HEAD>";

        self::assertSame($expected, $getter(new OgParser()->parse($html)));
    }

    public function testTitleInUpperCaseOnSeveralLines(): void
    {
        $meta = new OgParser()->parse("<TITLE lang=\"en\">First\nline</TITLE><p>body</p><title>Second</title>");

        self::assertSame("First\nline", $meta->getTitle());
    }

    public function testContentFirstTagsGiveNullInsteadOfTheNextTagsValue(): void
    {
        $meta = new OgParser()->parse($this->fixture('content-first.html'));

        self::assertSame('Attributes in any order', $meta->getTitle());
        self::assertNull($meta->getOgTitle());
        self::assertNull($meta->getOgUrl());
        self::assertNull($meta->getDescription());
        self::assertSame('Shop', $meta->getOgSiteName());
        self::assertSame('shop, products', $meta->getKeywords());
    }

    public function testEmptyContentGivesAnEmptyStringInsteadOfMarkup(): void
    {
        $meta = new OgParser()->parse($this->fixture('empty-content.html'));

        self::assertSame('', $meta->getTitle());
        self::assertSame('', $meta->getDescription());
        self::assertSame('first, second', $meta->getKeywords());
        self::assertSame('', $meta->getOgTitle());
        self::assertSame('product', $meta->getOgType());
    }

    public function testTagWithoutContentGivesNullInsteadOfTheNextTagsValue(): void
    {
        $meta = new OgParser()->parse($this->fixture('missing-content.html'));

        self::assertNull($meta->getOgImage());
        self::assertSame([], $meta->getAuthors());
        self::assertSame('https://shop.example/p/42', $meta->getOgUrl());
        self::assertSame('The real description', $meta->getDescription());
    }

    public function testQuotesAndGreaterThanInsideValues(): void
    {
        $meta = new OgParser()->parse($this->fixture('quotes-in-values.html'));

        self::assertSame("Don't panic", $meta->getTitle());
        self::assertSame("Don't panic", $meta->getOgTitle());
        self::assertSame('Say "hello" > goodbye', $meta->getOgDescription());
        self::assertSame('After an attribute with >', $meta->getDescription());
        self::assertSame('Guide', $meta->getOgSiteName());
    }

    public function testAttributeNamesMustMatchExactly(): void
    {
        $meta = new OgParser()->parse($this->fixture('prefixed-attributes.html'));

        self::assertSame('From property', $meta->getOgTitle());
        self::assertSame('From name', $meta->getDescription());
    }

    public function testTitleDoesNotRunIntoTheNextTitle(): void
    {
        self::assertSame('', new OgParser()->parse('<title></title><p>body</p><title>Second</title>')->getTitle());
        self::assertSame('Second', new OgParser()->parse('<titlex>no</titlex><title>Second</title>')->getTitle());
    }

    public function testStrayQuoteInAnEarlierTagDoesNotHideLaterTags(): void
    {
        // a quote only starts a value after "=", as in the HTML tokenizer; otherwise the expression ran on
        // through the rest of a large page and PCRE gave up, so every field came back null
        $body = '<body>' . str_repeat('<div class="c" id="d"><a href="/x">link</a></div>', 20000) . '</body></html>';
        $meta = new OgParser()->parse($this->fixture('stray-quote.html') . $body);

        self::assertSame(\PREG_NO_ERROR, preg_last_error());
        self::assertSame('first', $meta->getKeywords());
        self::assertSame('Read after a stray quote', $meta->getOgTitle());
        self::assertSame('Also read', $meta->getDescription());
    }

    private function fixture(string $name): string
    {
        return (string) file_get_contents(__DIR__ . '/fixtures/og/' . $name);
    }
}

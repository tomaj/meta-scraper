<?php

namespace Tomaj\Scraper;

use PHPUnit_Framework_TestCase;

require dirname(__FILE__) . '/../vendor/autoload.php';

class TestScraper extends PHPUnit_Framework_TestCase
{
    public function testBasicInformation()
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
        $meta = $scraper->parse($data, [new \Tomaj\Scraper\Parser\OgParser()]);

        $this->assertEquals('Page title', $meta->getTitle());
        $this->assertEquals('Default page description', $meta->getDescription());
        $this->assertEquals([['id' => null, 'name' => 'Jozko Pucik']], $meta->getAuthors());

        $this->assertEquals('article', $meta->getOgType());
        $this->assertEquals('Silny popis', $meta->getOgDescription());
        $this->assertEquals('https://web.sk/stranka.html', $meta->getOgUrl());
        $this->assertEquals('Mega site name', $meta->getOgSiteName());
        $this->assertEquals('Og title nadpis', $meta->getOgTitle());
        $this->assertEquals('https://obrazok.jpg', $meta->getOgImage());
        $this->assertEquals(['Ekonomika'], $meta->getSections());
        $this->assertEquals('12.10.2015 12:40:27', $meta->getPublishedTime()->format('d.m.Y H:i:s'));
        $this->assertEquals('13.11.2016 13:21:42', $meta->getModifiedTime()->format('d.m.Y H:i:s'));
        $this->assertEquals('Keyword1,Keyword2', $meta->getKeywords());
    }

    public function testEmpty()
    {
        $scraper = new Scraper();
        $meta = $scraper->parse("sdsadipojhafidsjf dsf ", [new \Tomaj\Scraper\Parser\OgParser()]);
        $this->assertNull($meta->getTitle());
        $this->assertNull($meta->getDescription());
        $this->assertEmpty($meta->getAuthors());
        $this->assertNull($meta->getOgType());
        $this->assertNull($meta->getOgDescription());
        $this->assertNull($meta->getOgUrl());
        $this->assertNull($meta->getOgSiteName());
        $this->assertNull($meta->getOgImage());
        $this->assertEmpty($meta->getSections());
    }

    public function testMoreAttributes()
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
        $meta = $scraper->parse($data, [new \Tomaj\Scraper\Parser\OgParser()]);

        $this->assertEquals('Spanish Court Blocks Election of Separatist Ex-Catalan Chief - The New York Times', $meta->getTitle());


        $this->assertEquals('Plans by Catalan separatists to re-elect their region&#x27;s former president in absentia were blocked Wednesday by Spain&#x27;s Constitutional Court.', $meta->getDescription());

        $this->assertEquals('Plans by Catalan separatists to re-elect their region&#x27;s former president in absentia were blocked Wednesday by Spain&#x27;s Constitutional Court. OG', $meta->getOgDescription());

        $this->assertEquals('https://www.nytimes.com/aponline/2018/05/09/world/europe/ap-eu-spain-catalonia.html', $meta->getOgUrl());

        $this->assertEquals('Spanish Court Blocks Election of Separatist Ex-Catalan Chief OG', $meta->getOgTitle());

        $this->assertEquals('https://static01.nyt.com/images/icons/t_logo_291_black.png', $meta->getOgImage());
    }

    public function testSchemaParser()
    {
        $data = <<<EOT
        text
        <script id="schema" type="application/ld+json">{"@context":"http://schema.org","@type":"NewsArticle","url":"https://dennikn.sk/1325802/vela-pohybu-a-malo-masa-pat-miest-kde-ludia-ziju-najdlhsie-ohrozuje-westernizacia/","position":1325802,"headline":"Veľa pohybu a málo mäsa. Päť miest, kde ľudia žijú najdlhšie, ohrozuje westernizácia","description":"Čo spája miesta na Zemi, kde ľudia žijú najdlhšie?","articleSection":["Svet","Veda"],"datePublished":"2018-12-15T19:00:26+00:00","dateModified":"2018-12-17T00:28:43+00:00","wordCount":1650,"mainEntityOfPage":{"@type":"WebPage","@id":"https://dennikn.sk/1325802/vela-pohybu-a-malo-masa-pat-miest-kde-ludia-ziju-najdlhsie-ohrozuje-westernizacia/"},"author":[{"@type":"Person","@id":"495","name":"Tomáš Vasilko"}],"publisher":{"@type":"Organization","name":"Denník N","logo":{"@type":"ImageObject","url":"https://dennikn.sk/wp-content/themes/dn-2-sk/dennikn-60x60.png","width":60,"height":60}},"image":{"@type":"ImageObject","url":"https://img.projektn.sk/wp-static/2018/12/XkJ05Z9kQFZoy6hlKrEcj8U3Aevn1wfu-6r8OMz0Ah4.jpg","width":756,"height":427,"thumbnail":{"@type":"ImageObject","url":"https://img.projektn.sk/wp-static/2018/12/XkJ05Z9kQFZoy6hlKrEcj8U3Aevn1wfu-6r8OMz0Ah4.jpg?fm=jpg&q=80&w=360&h=200&fit=crop","width":360,"height":200}},"isAccessibleForFree":false,"hasPart":[{"@type":"WebPageElement","isAccessibleForFree":"False","cssSelector":".a_single"}]}</script>
        text
EOT;

        $scraper = new Scraper();
        $meta = $scraper->parse($data, [new \Tomaj\Scraper\Parser\SchemaParser()]);

        $this->assertEquals('Veľa pohybu a málo mäsa. Päť miest, kde ľudia žijú najdlhšie, ohrozuje westernizácia', $meta->getTitle());

        $this->assertEquals('Čo spája miesta na Zemi, kde ľudia žijú najdlhšie?', $meta->getDescription());

        $this->assertEquals(null, $meta->getOgDescription());

        $this->assertEquals('https://dennikn.sk/1325802/vela-pohybu-a-malo-masa-pat-miest-kde-ludia-ziju-najdlhsie-ohrozuje-westernizacia/', $meta->getOgUrl());

        $this->assertEquals(['Svet', 'Veda'], $meta->getSections());

        $this->assertEquals(null, $meta->getOgTitle());

        $this->assertEquals('https://img.projektn.sk/wp-static/2018/12/XkJ05Z9kQFZoy6hlKrEcj8U3Aevn1wfu-6r8OMz0Ah4.jpg', $meta->getOgImage());

        $this->assertEquals([['id' => 495, 'name' => 'Tomáš Vasilko']], $meta->getAuthors());

        $this->assertEquals(new \DateTime('@' . strtotime('2018-12-15T19:00:26+00:00')), $meta->getPublishedTime());

        $this->assertEquals(new \DateTime('@' . strtotime('2018-12-17T00:28:43+00:00')), $meta->getModifiedTime());
    }

    public function testMerge()
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
            new \Tomaj\Scraper\Parser\SchemaParser(),
            new \Tomaj\Scraper\Parser\OgParser(),
        ]);

        $this->assertEquals('Veľa pohybu a málo mäsa. Päť miest, kde ľudia žijú najdlhšie, ohrozuje westernizácia', $meta->getTitle());

        $this->assertEquals('Čo spája miesta na Zemi, kde ľudia žijú najdlhšie?', $meta->getDescription());

        $this->assertEquals('Čo spája miesta na Zemi, kde ľudia žijú najdlhšie? OG', $meta->getOgDescription());

        $this->assertEquals('https://dennikn.sk/1325802/vela-pohybu-a-malo-masa-pat-miest-kde-ludia-ziju-najdlhsie-ohrozuje-westernizacia/', $meta->getOgUrl());

        $this->assertEquals(['Svet', 'Veda'], $meta->getSections());

        $this->assertEquals('Veľa pohybu a&nbsp;málo mäsa. Päť miest, kde ľudia žijú najdlhšie, ohrozuje westernizácia OG', $meta->getOgTitle());

        $this->assertEquals('https://img.projektn.sk/wp-static/2018/12/XkJ05Z9kQFZoy6hlKrEcj8U3Aevn1wfu-6r8OMz0Ah4.jpg', $meta->getOgImage());

        $this->assertEquals([['id' => 495, 'name' => 'Tomáš Vasilko']], $meta->getAuthors());

        $this->assertEquals(new \DateTime('@' . strtotime('2018-12-15T19:00:26+00:00')), $meta->getPublishedTime());

        $this->assertEquals(new \DateTime('@' . strtotime('2018-12-17T00:28:43+00:00')), $meta->getModifiedTime());

    }
}

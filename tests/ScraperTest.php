<?php

namespace Tomaj\Scraper;

use PHPUnit_Framework_TestCase;

require dirname(__FILE__). '/../vendor/autoload.php';

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
          <meta property="og:url" content="https://web.sk/stranka.html"/>
          <meta property="og:site_name" content="Mega site name" />
          <meta property="article:section" content="Ekonomika" />
          <meta property="og:image" content="https://obrazok.jpg">
          <meta property="article:published_time" content="2015-10-12T12:40:27+00:00" />
          <meta property="article:modified_time" content="2016-11-13T13:21:42+00:00" />
EOT;

      $scraper = new Scraper();
      $meta = $scraper->parse($data);

      $this->assertEquals('Page title', $meta->getTitle());
      $this->assertEquals('Default page description', $meta->getDescription());
      $this->assertEquals('Jozko Pucik', $meta->getAuthor());

      $this->assertEquals('article', $meta->getOgType());
      $this->assertEquals('Silny popis', $meta->getOgDescription());
      $this->assertEquals('https://web.sk/stranka.html', $meta->getOgUrl());
      $this->assertEquals('Mega site name', $meta->getOgSiteName());
      $this->assertEquals('https://obrazok.jpg', $meta->getOgImage());
      $this->assertEquals('Ekonomika', $meta->getSection());
      $this->assertEquals('12.10.2015 12:40:27', $meta->getPublishedTime()->format('d.m.Y H:i:s'));
      $this->assertEquals('13.11.2016 13:21:42', $meta->getModifiedTime()->format('d.m.Y H:i:s'));
    }

    public function testEmpty()
    {
        $scraper = new Scraper();
        $meta = $scraper->parse("sdsadipojhafidsjf dsf ");
        $this->assertNull($meta->getTitle());
        $this->assertNull($meta->getDescription());
        $this->assertNull($meta->getAuthor());
        $this->assertNull($meta->getOgType());
        $this->assertNull($meta->getOgDescription());
        $this->assertNull($meta->getOgUrl());
        $this->assertNull($meta->getOgSiteName());
        $this->assertNull($meta->getOgImage());
        $this->assertNull($meta->getSection());
    }
}

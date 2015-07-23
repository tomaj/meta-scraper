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
          <meta property="og:image" content="https://obrazok.jpg">
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

    }
}

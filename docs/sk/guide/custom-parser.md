---
description: Napíšte vlastný parser pre meta-scraper implementáciou ParserInterface - pravidlá, celý príklad na čítanie Twitter kariet a kombinovanie so vstavanými parsermi.
---

# Vlastný parser

Vstavané parsery čítajú meta tagy, Open Graph a JSON-LD. Na všetko ostatné (Twitter karty, mikrodáta, vlastné značkovanie webu, dáta v tele stránky) implementujte `ParserInterface`:

<!-- snippet: skip -->
```php
namespace Tomaj\Scraper\Parser;

use Tomaj\Scraper\Meta;

interface ParserInterface
{
    public function parse(string $content): Meta;
}
```

## Pravidlá {#rules}

- **Pri každom volaní vráťte nový `Meta`.** Jedna inštancia parsera sa používa na veľa stránok; `Meta` uložený vo vlastnosti by prenášal hodnoty z jednej stránky do ďalšej.
- **Pri zlom vstupe nevyhadzujte výnimky.** Vráťte to, čo sa dalo prečítať. Výnimku vyhoďte len pri programátorskej chybe, napríklad pri chýbajúcom rozšírení (a to v konštruktore, ako `OgDomParser`).
- **Nastavte len to, čo ste našli.** Nenastavené pole zostane `null` a môže ho vyplniť neskorší parser.
- **Nespoliehajte sa na iné parsery.** Každý parser dostane pôvodné HTML; výsledky zlučuje scraper.
- Vstavané triedy sú `final`: namiesto dedenia ich obaľte.

`Meta` nemá vlastné polia pre iné značkovanie. Prečítané hodnoty namapujte na najbližšie pole (titulok Twitter karty na `setOgTitle()`) alebo si vlastný výsledok držte vedľa `Meta`.

## Krátky parser

<<< @/snippets/custom-parser.php

Výstup:

<<< @/snippets/custom-parser.php.out{text}

## Úplný parser: Twitter karty

[Príklad s Twitter kartami](../examples/twitter-card.md) obsahuje parser postavený na DOM, ktorý číta `twitter:title`, `twitter:description` a `twitter:image` (aj v zápise `property="twitter:…"`), zvládne stránky v UTF-8 bez deklarácie kódovania a obnoví nastavenie chýb libxml:

<<< @/../examples/twitter-card/src/TwitterCardParser.php

Zaraďte ho za vstavané parsery, aby vyplnil len to, čo nenašli ony:

<!-- snippet: skip -->
```php
$parsers = [new OgDomParser(), new TwitterCardParser()];
```

## Obalenie vstavaného parsera

Ak chcete výsledok vstavaného parsera dodatočne upraviť, použite kompozíciu:

```php
final class AbsoluteImageParser implements ParserInterface
{
    public function __construct(
        private readonly string $pageUrl,
        private readonly ParserInterface $inner = new OgDomParser(),
    ) {}

    #[\Override]
    public function parse(string $content): Meta
    {
        $meta = $this->inner->parse($content);
        $image = $meta->getOgImage();
        if ($image !== null && str_starts_with($image, '/') && !str_starts_with($image, '//')) {
            $parts = parse_url($this->pageUrl);
            $meta->setOgImage($parts['scheme'] . '://' . $parts['host'] . $image);
        }

        return $meta;
    }
}

$meta = new AbsoluteImageParser('https://shop.example/p/1')->parse('<meta property="og:image" content="/img/1.jpg">');
echo $meta->getOgImage(), "\n";
```

<!-- snippet: output -->
```text
https://shop.example/img/1.jpg
```

## Testovanie parsera {#testing-a-parser}

Parser je čistá funkcia reťazca, takže ho testujte s HTML fixtures a bez siete:

```php
final class TwitterTitleParser implements ParserInterface
{
    #[\Override]
    public function parse(string $content): Meta
    {
        $meta = new Meta();
        if (preg_match('/<meta\s+name="twitter:title"\s+content="([^"]*)"/i', $content, $m) === 1) {
            $meta->setOgTitle(html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5));
        }

        return $meta;
    }
}

$parser = new TwitterTitleParser();
var_dump($parser->parse('<meta name="twitter:title" content="Tom &amp; Jerry">')->getOgTitle() === 'Tom & Jerry');
var_dump($parser->parse('')->getOgTitle() === null);
var_dump($parser->parse('<meta name="twitter:title" content="A">') !== $parser->parse('<meta name="twitter:title" content="A">'));
```

<!-- snippet: output -->
```text
bool(true)
bool(true)
bool(true)
```

Otestujte ho aj spolu so vstavanými parsermi cez `Scraper::parse()`, aby ste videli zlúčený výsledok, ktorý dostane vaša aplikácia. Pozrite si [Testovanie vlastného kódu](./testing.md).

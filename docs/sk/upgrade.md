---
description: Prechod z meta-scraper 4.x na 5.0 - PHP 8.4, FetchException, finálne triedy, typované options pre Scraper, požiadavky OgDomParser a zmeny vo výstupe parserov.
---

# Prechod zo 4.x na 5.0

Táto stránka uvádza zmeny vo verzii 5.0, ktoré si môžu vyžiadať úpravy vo vašej aplikácii. Úplný zoznam zmien je v [CHANGELOG.md](https://github.com/tomaj/meta-scraper/blob/master/CHANGELOG.md). Anglický originál je [UPGRADE-5.0.md](https://github.com/tomaj/meta-scraper/blob/master/UPGRADE-5.0.md).

Kontrolný zoznam:

- PHP 8.4 a Guzzle 7.15.2+ alebo 8: [Požiadavky](#requirements).
- `parseUrl()` vyhadzuje `FetchException` namiesto výnimiek Guzzle: [Výnimky](#parseurl-throws-fetchexception).
- Triedy sú `final`, hodnotové objekty `readonly`, `protected` členy zmizli: [Finálne triedy](#classes-are-final).
- Nastavenia (options) pre `Scraper` sú typované; nastavenia mimo zdokumentovaného zoznamu patria do vášho vlastného Guzzle klienta: [Nastavenia Scrapera](#scraper-options-and-your-own-guzzle-client).
- `OgDomParser` bez `ext-dom` vyhodí výnimku: [OgDomParser](#ogdomparser-requires-ext-dom-and-ext-libxml).
- Parsery pri niektorých stránkach vracajú iné (správne) hodnoty: [Výstup parserov](#parser-output-changes).

## Požiadavky {#requirements}

- PHP 8.4 alebo novšie (4.x bežala na PHP 7.1+).
- `guzzlehttp/guzzle` `^7.15.2 || ^8.0.1`. Guzzle 6 už nie je podporovaný; staršie vydania Guzzle 7 majú zverejnené bezpečnostné upozornenia.

## `parseUrl()` vyhadzuje `FetchException` {#parseurl-throws-fetchexception}

Predtým pri neúspešnom sťahovaní prenikla von výnimka Guzzle. Teraz je každé zlyhanie (chyba spojenia, timeout, chyba TLS, priveľa presmerovaní, odpoveď 4xx/5xx) `Tomaj\Scraper\Exception\FetchException`. Výnimka Guzzle je jej predchádzajúca výnimka (previous). Všetky výnimky balíka implementujú `Tomaj\Scraper\Exception\ScraperException`.

<!-- snippet: skip -->
```php
// 4.x
try {
    $meta = $scraper->parseUrl($url, $parsers);
} catch (\GuzzleHttp\Exception\GuzzleException $e) {
    // ...
}

// 5.0
try {
    $meta = $scraper->parseUrl($url, $parsers);
} catch (\Tomaj\Scraper\Exception\FetchException $e) {
    $guzzleException = $e->getPrevious();
    $failedUrl = $e->url;
}
```

## Triedy sú finálne {#classes-are-final}

`Scraper`, `Meta`, `Author`, `Section`, `Tag`, `OgParser`, `OgDomParser` a `SchemaParser` sú `final`; `Author`, `Section` a `Tag` sú `readonly`. `Scraper::$guzzleOptions`, `OgDomParser::$meta`, `OgDomParser::$allowedAttributes`, `OgDomParser::processMetaTag()` a `OgDomParser::fixMetaTag()` (všetky `protected`) sú odstránené.

Ak ste niektorú triedu rozširovali, použite namiesto toho kompozíciu alebo implementujte `ParserInterface`:

<!-- snippet: skip -->
```php
// 4.x
class MyParser extends OgParser
{
    public function parse(string $content): Meta
    {
        $meta = parent::parse($content);
        // ...
        return $meta;
    }
}

// 5.0
final class MyParser implements ParserInterface
{
    public function __construct(private readonly OgParser $og = new OgParser()) {}

    public function parse(string $content): Meta
    {
        $meta = $this->og->parse($content);
        // ...
        return $meta;
    }
}
```

Ak ste predvolené options pre Guzzle menili prepísaním `Scraper::$guzzleOptions`, odovzdajte ich namiesto toho konštruktoru (ďalšia sekcia).

## Nastavenia Scrapera a vlastný Guzzle klient {#scraper-options-and-your-own-guzzle-client}

`new Scraper(array $guzzleOptions = [])` stále funguje a options sa stále zlučujú s predvolenými hodnotami (timeout spojenia 5 sekúnd, User-Agent `Tomaj\Scraper`). Pole options je teraz typované pre statickú analýzu: `connect_timeout`, `timeout`, `read_timeout`, `headers`, `proxy`, `allow_redirects`, `verify`, `http_errors`, `decode_content`, `force_ip_resolve` a `version`. Ostatné options sa za behu do Guzzle stále dostanú, ale PHPStan a Psalm ich nahlásia. Pre ne odovzdajte vlastného klienta ako druhý argument; predvolené hodnoty sa naň neuplatnia a z poľa options sa stanú request options:

<!-- snippet: skip -->
```php
// 4.x
$scraper = new Scraper(['handler' => $stack, 'cookies' => true]);

// 5.0
$scraper = new Scraper([], new \GuzzleHttp\Client([
    'handler' => $stack,
    'cookies' => true,
    'connect_timeout' => 5,
    'headers' => ['User-Agent' => 'MyBot/1.0'],
]));
```

Klient sa vytvorí raz v konštruktore, nie pri každom volaní `parseUrl()`.

## OgDomParser vyžaduje ext-dom a ext-libxml {#ogdomparser-requires-ext-dom-and-ext-libxml}

Bez `ext-dom` alebo `ext-libxml` sa `new OgDomParser()` predtým vytvoril a zlyhal až v `parse()` (alebo nič nenašiel). Teraz vyhodí `Tomaj\Scraper\Exception\MissingExtensionException` (`LogicException`). Tam, kde rozšírenia nie sú k dispozícii, použite `OgParser`.

## Zmeny vo výstupe parserov {#parser-output-changes}

Ide o opravy chýb, ale menia to, čo parsery vracajú:

- `OgDomParser` vracia pri každom volaní `parse()` nový `Meta`. Predtým jedna inštancia parsera vracala zakaždým ten istý objekt a autori aj sekcie sa naprieč stránkami hromadili ([#21](https://github.com/tomaj/meta-scraper/issues/21)). Ak ste sa na toto hromadenie spoliehali, zlučujte výsledky sami cez `Meta::merge()`.
- `OgDomParser` číta dokumenty bez deklarácie kódovania ako UTF-8. Predtým sa ich text s diakritikou vrátil pokazený (`Å½ltÃ½` namiesto `Žltý`) ([#22](https://github.com/tomaj/meta-scraper/issues/22)).
- `SchemaParser` číta štandardné JSON-LD (`<script type="application/ld+json">` bez `id="schema"`, JSON na viacerých riadkoch, viac blokov, `@graph`, odkazy `{"@id": …}`). Stránky, pri ktorých `SchemaParser` predtým vrátil prázdny výsledok, teraz môžu vrátiť titulok, autorov, dátumy a ďalšie. Keď `SchemaParser` beží prvý, jeho hodnoty majú prednosť pred hodnotami z Open Graph, takže `getTitle()` teraz môže byť titulok článku namiesto `<title>` ([#23](https://github.com/tomaj/meta-scraper/issues/23)). Stránky s blokom `id="schema"` ho naďalej používajú.
- `OgDomParser` po parsovaní obnoví pôvodné nastavenie `libxml_use_internal_errors()`; 4.x nechávala interné chyby zapnuté po zvyšok behu procesu.
- `OgParser` vráti `null` pre tag, ktorý nevie prečítať (`content` pred `name`/`property`, chýbajúci `content`), a prázdny reťazec pre `content=""`. 4.x namiesto toho vrátila obsah neskoršieho tagu alebo kus značkovania. Hodnoty s úvodzovkou druhého typu (`content="Don't"`) sa na nej už neorežú ([#29](https://github.com/tomaj/meta-scraper/issues/29)).
- `OgParser` dekóduje každú HTML entitu (`&eacute;`, `&hellip;`, `&nbsp;`, `&#8211;` …), nielen `&amp;`, `&lt;`, `&gt;`, `&quot;` a `&#039;`. Kód, ktorý hodnoty dekódoval ešte raz, teraz môže dekódovať text, ktorý stránka myslela doslovne; `&nbsp;` sa zmení na U+00A0, nie na obyčajnú medzeru ([#35](https://github.com/tomaj/meta-scraper/issues/35)).

## Pozrite aj

- [Zoznam zmien](./changelog.md): každá zmena vo verzii 5.0.
- [Spracovanie chýb](./guide/errors.md): `FetchException` a výnimky Guzzle za ňou.
- [Prehľad konfigurácie](./guide/configuration.md): typované options pre `Scraper`.
- [Výber parsera](./guide/parsers.md): `OgDomParser` a jeho požiadavky.

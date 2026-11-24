---
description: Sedem spustiteľných príkladov meta-scraper - Open Graph, JSON-LD @graph, parser Twitter kariet, poškodené stránky, chyby pri sťahovaní, náhľad odkazu bezpečný voči SSRF a testovanie - každý testovaný v CI.
---

# Príklady

Každý príklad je malý Composer projekt v priečinku [`examples/`](https://github.com/tomaj/meta-scraper/tree/master/examples) repozitára. Sťahuje testovacie stránky, ktoré servíruje vstavaný webový server PHP (`php -S`) na `127.0.0.1`, takže nepotrebuje internet a zakaždým dá rovnaký výstup. Stránky tohto webu načítavajú kód priamo z týchto súborov.

| Príklad | Čo ukazuje |
| --- | --- |
| [Článok s Open Graph](./open-graph-article.md) | Každý meta tag a každé pole Open Graph spravodajského článku, `OgDomParser` a `OgParser` vedľa seba |
| [JSON-LD @graph](./json-ld-graph.md) | `@graph` v štýle Yoast s odkazmi `@id`; ako poradie parserov rozhoduje, ktorá hodnota platí |
| [Parser Twitter kariet](./twitter-card.md) | Vlastný `ParserInterface`, ktorý číta tagy `twitter:*` ako záložný zdroj |
| [Poškodené stránky](./broken-pages.md) | Prehodené atribúty, tagy bez úvodzoviek, neplatné JSON-LD, UTF-8 bez deklarácie, Windows-1250, prázdna stránka |
| [Chyby pri sťahovaní](./fetch-errors.md) | 404, 500, timeout, slučka presmerovaní, odmietnuté spojenie, `http_errors` a `allow_redirects` |
| [Náhľad odkazu (bezpečný voči SSRF)](./link-preview.md) | Karty náhľadu pre URL zadané používateľmi: kontrola URL, pevne nastavené DNS, kontrolované presmerovania, escapovaný výstup |
| [Testovanie s mockom](./testing.md) | Unit testy kódu, ktorý volá `parseUrl()`, s Guzzle `MockHandler` |

## Ako sa testujú

Každý príklad má:

- `composer.json`, ktorý vyžaduje `tomaj/meta-scraper`,
- `run.php`, samotný príklad, ktorý vypíše, čo našiel,
- `site/` s testovacími stránkami (a `router.php` tam, kde stránka potrebuje zvláštny stavový kód, hlavičku alebo oneskorenie),
- `e2e.sh`, ktorý spustí `php -S` pre `site/`, spustí `run.php` a porovná jeho výstup so súborom
- `expected-output.txt`.

[Workflow Examples](https://github.com/tomaj/meta-scraper/actions/workflows/examples.yml) spúšťa každý `e2e.sh` pri každom pushi a pull requeste. [`examples/ci/use-local-meta-scraper.sh`](https://github.com/tomaj/meta-scraper/blob/master/examples/ci/use-local-meta-scraper.sh) nainštaluje balík z checkoutu cez Composer path repository, takže príklady vždy bežia proti kódu pull requestu, nie proti vydanej verzii. Keď zmena v knižnici zmení výstup príkladu, workflow zlyhá a ukáže rozdiel.

## Spustite si ho sami

```bash
git clone https://github.com/tomaj/meta-scraper.git
cd meta-scraper
examples/ci/recipe.sh link-preview
```

`recipe.sh` skopíruje príklad do dočasného priečinka, nainštaluje ho proti vášmu checkoutu a spustí jeho `e2e.sh`. Potrebuje PHP 8.4+, Composer a voľné porty 8401 až 8406.

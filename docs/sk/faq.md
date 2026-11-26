---
description: Časté otázky o meta-scraper - výber parsera, titulky, kódovanie, stránky s JavaScriptom, relatívne URL, Twitter karty, testovanie a bezpečnosť.
---

# Časté otázky

## Ktorý parser mám použiť?

Pre spravodajské a blogové články `[new SchemaParser(), new OgDomParser()]`: JSON-LD má najčistejší titulok, autorov a dátumy, Open Graph doplní zvyšok. Pre náhľady odkazov dajte na prvé miesto `OgDomParser`. `OgParser` použite len tam, kde nie je k dispozícii `ext-dom`. Pozrite [Výber parsera](./guide/parsers.md).

## Prečo sa `getTitle()` líši od tagu `<title>`?

Platí titulok, ktorý nájde prvý parser. `SchemaParser` použije `headline` z JSON-LD, ktorému zvyčajne chýba prípona „ | Názov webu“ z `<title>`. Ak chcete `<title>`, dajte na prvé miesto `OgDomParser`. A `getOgTitle()` je iné pole: tag `og:title`. Pozrite [Kombinovanie parserov](./guide/combining.md).

## Prečo je pole `null`, hoci ho stránka obsahuje?

Overte, či ide o tag, ktorý parsery čítajú ([zoznam](./guide/open-graph.md#tags-that-are-read)): `<meta name="og:title">` (s `name` namiesto `property`), `twitter:*`, `og:image:width` ani `article:tag` sa nečítajú. Pri JSON-LD overte, či je uzol typu článok alebo webová stránka a či je `image` objekt s `url`, nie obyčajný reťazec ([schema.org JSON-LD](./guide/json-ld.md#fields)). Pri `OgParser` skontrolujte poradie atribútov.

## Prečo sú znaky s diakritikou pokazené?

`OgParser` a `SchemaParser` vracajú bajty stránky tak, ako sú. Ak stránka nie je v UTF-8 (napríklad Windows-1250), pred parsovaním ju preveďte cez `iconv()`; `OgDomParser` deklarované kódovanie dekóduje sám. Pozrite [Kódovanie](./guide/fetching.md#character-sets) a [príklad s poškodenými stránkami](./examples/broken-pages.md).

## Dá sa spracovať stránka, ktorá potrebuje JavaScript?

Nie. meta-scraper číta HTML, ktoré vráti server. Stránky, ktoré si metadáta skladajú až v prehliadači, najprv vykreslite v headless prehliadači a výsledné HTML odovzdajte do `Scraper::parse()`.

## Prečo je `og:image` relatívna URL?

Parsery vracajú hodnotu tak, ako ju stránka zapísala. Vyriešte ju voči URL stránky sami; robí to [príklad s náhľadom odkazu](./examples/link-preview.md), ktorý navyše zahodí URL, ktoré nie sú http(s).

## Ako prečítam Twitter karty, mikrodáta alebo iné značkovanie?

Napíšte si parser: jedna trieda s jednou metódou. Pozrite [Vlastný parser](./guide/custom-parser.md) a [príklad s Twitter kartami](./examples/twitter-card.md).

## V akom časovom pásme sú dátumy?

V posune, ktorý zapísala stránka: `2026-11-20T06:30:00+01:00` sa vráti ako `DateTimeImmutable` s `+01:00`. Dátum bez posunu je v predvolenom časovom pásme PHP. Na zobrazenie ho preveďte cez `setTimezone()`. Pozrite [Dátumy](./guide/meta.md#dates).

## Ako nastavím timeout alebo User-Agent?

Odovzdajte konštruktoru options pre Guzzle: `new Scraper(['timeout' => 10, 'headers' => ['User-Agent' => 'MyBot/1.0']])`. Pole `headers` nahradí predvolené hlavičky. Pozrite [Prehľad konfigurácie](./guide/configuration.md).

## Ako pridám opakovanie požiadaviek, cookies alebo logovanie?

Nakonfigurujte Guzzle klienta s middlewarom alebo options, ktoré potrebujete, a odovzdajte ho ako druhý argument konštruktora. Pozrite [Vlastný Guzzle klient](./guide/fetching.md#your-own-guzzle-client).

## Ako otestujem kód, ktorý používa `parseUrl()`?

Dajte `Scraper` Guzzle klienta s `MockHandler`. Pozrite [Testovanie vlastného kódu](./guide/testing.md) a [príklad s testovaním](./examples/testing.md).

## Je bezpečné sťahovať URL, ktoré zadajú používatelia?

Len s ochranou proti SSRF (podvrhnutiu požiadavky na strane servera) a len ak escapujete všetko, čo zobrazujete. Pozrite [Bezpečnosť](./guide/security.md) a [príklad s náhľadom odkazu](./examples/link-preview.md).

## Funguje s Guzzle 7 aj 8?

Áno: `^7.15.2 || ^8.0.1`. Triedy výnimiek Guzzle v `FetchException::getPrevious()` sa medzi verziami líšia; pozrite [Spracovanie chýb](./guide/errors.md#fetchexception).

## Kde je dokumentácia k 4.x?

Verzia 4.x je opísaná v jej [README](https://github.com/tomaj/meta-scraper/blob/4.3.0/README.md). Rozdiely nájdete v [Prechod zo 4.x na 5.0](./upgrade.md).

---
description: Tri parsery meta-scrapera - OgParser, OgDomParser a SchemaParser - čo každý číta, čo potrebuje a v akom poradí ich použiť.
---

# Výber parsera

Parser implementuje `Tomaj\Scraper\Parser\ParserInterface`: dostane HTML ako reťazec a vráti nový `Meta`. Balík obsahuje tri:

| | `OgParser` | `OgDomParser` | `SchemaParser` |
| --- | --- | --- | --- |
| Číta | `<title>`, meta tagy, Open Graph | `<title>`, meta tagy, Open Graph | schema.org JSON-LD |
| Ako | regulárne výrazy | PHP DOM (`DOMDocument`) | regulárny výraz na bloky `<script>`, `json_decode()` |
| Potrebuje | nič | `ext-dom`, `ext-libxml` | `ext-json` (vždy zapnuté) |
| Poradie atribútov | `name`/`property` musí byť pred `content` | ľubovoľné | ľubovoľné |
| Opakované tagy (`article:section`) | len prvý | všetky | zoznamy v JSON |
| Kódovanie | bajty tak, ako sú | deklarované kódovanie sa dekóduje, inak UTF-8 | bajty tak, ako sú |
| Autori, sekcie, tagy s `@id` | nie | nie | áno |
| Stránka | [Meta tagy a Open Graph](./open-graph.md) | [Meta tagy a Open Graph](./open-graph.md) | [schema.org JSON-LD](./json-ld.md) |

## Ktoré a v akom poradí

Poradie rozhoduje, ktorá hodnota platí, keď dva parsery nájdu to isté pole ([Kombinovanie parserov](./combining.md)).

| Použitie | Parsery |
| --- | --- |
| Novinové a blogové články: čisté titulky, štruktúrovaní autori s `@id`, dátumy | `[new SchemaParser(), new OgDomParser()]` |
| Náhľady odkazov: čo stránka zobrazí pri zdieľaní na sociálnych sieťach | `[new OgDomParser(), new SchemaParser()]` |
| Bez `ext-dom` alebo rýchlosť pred presnosťou pri korektných stránkach | `[new SchemaParser(), new OgParser()]` |
| Stránky len s Twitter kartami alebo iným značkovaním | na koniec pridajte [vlastný parser](./custom-parser.md) |

Na meta tagy je lepšou voľbou `OgDomParser` vždy, keď je `ext-dom` k dispozícii: `OgParser` sa spolieha na poradie atribútov a keď stránka zapíše `content` pred `property`, môže vrátiť hodnotu **iného** tagu (pozrite si [príklad s poškodenými stránkami](../examples/broken-pages.md)). `OgParser` zostáva pre prostredia bez `ext-dom` a kvôli spätnej kompatibilite.

## Vytváranie parserov

Všetky tri majú konštruktor bez argumentov a medzi volaniami si nedržia stav, takže ich vytvorte raz a používajte opakovane:

```php
$parsers = [new SchemaParser(), new OgDomParser()];

foreach ([$html, $html] as $page) {
    echo new Scraper()->parse($page, $parsers)->getTitle(), "\n";
}
```

<!-- snippet: output -->
```text
Ten tips for PHP 8.4
Ten tips for PHP 8.4
```

`new OgDomParser()` vyhodí `MissingExtensionException`, keď `ext-dom` alebo `ext-libxml` nie je načítané, takže chýbajúce rozšírenie sa prejaví už pri vytvorení parsera, nie až neskôr ako prázdne výsledky.

Parser sa dá použiť aj bez `Scraper`:

```php
$meta = new SchemaParser()->parse($html);
echo $meta->getAuthors()[0]->id(), "\n";
```

<!-- snippet: output -->
```text
https://blog.example/#jana
```

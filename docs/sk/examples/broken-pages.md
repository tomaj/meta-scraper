---
description: Čo vracajú parsery meta-scraper pri poškodených a nezvyčajných stránkach - prehodené atribúty, prázdny content, tagy bez úvodzoviek, neplatné JSON-LD, chýbajúce alebo staré kódovanie a prázdne telo.
---

# Príklad: poškodené stránky

Skutočné stránky nie sú vždy správne zapísané. Tento príklad pustí parsery na šesť testovacích stránok, z ktorých každá niečo porušuje, a vypíše, čo každý parser vrátil. Ani jeden nevyhodí výnimku.

<ProjectSource name="broken-pages" :files="['run.php', 'site/reversed.html', 'site/unquoted.html', 'site/invalid-json-ld.html', 'site/windows-1250.html', 'site/router.php', 'expected-output.txt']" />

## Stránky

::: code-group

<<< @/../examples/broken-pages/site/reversed.html [reversed.html]

<<< @/../examples/broken-pages/site/unquoted.html [unquoted.html]

<<< @/../examples/broken-pages/site/invalid-json-ld.html [invalid-json-ld.html]

<<< @/../examples/broken-pages/site/router.php [router.php]

:::

`no-charset.html` je stránka v UTF-8 bez akejkoľvek deklarácie kódovania, `windows-1250.html` je v kódovaní Windows-1250 a deklaruje ho (súbor nie je platné UTF-8, preto ho tu neukazujeme; `router.php` ho posiela so zodpovedajúcim `Content-Type`) a `empty.html` je prázdny súbor.

## Kód

<<< @/../examples/broken-pages/run.php

## Výstup

<<< @/../examples/broken-pages/expected-output.txt{text}

## Čo si všimnúť

- **`reversed.html`: `OgParser` vynechá tagy s `content` na začiatku.** Keď je `content` pred `property`, `OgParser` tag neprečíta a pre `og:title` aj `og:url` vráti `null`; hodnotu susedného tagu nikdy nevezme. `OgDomParser` prečíta všetky tri. Preto je `OgDomParser` odporúčaný parser pre stránky, ktoré nemáte pod kontrolou; pozrite [Výber parsera](../guide/parsers.md). Pre prázdny `og:description` oba parsery vrátia prázdny reťazec a `Meta::merge()` berie prázdny reťazec ako chýbajúcu hodnotu, preto je výsledok `null`.
- **`unquoted.html`:** tagy veľkými písmenami a hodnoty atribútov bez úvodzoviek `OgDomParser` nevadia; `OgParser` potrebuje úvodzovky a nájde len titulok.
- **`invalid-json-ld.html`:** blok s čiarkou na konci sa preskočí a použije sa ďalší blok. Autor zadaný ako obyčajný reťazec sa prečíta; `"datePublished": "not a date"` sa ignoruje.
- **`no-charset.html`:** oba parsery vrátia správne UTF-8: `OgParser` bajty len odovzdá ďalej, `OgDomParser` číta dokumenty bez deklarácie ako UTF-8.
- **`windows-1250.html`:** `OgParser` vráti bajty vo Windows-1250, ktoré nie sú platné UTF-8. `OgDomParser` deklarované kódovanie dekóduje. Ak telo najprv prevediete cez `iconv()`, `OgParser` to opraví (a opravilo by to aj `SchemaParser`); `mb_convert_encoding()` Windows-1250 nepodporuje.
- **`empty.html`:** každé pole je `null`, každý zoznam prázdny.

## Čo kontroluje CI

Každú hodnotu vyššie vrátane hodnôt `null` z `OgParser` pri `reversed.html`, takže zmena správania parserov na poškodenom vstupe sa prejaví ako zlyhaný príklad.

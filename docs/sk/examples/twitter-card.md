---
description: Vlastný parser pre meta-scraper, ktorý číta tagy Twitter (X) kariet ako záložný zdroj k Open Graph, v kombinácii s OgDomParser.
---

# Príklad: parser Twitter kariet

Vstavané parsery tagy `twitter:*` nečítajú. Niektoré stránky majú len Twitter kartu a žiadny Open Graph. Tento príklad pridá parser pre kartu a použije ho ako záložný zdroj za `OgDomParser`.

<ProjectSource name="twitter-card" :files="['src/TwitterCardParser.php', 'run.php', 'site/card-only.html', 'site/both.html', 'expected-output.txt']" />

## Parser

<<< @/../examples/twitter-card/src/TwitterCardParser.php

Dodržiava [pravidlá pre parsery](../guide/custom-parser.md#rules): pri každom volaní nový `Meta`, pri zlom značkovaní žiadna výnimka, nastavené len nájdené hodnoty. Kartu mapuje na polia Open Graph, ktoré sú v `Meta` najbližšie.

## Stránky

::: code-group

<<< @/../examples/twitter-card/site/card-only.html [card-only.html]

<<< @/../examples/twitter-card/site/both.html [both.html]

:::

## Kód

<<< @/../examples/twitter-card/run.php

## Výstup

<<< @/../examples/twitter-card/expected-output.txt{text}

## Čo si všimnúť

- So samotným `OgDomParser` má stránka len s kartou `<title>` a nič iné.
- S parserom kariet za ním sa polia Open Graph doplnia z karty. `&amp;` v popise dekóduje DOM parser.
- Na stránke, ktorá má oboje, platí pre titulok a obrázok Open Graph, pretože `OgDomParser` beží prvý; karta doplní len popis, ktorý stránka má iba ako `twitter:description`.

## Čo kontroluje CI

Tri výsledky vyššie: bez parsera nič z karty, karta ako záložný zdroj a prednosť Open Graph.

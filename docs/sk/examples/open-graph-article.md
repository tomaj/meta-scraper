---
description: Ako s meta-scraper prečítať každý meta tag a každú vlastnosť Open Graph spravodajského článku - OgDomParser a OgParser porovnané na tej istej stránke.
---

# Príklad: článok s Open Graph

Spravodajský článok s bežnými tagmi: `<title>`, description, keywords, author, Open Graph, dva tagy `article:section` a čas publikovania a úpravy. `run.php` ho stiahne cez `parseUrl()` a vypíše každé pole, najprv s `OgDomParser`, potom s `OgParser` nad tým istým HTML z `getBody()`.

<ProjectSource name="open-graph-article" :files="['run.php', 'site/article.html', 'e2e.sh', 'expected-output.txt']" />

## Stránka

<<< @/../examples/open-graph-article/site/article.html

## Kód

<<< @/../examples/open-graph-article/run.php

## Výstup

<<< @/../examples/open-graph-article/expected-output.txt{text}

## Čo si všimnúť

- **Časy sú v UTC.** Z `2026-11-20T06:30:00+01:00` sa stane `2026-11-20T05:30:00+00:00`: `Meta` ukladá dátumy prečítané cez `strtotime()` ako objekty `DateTime` v UTC. Pozrite [Dátumy](../guide/meta.md#dates).
- **Sekcie:** stránka má dva tagy `article:section`. `OgDomParser` vráti oba, `OgParser` len prvý. Všetko ostatné je pri tejto správne zapísanej stránke rovnaké.
- **Text s diakritikou** (`Račianske mýto`, `Kováčová`) prejde nezmenený: stránka je v UTF-8 a deklaruje to.
- **`twitter:card` sa ignoruje**: tagy Twitteru nečíta žiadny vstavaný parser. [Príklad s Twitter kartami](./twitter-card.md) pre ne pridáva parser.
- `getBody()` vracia HTML z posledného sťahovania, takže druhé parsovanie nepotrebuje druhú požiadavku.

## Čo kontroluje CI

Výstup sa musí presne zhodovať s `expected-output.txt`: každé pole prečítané parsermi `OgDomParser` a `OgParser`, prevod oboch dátumov do UTC a rozdiel v sekciách.

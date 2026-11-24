---
layout: home
title: Meta Scraper – Open Graph a JSON-LD metadáta pre PHP
titleTemplate: false
description: 'PHP knižnica, ktorá z ľubovoľnej HTML stránky prečíta titulok, popis, Open Graph tagy a schema.org JSON-LD do jedného objektu Meta. Stiahne stránku cez Guzzle, vracia typované hodnoty a parsery môžete kombinovať alebo si napísať vlastné.'
hero:
  name: Meta Scraper
  text: Metadáta stránky pre PHP v jednom objekte
  tagline: 'Titulok, popis, autori, obrázok, dátumy, sekcie a tagy z meta tagov, Open Graph a schema.org JSON-LD ľubovoľnej HTML stránky. Kombinujte parsery, sťahujte cez Guzzle a dostanete späť jeden objekt Meta.'
  image:
    light: /illustrations/hero-sk-light.svg
    dark: /illustrations/hero-sk-dark.svg
    alt: HTML stránka s titulkom, Open Graph a JSON-LD tagmi prejde cez SchemaParser, OgDomParser a váš vlastný parser a stane sa z nej jeden objekt Meta s titulkom, obrázkom, autormi a dátumom publikovania
  actions:
    - theme: brand
      text: Začíname
      link: /sk/guide/getting-started
    - theme: alt
      text: Príklady
      link: /sk/examples/
    - theme: alt
      text: GitHub
      link: https://github.com/tomaj/meta-scraper
features:
  - icon: '<svg class="ms-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" width="44" height="44" aria-hidden="true" focusable="false"><rect class="ms-soft" x="4" y="6" width="30" height="36" rx="6"/><path class="ms-ink" d="M11 15h16M11 22h12M11 29h8"/><path class="ms-acc-f" d="M24 36.5l6-6.5h12.5a2.5 2.5 0 0 1 2.5 2.5v8a2.5 2.5 0 0 1-2.5 2.5H30z"/><circle class="ms-ink-f" cx="30.5" cy="36.5" r="2"/></svg>'
    title: Meta tagy a Open Graph
    details: '&lt;title&gt;, description, keywords a author, og:title, og:description, og:type, og:url, og:site_name, og:image, article:section a čas publikovania aj úpravy.'
    link: /sk/guide/open-graph
    linkText: OgParser a OgDomParser
  - icon: '<svg class="ms-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" width="44" height="44" aria-hidden="true" focusable="false"><rect class="ms-soft" x="3" y="5" width="34" height="34" rx="7"/><path class="ms-ink" d="M14 13c-3 0-3 2-3 4.5S10 21 8.5 21c1.5 0 2.5 1 2.5 3.5s0 4.5 3 4.5M26 13c3 0 3 2 3 4.5s1 3.5 2.5 3.5c-1.5 0-2.5 1-2.5 3.5s0 4.5-3 4.5"/><circle class="ms-acc-f" cx="36" cy="36" r="9.5"/><path class="ms-on-acc" d="M32 36h8M36 32v8"/></svg>'
    title: schema.org JSON-LD
    details: 'Každý blok application/ld+json, polia @graph a odkazy {"@id"}. Vyberie článok (NewsArticle, BlogPosting, …) alebo uzol webovej stránky a zvyšok ignoruje.'
    link: /sk/guide/json-ld
    linkText: SchemaParser
  - icon: '<svg class="ms-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" width="44" height="44" aria-hidden="true" focusable="false"><rect class="ms-soft" x="4" y="5" width="26" height="10" rx="4"/><rect class="ms-soft" x="4" y="19" width="26" height="10" rx="4"/><rect class="ms-soft" x="4" y="33" width="26" height="10" rx="4"/><path class="ms-ink" d="M9 10h12M9 24h8M9 38h14"/><path class="ms-acc" d="M34 10c6 0 8 4 8 14s-2 14-8 14M38 20l4 4 4-4"/></svg>'
    title: Kombinovanie parserov
    details: 'Spustite viac parserov v poradí, ktoré si zvolíte. Platí prvá nájdená hodnota, ďalšie parsery doplnia len to, čo ešte chýba.'
    link: /sk/guide/combining
    linkText: Ako funguje zlúčenie
  - icon: '<svg class="ms-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" width="44" height="44" aria-hidden="true" focusable="false"><circle class="ms-soft" cx="20" cy="20" r="16"/><path class="ms-ink" d="M4.5 20h31M20 4.5c5 4.5 7 9.5 7 15.5s-2 11-7 15.5M20 4.5c-5 4.5-7 9.5-7 15.5s2 11 7 15.5"/><circle class="ms-acc-f" cx="36" cy="36" r="9.5"/><path class="ms-on-acc" d="M36 31v9M32 36.5l4 4 4-4"/></svg>'
    title: Sťahovanie cez Guzzle
    details: 'parseUrl() stiahne stránku cez Guzzle 7 alebo 8 s vašimi timeoutmi, hlavičkami a proxy, alebo cez vlastného nakonfigurovaného klienta. Každé zlyhanie je jedna FetchException.'
    link: /sk/guide/fetching
    linkText: Sťahovanie stránok
  - icon: '<svg class="ms-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" width="44" height="44" aria-hidden="true" focusable="false"><path class="ms-soft" d="M20 3l16 6v11c0 10.5-6.5 18-16 22C10.5 38 4 30.5 4 20V9z"/><path class="ms-ink" d="M12 21l6 6 10-11"/><circle class="ms-acc-f" cx="36" cy="36" r="9.5"/><path class="ms-on-acc" d="M36 31.5v5.5M36 40.5v.1"/></svg>'
    title: Zlé HTML ho nezhodí
    details: 'Neuzavreté tagy, neplatný JSON-LD, prázdne stránky a staré kódovania vrátia hodnoty, ktoré sa dali prečítať, a null pre zvyšok. OgDomParser dekóduje deklarované kódovanie.'
    link: /sk/examples/broken-pages
    linkText: Ukážka na poškodených stránkach
  - icon: '<svg class="ms-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" width="44" height="44" aria-hidden="true" focusable="false"><path class="ms-soft" d="M8 10h9a4 4 0 1 1 8 0h9v9a4 4 0 1 1 0 8v9H8z"/><path class="ms-ink" d="M14 20h8M14 27h12"/><circle class="ms-acc-f" cx="37" cy="37" r="9.5"/><path class="ms-on-acc" d="M33.5 37.5l2.5 2.5 5-5.5"/></svg>'
    title: Vlastný parser
    details: 'Jedno rozhranie s jednou metódou. Čítajte Twitter karty, mikrodáta alebo čokoľvek iné a kombinujte to so vstavanými parsermi.'
    link: /sk/guide/custom-parser
    linkText: Ako napísať parser
---

## Ako to vyzerá

<Diagram name="how-it-works" alt="parseUrl stiahne stránku cez Guzzle, parse dostane HTML, ktoré už máte; parsery bežia v poradí, každý vráti nový Meta, zlúčenie ponechá prvú nájdenú hodnotu a výsledkom je jeden objekt Meta" />

Dajte scraperu HTML (alebo URL) a parsery, ktoré chcete, v poradí, v akom im dôverujete:

```php
use Tomaj\Scraper\Parser\OgDomParser;
use Tomaj\Scraper\Parser\SchemaParser;
use Tomaj\Scraper\Scraper;

$meta = new Scraper()->parse($html, [new SchemaParser(), new OgDomParser()]);

echo $meta->getTitle(), "\n";
echo $meta->getOgImage(), "\n";
echo $meta->getAuthors()[0]->name(), "\n";
echo $meta->getPublishedTime()?->format('Y-m-d H:i e'), "\n";
```

<!-- snippet: output -->
```text
Ten tips for PHP 8.4
https://blog.example/php84.jpg
Jana Nováková
2024-11-21 08:00 +00:00
```

Titulok a autor pochádzajú z JSON-LD `BlogPosting`, obrázok z `og:image`, ktorý JSON-LD nemal. [`examples/open-graph-article`](/sk/examples/open-graph-article) robí to isté proti skutočnému webovému serveru.

## Kam ďalej

<LinkCards :items="[
  { title: 'Začíname', text: 'Nainštalujte balík a prečítajte metadáta prvej stránky.', link: '/sk/guide/getting-started' },
  { title: 'Výber parsera', text: 'OgParser, OgDomParser alebo SchemaParser a v akom poradí.', link: '/sk/guide/parsers' },
  { title: 'Príklady', text: 'Sedem spustiteľných receptov na testovacích stránkach, otestovaných v CI.', link: '/sk/examples/' },
  { title: 'Bezpečnosť', text: 'Ako sťahovať URL od používateľov a neotvoriť dieru pre SSRF.', link: '/sk/guide/security' },
]" />

## Časté otázky {#faq}

### Čo Meta Scraper robí?

Prečíta metadáta, ktoré o sebe webová stránka zverejňuje: `<title>` a meta tagy, Open Graph vlastnosti a schema.org JSON-LD, a vráti ich ako jeden objekt `Meta` s getterom pre každé pole. Pozrite si [Začíname](/sk/guide/getting-started).

### Akú verziu PHP potrebuje?

PHP 8.4 alebo novšie a Guzzle 7.15.2+ alebo 8. `OgDomParser` potrebuje aj `ext-dom` a `ext-libxml`, ktoré má väčšina inštalácií PHP. Pozrite si [Požiadavky](/sk/guide/getting-started#requirements).

### Spúšťa JavaScript?

Nie. Spracuje HTML, ktoré vráti server. Stránky, ktoré si metadáta skladajú až v prehliadači, vyrenderujte headless prehliadačom a výsledné HTML odovzdajte do `parse()`. Pozrite si [Časté otázky](/sk/faq).

### Je bezpečné sťahovať URL, ktoré vložia používatelia?

Len s ochranou proti SSRF (podvrhnutiu požiadavky na strane servera): povoľte len http(s), odmietnite privátne adresy, overte presmerovania a nastavte timeouty. [Príklad náhľadu odkazu](/sk/examples/link-preview) ukazuje kompletnú ochranu. Pozrite si [Bezpečnosť](/sk/guide/security).

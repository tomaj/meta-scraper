---
description: OgParser a OgDomParser čítajú titulok, popis, kľúčové slová, autora a Open Graph tagy stránky. Všetky tagy, ktoré čítajú, a ako sa tieto dva parsery líšia.
---

# Meta tagy a Open Graph

`OgParser` a `OgDomParser` čítajú tie isté tagy do tých istých polí `Meta`. `OgParser` používa regulárne výrazy a nepotrebuje žiadne rozšírenie; `OgDomParser` spracuje dokument HTML5 parserom z PHP 8.4 (`Dom\HTMLDocument`) a je presnejší.

## Čítané tagy {#tags-that-are-read}

| Tag | Pole `Meta` |
| --- | --- |
| `<title>…</title>` | `setTitle()` / `getTitle()` |
| `<meta name="description" content="…">` | `getDescription()` |
| `<meta name="keywords" content="…">` | `getKeywords()` (celý reťazec, nerozdelený) |
| `<meta name="author" content="…">` | `getAuthors()`: `Author` s `id()` `null` |
| `<meta property="og:title" content="…">` | `getOgTitle()` |
| `<meta property="og:description" content="…">` | `getOgDescription()` |
| `<meta property="og:type" content="…">` | `getOgType()` |
| `<meta property="og:url" content="…">` | `getOgUrl()` |
| `<meta property="og:site_name" content="…">` | `getOgSiteName()` |
| `<meta property="og:image" content="…">` | `getOgImage()` |
| `<meta property="article:section" content="…">` | `getSections()`: `Section` s `id()` `null` |
| `<meta property="article:published_time" content="…">` | `getPublishedTime()`, spracované cez `strtotime()` |
| `<meta property="article:modified_time" content="…">` | `getModifiedTime()`, spracované cez `strtotime()` |

Tagy `name` musia používať `name`, Open Graph tagy musia používať `property` (tak to určuje protokol Open Graph): `<meta name="og:title">` sa nečíta. Tagy, ktoré v tabuľke nie sú, vrátane `twitter:*`, `og:image:width`, `og:locale` a `article:tag`, sa ignorujú; prečítate ich [vlastným parserom](./custom-parser.md).

```php
$meta = new OgDomParser()->parse($html);

echo $meta->getOgType(), ' | ', $meta->getOgSiteName(), ' | ', $meta->getOgUrl(), "\n";
echo $meta->getSections()[0]->name(), ' | ', $meta->getPublishedTime()?->format(DATE_ATOM), "\n";
```

<!-- snippet: output -->
```text
article | Example Blog | https://blog.example/php-84-tips
PHP | 2024-11-21T08:00:00+00:00
```

## OgDomParser

```php
$parser = new OgDomParser(); // throws MissingExtensionException without ext-dom or ext-libxml
```

- **Akékoľvek značkovanie.** Dokument spracuje `Dom\HTMLDocument`, HTML5 parser z PHP 8.4, ktorý dodržiava špecifikáciu, rovnako ako prehliadač: na poradí atribútov, spôsobe úvodzoviek, veľkých písmenách v tagoch, elementoch HTML5 (`<header>`, `<main>`, vlastné elementy) ani na chýbajúcich či zle vnorených uzatváracích tagoch nezáleží. Chyby parsovania sa nehlásia (`LIBXML_NOERROR`) a parser vaše nastavenie `libxml_use_internal_errors()` nemení.
- **Titulok:** text prvého HTML elementu `<title>` tak, ako je (bez orezania medzier). `<title>` vnútri vloženého `<svg>` nie je titulok stránky.
- **Hodnoty atribútov nerozlišujú veľkosť písmen:** `property="OG:Title"` je `og:title`.
- **Opakované tagy:** každý tag `author` a `article:section` sa pridá do zoznamu; pri ostatných poliach platí **posledný** tag na stránke.
- **Entity** dekóduje práve raz HTML parser, s úplným zoznamom HTML5 (`&hellip;` sa zmení na `…`, `&ccaron;` na `č`). Dvakrát escapované `&amp;amp;` zostane doslovným textom `&amp;`, rovnako ako v prehliadači.
- **Kódovanie:** kódovanie sa určí z BOM alebo z deklarácie `<meta charset="windows-1250">` / `<meta http-equiv="Content-Type" content="text/html; charset=…">` v prvých 1024 bajtoch, rovnako ako v prehliadači. Stránka bez deklarácie sa číta ako UTF-8. Keď stránka nie je platné UTF-8 a jej deklarácia príde neskôr alebo je to XML deklarácia (`<?xml encoding="…"?>`), použije sa táto deklarácia. Hodnoty sú vždy platné UTF-8: bajty, ktoré v danom kódovaní nie sú platné, sa zmenia na U+FFFD. Hlavička `Content-Type` odpovede sa nepoužíva: `parse()` vidí len HTML.
- **Prázdny vstup:** `parse('')` vráti prázdny `Meta`.

## OgParser

```php
$parser = new OgParser(); // no requirements
```

- **Regulárne výrazy:** jeden výraz na tag, bez rozlišovania veľkosti písmen, nad celým reťazcom. Funguje na akomkoľvek reťazci, aj na fragmentoch, napríklad len na `<head>`.
- **Na poradí atribútov záleží:** atribút `name` alebo `property` musí byť v tom istom tagu pred `content`. Fungujú jednoduché aj dvojité úvodzovky (hodnota môže obsahovať úvodzovky druhého typu aj `>`), hodnoty bez úvodzoviek nie.
- **Vždy len jeden tag:** každý výraz zostane vnútri jedného tagu `<meta>`. Tag, ktorý sa nedá prečítať, napríklad `<meta content="…" property="og:title">` (content na začiatku) alebo tag bez `content`, dá `null`, nikdy nie hodnotu iného tagu. Prázdny `content=""` dá prázdny reťazec.
- **Len prvá zhoda:** pri každom tagu, vrátane `author` a `article:section`, sa číta len prvý výskyt.
- **Entity:** každá pomenovaná aj číselná entita HTML5 sa raz dekóduje cez `html_entity_decode()` (`&eacute;` sa zmení na `é`, `&hellip;` na `…`, `&#8211;` na `–`, `&nbsp;` na nezlomiteľnú medzeru U+00A0), v titulku aj v každom `content`. Dvakrát escapované `&amp;amp;` sa zmení na doslovný text `&amp;`. Neznáme entity a entity bez koncovej `;` zostanú tak, ako sú.
- **Kódovanie:** bajty sa vrátia tak, ako sú; stránky, ktoré nie sú v UTF-8, pred spracovaním preveďte ([Sťahovanie stránok](./fetching.md#character-sets)).

::: tip Content na začiatku
`OgParser` tagy zapísané ako `<meta content="…" property="og:title">` neprečíta, `OgDomParser` áno. [Príklad s poškodenými stránkami](../examples/broken-pages.md) ich porovnáva. Na stránky, ktoré nemáte pod kontrolou, používajte `OgDomParser`.
:::

## Porovnanie oboch

[Príklad článku s Open Graph](../examples/open-graph-article.md) spracuje jeden novinový článok oboma parsermi. Výstup sa líši len v sekciách: stránka má dva tagy `article:section` a `OgParser` prečíta prvý z nich.

```php
$html = '<title>A &amp; B &hellip;</title>'
    . '<meta property="og:title" content="First"><meta property="og:title" content="Second">'
    . '<meta name="author" content="Ann"><meta name="author" content="Bob">';

foreach ([new OgParser(), new OgDomParser()] as $parser) {
    $meta = $parser->parse($html);
    $authors = implode(', ', array_map(static fn(Author $a): ?string => $a->name(), $meta->getAuthors()));
    echo new ReflectionClass($parser)->getShortName(), ' | ', $meta->getTitle(), ' | ', $meta->getOgTitle(), ' | ', $authors, "\n";
}
```

<!-- snippet: output -->
```text
OgParser | A & B … | First | Ann
OgDomParser | A & B … | Second | Ann, Bob
```

---
description: Nainštalujte tomaj/meta-scraper, prečítajte metadáta prvej HTML stránky a stiahnite stránku cez parseUrl().
---

# Začíname

## Požiadavky {#requirements}

| Požiadavka | Verzia | Na čo |
| --- | --- | --- |
| PHP | 8.4 alebo novšie | |
| `guzzlehttp/guzzle` | `^7.15.2` alebo `^8.0.1` | nainštaluje sa spolu s balíkom, používa ho `parseUrl()` |
| `ext-dom`, `ext-libxml` | ľubovoľná | len pre `OgDomParser`; vo väčšine buildov PHP sú zapnuté |

## Inštalácia

```bash
composer require tomaj/meta-scraper
```

## Prvá stránka

`Scraper::parse()` dostane HTML a zoznam parserov. Každý parser číta jeden druh metadát a scraper ich výsledky zlúči do jedného objektu `Meta`:

<<< @/snippets/getting-started.php

Výstup:

<<< @/snippets/getting-started.php.out{text}

Titulok je JSON-LD `headline`, pretože `SchemaParser` beží prvý. Popis a obrázok pochádzajú z meta tagov, ktoré `SchemaParser` nenašiel. Pravidlá vysvetľuje [Kombinovanie parserov](./combining.md).

## Stiahnutie stránky

`parseUrl()` stiahne stránku požiadavkou `GET` a spracuje ju. `getBody()` vráti stiahnuté HTML:

```php
use Tomaj\Scraper\Exception\FetchException;

$scraper = new Scraper(['timeout' => 10]);

try {
    $meta = $scraper->parseUrl($url, [new SchemaParser(), new OgDomParser()]);
    echo $meta->getOgSiteName(), ': ', $meta->getTitle(), "\n";
    echo strlen($scraper->getBody()) > 0 ? "body downloaded\n" : "empty body\n";
} catch (FetchException $e) {
    echo "Cannot download {$e->url}\n";
}
```

<!-- snippet: output -->
```text
Example Blog: Ten tips for PHP 8.4
body downloaded
```

Timeouty, hlavičky, proxy a vlastný Guzzle klient sú opísané v [Sťahovaní stránok](./fetching.md); všetky voľby nájdete v [prehľade konfigurácie](./configuration.md).

::: warning URL od používateľov
URL, ktorú zadal používateľ, neodovzdávajte do `parseUrl()` bez ochrany proti SSRF (podvrhnutiu požiadavky na strane servera). Pozrite si [Bezpečnosť](./security.md).
:::

## Čo dostanete

`Meta` má getter pre každé pole. Pole, ktoré žiadny parser nenašiel, je `null`; zoznam, ktorý žiadny parser nenašiel, je `[]`:

| Getter | Typ | Zdroj |
| --- | --- | --- |
| `getTitle()` | `?string` | `<title>`, JSON-LD `headline` |
| `getDescription()` | `?string` | `<meta name="description">`, JSON-LD `description` |
| `getKeywords()` | `?string` | `<meta name="keywords">` |
| `getAuthors()` | `list<Author>` | `<meta name="author">`, JSON-LD `author` |
| `getSections()` | `list<Section>` | `article:section`, JSON-LD `articleTerms` / `articleSection` |
| `getTags()` | `list<Tag>` | JSON-LD `about` |
| `getOgType()` | `?string` | `og:type` |
| `getOgTitle()` | `?string` | `og:title` |
| `getOgDescription()` | `?string` | `og:description` |
| `getOgUrl()` | `?string` | `og:url`, JSON-LD `url` |
| `getOgSiteName()` | `?string` | `og:site_name` |
| `getOgImage()` | `?string` | `og:image`, JSON-LD `image.url` |
| `getPublishedTime()` | `?DateTime` (UTC) | `article:published_time`, JSON-LD `datePublished` |
| `getModifiedTime()` | `?DateTime` (UTC) | `article:modified_time`, JSON-LD `dateModified` |

Každé pole opisuje stránka [Objekt Meta](./meta.md); [referencia API](../api/reference.md) obsahuje všetky triedy a metódy.

## Ďalšie kroky

- [Ako to funguje](./how-it-works.md): čo sa deje medzi HTML a objektom `Meta`.
- [Výber parsera](./parsers.md): ktoré parsery použiť a v akom poradí.
- [Príklady](../examples/index.md): sedem spustiteľných receptov, od novinového článku po náhľad odkazu bezpečný voči SSRF.

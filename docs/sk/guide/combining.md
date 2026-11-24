---
description: Ako Scraper::parse() zlučuje výsledky viacerých parserov cez Meta::merge() - platí prvá nájdená hodnota - a aké poradie zvoliť.
---

# Kombinovanie parserov

`Scraper::parse($html, $parsers)` spustí parsery v poradí poľa a ich výsledky zlúči do jedného `Meta` cez `Meta::merge()`.

<Diagram name="merge" alt="SchemaParser beží prvý a nastaví titulok a autorov; OgDomParser beží druhý: jeho titulok a autori sa ignorujú, lebo tieto polia sú už nastavené, jeho obrázok a názov webu vyplnia polia, ktoré boli ešte prázdne" />

## Pravidlá

1. **Platí hodnota z prvého parsera, ktorý ju našiel.** Neskorší parser vyplní len polia, ktoré sú ešte prázdne.
2. Textové pole je prázdne, keď je `null`, `''` alebo `'0'`. Parser, ktorý našiel `''` (napríklad `<meta name="description" content="">`), neblokuje neskorší parser.
3. **Zoznamy sa preberajú celé.** Autori, sekcie a tagy pochádzajú z prvého parsera, ktorého zoznam nie je prázdny, a nikdy sa nemiešajú: JSON-LD autori s `@id` sa nerozšíria o duplicitné meno z `<meta name="author">`.
4. Čas publikovania a čas úpravy sa preberú z prvého parsera, ktorý dátum spracoval.
5. O každom poli sa rozhoduje samostatne: titulok môže pochádzať z jedného parsera a obrázok z iného.

## Poradie mení výsledok

<<< @/snippets/combine-parsers.php

Výstup:

<<< @/snippets/combine-parsers.php.out{text}

Bežné poradia:

| Poradie | Výsledok |
| --- | --- |
| `[new SchemaParser(), new OgDomParser()]` | Redakčný titulok (bez prípony `" \| Site"`), autori s `@id`, presné dátumy; Open Graph doplní obrázok, názov webu a polia `og:*`. Vhodné na archiváciu a indexovanie článkov. |
| `[new OgDomParser(), new SchemaParser()]` | To, čo stránka ukáže pri zdieľaní: `<title>` a `og:image` vybraný pre sociálne siete. JSON-LD len vypĺňa medzery (autorov s `@id`, keď chýba meta tag `author`). Vhodné na náhľady odkazov. |
| `[…, new YourParser()]` | Váš parser ako posledná záloha, napríklad pre [Twitter karty](../examples/twitter-card.md). |

[Príklad JSON-LD @graph](../examples/json-ld-graph.md) spustí na jednej stránke obe poradia a vypíše, ktorá hodnota platí.

## `getTitle()` verzus `getOgTitle()`

Sú to rôzne polia. `getTitle()` je `<title>` (alebo JSON-LD `headline`), `getOgTitle()` je `og:title`. Náhľad odkazu zvyčajne chce najprv `og:title`:

```php
$meta = $scraper->parse($html, [new OgDomParser()]);
$headline = $meta->getOgTitle() ?? $meta->getTitle();
echo $headline, "\n";
```

<!-- snippet: output -->
```text
Ten tips for PHP 8.4
```

## Vlastné zlučovanie

`Meta::merge()` je verejná metóda, takže môžete kombinovať výsledky, ktoré ste získali samostatne, napríklad JSON-LD z cache s čerstvo stiahnutým Open Graph. Mení a vracia objekt, na ktorom ju voláte:

```php
$fromCache = new Meta()->setTitle('Cached title');
$fresh = new OgDomParser()->parse($html);

$meta = $fromCache->merge($fresh);

echo $meta->getTitle(), "\n";      // kept: it was already set
echo $meta->getOgImage(), "\n";    // filled from $fresh
```

<!-- snippet: output -->
```text
Cached title
https://blog.example/php84.jpg
```

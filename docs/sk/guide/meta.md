---
description: Výsledný objekt Meta knižnice meta-scraper - každé pole s typom a zdrojom, Author, Section a Tag, dátumy, settery a merge().
---

# Objekt Meta

`Tomaj\Scraper\Meta` vracia každý parser aj `Scraper::parse()` / `parseUrl()`. Je to obyčajný meniteľný objekt: jedno pole s možnou hodnotou `null` pre každú hodnotu a tri zoznamy.

## Polia

| Pole | Getter | Typ | Setter / pridanie | Číta ho |
| --- | --- | --- | --- | --- |
| Titulok | `getTitle()` | `?string` | `setTitle(string)` | Og*, Schema (`headline`) |
| Popis | `getDescription()` | `?string` | `setDescription(string)` | Og*, Schema |
| Kľúčové slová | `getKeywords()` | `?string` | `setKeywords(string)` | Og* |
| Autori | `getAuthors()` | `list<Author>` | `addAuthor(Author)`, `addAuthorByName(string)` | Og*, Schema |
| Sekcie | `getSections()` | `list<Section>` | `addSection(Section)`, `addSectionByName(string)` | Og*, Schema |
| Tagy | `getTags()` | `list<Tag>` | `addTag(Tag)` | Schema (`about`) |
| Typ Open Graph | `getOgType()` | `?string` | `setOgType(string)` | Og* |
| Titulok Open Graph | `getOgTitle()` | `?string` | `setOgTitle(string)` | Og* |
| Popis Open Graph | `getOgDescription()` | `?string` | `setOgDescription(string)` | Og* |
| Kanonická URL | `getOgUrl()` | `?string` | `setOgUrl(string)` | Og*, Schema (`url`) |
| Názov webu | `getOgSiteName()` | `?string` | `setOgSiteName(string)` | Og* |
| URL obrázka | `getOgImage()` | `?string` | `setOgImage(string)` | Og*, Schema (`image.url`) |
| Publikované | `getPublishedTime()` | `?DateTimeImmutable` | `setPublishedTime(DateTimeInterface\|string)` | Og*, Schema |
| Upravené | `getModifiedTime()` | `?DateTimeImmutable` | `setModifiedTime(DateTimeInterface\|string)` | Og*, Schema |

„Og\*“ znamená `OgParser` a `OgDomParser`; presné tagy sú na stránkach [Meta tagy a Open Graph](./open-graph.md) a [schema.org JSON-LD](./json-ld.md).

- Pole, ktoré nikto nenastavil, je `null`; zoznam, do ktorého nikto nič nepridal, je `[]`.
- Hodnoty sú reťazce presne tak, ako ich stránka zapísala, po dekódovaní entít: neorezané, nevalidované, URL nie sú prevedené na absolútne. Považujte ich za nedôveryhodný vstup ([Bezpečnosť](./security.md#untrusted-values)).
- Settery a metódy na pridávanie vracajú ten istý `Meta` (`$this`), takže volania sa dajú reťaziť. Setter hodnotu prepíše, pridávacia metóda ju pripojí na koniec zoznamu.

## Author, Section a Tag {#author-section-and-tag}

Tri malé readonly hodnotové objekty s rovnakou štruktúrou:

```php
$author = new Author('https://blog.example/#jana', 'Jana Nováková');

echo $author->id(), "\n";    // the schema.org @id, or null
echo $author->name(), "\n";  // the name, or null
```

<!-- snippet: output -->
```text
https://blog.example/#jana
Jana Nováková
```

Obe hodnoty môžu byť `null`: autor v JSON-LD môže mať len `@id` a hodnota neočakávaného typu dá `null`. `addAuthorByName('Jana')` je skratka pre `addAuthor(new Author(null, 'Jana'))`, rovnako `addSectionByName()`. Metóda `addTagByName()` neexistuje.

## Dátumy {#dates}

`setPublishedTime()` a `setModifiedTime()` prijímajú ľubovoľný `DateTimeInterface` (uloží sa kópia, takže neskoršia zmena vášho objektu `Meta` nezmení) alebo reťazec. Gettery vracajú `DateTimeImmutable`.

- Reťazec sa spracuje cez `new DateTimeImmutable()` a zachová **posun, ktorý zapísala stránka**: `2026-11-20T06:30:00+01:00` zostane `+01:00`, `…Z` je `+00:00`. Na zobrazenie ho preveďte cez `setTimezone()`.
- Prijímajú sa len **absolútne** dátumy: ISO 8601 / RFC 3339 (so sekundami aj bez nich, so zlomkami sekundy a s posunom), RFC 2822 (`Fri, 20 Nov 2026 06:30:00 +0100`, deň v týždni musí sedieť s dátumom), `2026-11-20 06:30`, samotný dátum (`2026-11-20`, polnoc). Reťazec bez posunu alebo časového pásma je v predvolenom časovom pásme PHP (`date_default_timezone_get()`).
- Všetko ostatné sa ignoruje a pole si ponechá predchádzajúcu hodnotu: text, relatívne výrazy závislé od aktuálneho času (`yesterday noon`, `+1 week`, `now`, `2026-11-20 +1 day`), čas bez dátumu, samotný rok, Unix timestamp (`@1763616600`), neplatné dátumy (`2026-02-30`) a deň v týždni, ktorý nesedí s dátumom.

```php
$meta = new Meta()
    ->setPublishedTime('2026-11-20T06:30:00+01:00')
    ->setModifiedTime('yesterday noon');

echo $meta->getPublishedTime()?->format(DATE_ATOM), "\n";
var_dump($meta->getModifiedTime());

// show it in your time zone: setTimezone() returns a new object, $meta is not changed
echo $meta->getPublishedTime()?->setTimezone(new DateTimeZone('UTC'))->format('j. n. Y H:i e'), "\n";
```

<!-- snippet: output -->
```text
2026-11-20T06:30:00+01:00
NULL
20. 11. 2026 05:30 UTC
```

## merge()

<!-- snippet: skip -->
```php
public function merge(Meta $meta): Meta;
```

Skopíruje každú hodnotu z `$meta` do tohto objektu tam, kde tento objekt žiadnu nemá, a vráti `$this`. Textové pole sa považuje za prázdne, keď je `null`, `''` alebo `'0'`; zoznam, keď je `[]`; dátum, keď je `null`. Zoznamy sa kopírujú celé. `Scraper::parse()` cez ňu spája výsledky parserov; pozrite si [Kombinovanie parserov](./combining.md).

## Vlastný objekt Meta

`new Meta()` so settermi sa hodí v testoch kódu, ktorý metadáta spracúva, a vo [vlastnom parseri](./custom-parser.md):

```php
$meta = new Meta()
    ->setTitle('Hello')
    ->setOgImage('https://example.com/a.jpg')
    ->addAuthorByName('Ann')
    ->addTag(new Tag('https://example.com/tag/php', 'PHP'));

echo $meta->getTitle(), ' by ', $meta->getAuthors()[0]->name(), ', tag ', $meta->getTags()[0]->name(), "\n";
```

<!-- snippet: output -->
```text
Hello by Ann, tag PHP
```

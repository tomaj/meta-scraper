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
| Publikované | `getPublishedTime()` | `?DateTime` | `setPublishedTime(DateTime\|string)` | Og*, Schema |
| Upravené | `getModifiedTime()` | `?DateTime` | `setModifiedTime(DateTime\|string)` | Og*, Schema |

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

`setPublishedTime()` a `setModifiedTime()` prijímajú `DateTime` (uloží sa tak, ako je) alebo reťazec:

- reťazec sa spracuje cez `strtotime()` a uloží ako `DateTime` v **UTC**, takže z `2026-11-20T06:30:00+01:00` sa stane `2026-11-20T05:30:00+00:00`;
- reťazec, ktorý `strtotime()` nevie spracovať, sa ignoruje: pole si ponechá predchádzajúcu hodnotu;
- `strtotime()` rozumie aj relatívnym výrazom ako `yesterday noon`, ktoré sa vyhodnotia voči aktuálnemu času.

```php
$meta = new Meta()
    ->setPublishedTime('2026-11-20T06:30:00+01:00')
    ->setModifiedTime('not a date');

echo $meta->getPublishedTime()?->format(DATE_ATOM), "\n";
var_dump($meta->getModifiedTime());

// show it in your time zone, without changing the object stored in $meta
$published = $meta->getPublishedTime();
if ($published !== null) {
    echo DateTimeImmutable::createFromMutable($published)->setTimezone(new DateTimeZone('Europe/Bratislava'))->format('j. n. Y H:i'), "\n";
}
```

<!-- snippet: output -->
```text
2026-11-20T05:30:00+00:00
NULL
20. 11. 2026 06:30
```

Gettery vracajú priamo uložený meniteľný objekt `DateTime`: zavolanie `setTimezone()` na ňom zmení aj `Meta`. Najprv ho preveďte na `DateTimeImmutable` (ako vyššie) alebo ho naklonujte.

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

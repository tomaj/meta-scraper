---
description: SchemaParser číta schema.org JSON-LD - ktoré bloky script a ktorý uzol použije, @graph a odkazy @id a všetky polia, ktoré mapuje do Meta.
---

# schema.org JSON-LD

`SchemaParser` číta štruktúrované dáta [JSON-LD](https://json-ld.org/), teda bloky `<script type="application/ld+json">`, ktoré pre vyhľadávače pridávajú spravodajské weby, blogy a pluginy CMS (Yoast, Rank Math, …). Nepotrebuje žiadne rozšírenie.

```php
$parser = new SchemaParser(); // no requirements
```

## Ktoré bloky sa čítajú

- Každý element `<script>`, ktorého atribút `type` je `application/ld+json`, bez ohľadu na poradie atribútov, úvodzovky či veľkosť písmen, s JSON na jednom alebo viacerých riadkoch.
- Blok, ktorý nie je platný JSON, alebo je platný JSON, ale nie objekt ani zoznam (`"text"`, `[]`), sa preskočí; ostatné bloky sa čítajú ďalej.
- Blok môže obsahovať jeden objekt, zoznam objektov alebo objekt so zoznamom `@graph`. Grafy sa sploštia, aj vnorené, takže sa zúčastní každý uzol každého bloku.

## Ktorý uzol sa použije {#which-node-is-used}

Jeden uzol stránky opisuje samotnú stránku, ostatné opisujú vydavateľa, web, navigačnú cestu (breadcrumbs) alebo obrázok. `SchemaParser` vyberá uzol v tomto poradí:

1. **Blok označený `id="schema"`**, ak ho stránka má: ako uzol sa použije celý dekódovaný blok. Toto je značkovanie, ktoré parser podporoval pred verziou 5.0.
2. **Prvý uzol článku** v poradí dokumentu: `@type` s hodnotou `Article`, `AdvertiserContentArticle`, `AnalysisNewsArticle`, `AskPublicNewsArticle`, `BackgroundNewsArticle`, `BlogPosting`, `DiscussionForumPosting`, `LiveBlogPosting`, `NewsArticle`, `OpinionNewsArticle`, `Report`, `ReportageNewsArticle`, `ReviewNewsArticle`, `SatiricalArticle`, `ScholarlyArticle`, `SocialMediaPosting` alebo `TechArticle`.
3. **Prvý uzol webovej stránky**: `WebPage`, `AboutPage`, `CheckoutPage`, `CollectionPage`, `ContactPage`, `FAQPage`, `ItemPage`, `MedicalWebPage`, `ProfilePage`, `QAPage`, `RealEstateListing` alebo `SearchResultsPage`.

Ak žiadny nevyhovuje, výsledkom je prázdny `Meta`. `@type` môže byť reťazec alebo zoznam (`["NewsArticle", "Article"]`); názvy typov sa porovnávajú presne, bez predpony `https://schema.org/`. Uzly iných typov (`Organization`, `WebSite`, `BreadcrumbList`, `ImageObject`, `Person`, `Product`, `Event`, …) sa nikdy nevyberú, pretože ich `url`, `description` ani `image` neopisujú stránku.

## Odkazy

Hodnoty zapísané ako odkaz, teda objekt, ktorý obsahuje len `@id`, napríklad `{"@id": "https://example.com/#author"}`, sa nahradia uzlom s týmto `@id` z ktoréhokoľvek bloku na stránke. Platí to pre `author`, `articleTerms`, `about` a `image`. Keď majú dva uzly rovnaké `@id`, platí prvý. Odkaz na uzol, ktorý neexistuje, zostane tak, ako je (objekt s `@id` a bez mena).

## Polia {#fields}

| JSON-LD vlastnosť uzla | `Meta` | Prijímané hodnoty |
| --- | --- | --- |
| `headline` | `getTitle()` | reťazec alebo celé číslo |
| `description` | `getDescription()` | reťazec alebo celé číslo |
| `author` | `getAuthors()` | objekt s `@id`/`name`, reťazec (meno) alebo ich zoznam |
| `articleTerms` | `getSections()` | zoznam objektov s `"@type": "Category"` a `@id`/`name`; ostatné objekty sa preskočia |
| `articleSection` | `getSections()`, len keď nie je zoznam `articleTerms` | reťazec alebo zoznam reťazcov |
| `about` | `getTags()` | objekt s `@id`/`name` alebo ich zoznam; samotné reťazce sa preskočia |
| `image` | `getOgImage()` | reťazec s URL, objekt s `url` (`ImageObject`, prípadne odkaz) alebo ich zoznam: prvá položka s URL |
| `url` | `getOgUrl()` | reťazec |
| `datePublished` | `getPublishedTime()` | dátum, ktorému rozumie `strtotime()` |
| `dateModified` | `getModifiedTime()` | dátum, ktorému rozumie `strtotime()` |

Podrobnosti:

- Kde sa očakáva text, použijú sa reťazce a celé čísla (`"@id": 42` sa zmení na `"42"`); ostatné typy (booleovské hodnoty, desatinné čísla, objekty) sa ignorujú. Hodnoty sa nikdy neorezávajú ani nedekódujú: JSON má vlastné escapovanie.
- Na titulok sa nepoužíva `name` uzla, len `headline`. Uzol `WebPage` má zvyčajne `name` a nemá `headline`, takže jeho titulok zostane `null` a môže ho vyplniť neskorší parser.
- `image` môže byť reťazec s URL (`"image": "https://…/a.jpg"`), `ImageObject` alebo odkaz naň, prípadne zoznam ľubovoľných z nich (`"image": ["https://…/1x1.jpg", "https://…/16x9.jpg"]`, tvar, ktorý pre články odporúča Google). Zo zoznamu sa použije prvá položka, ktorá dá neprázdny reťazec s URL; prázdne reťazce, čísla, odkazy na neexistujúce uzly a objekty bez reťazca v `url` (napríklad `ImageObject` len s `contentUrl`) sa preskočia.
- Objekty `Author`, `Section` a `Tag` dostanú ako `id()` hodnotu `@id` uzla; pri autoroch zadaných reťazcom a pri `articleSection` je `null`.
- `og:type`, `og:site_name`, `og:title`, `og:description` a kľúčové slová nemajú v JSON-LD zdroj a zostanú `null`.

## Príklad

`@graph` v štýle WordPressu, kde článok odkazuje na autora a obrázok cez `@id`:

```php
$page = <<<'HTML'
    <script type="application/ld+json">
    {"@context": "https://schema.org", "@graph": [
        {"@type": "WebSite", "@id": "https://news.example/#website", "name": "News", "description": "Not the page"},
        {"@type": "ImageObject", "@id": "https://news.example/a/#image", "url": "https://news.example/a.jpg"},
        {"@type": "NewsArticle", "headline": "The headline", "url": "https://news.example/a/",
         "image": {"@id": "https://news.example/a/#image"},
         "author": [{"@id": "https://news.example/#eva"}, "Guest Writer"],
         "articleSection": ["World", "Europe"], "about": {"@id": "https://news.example/t/eu", "name": "EU"},
         "datePublished": "2026-01-15T10:00:00+01:00"},
        {"@type": "Person", "@id": "https://news.example/#eva", "name": "Eva"}
    ]}
    </script>
    HTML;

$meta = new SchemaParser()->parse($page);

echo $meta->getTitle(), ' | ', $meta->getOgUrl(), ' | ', $meta->getOgImage(), "\n";
foreach ($meta->getAuthors() as $author) {
    echo 'author: ', $author->name(), ' (', $author->id() ?? 'no @id', ")\n";
}
echo 'sections: ', implode(', ', array_map(static fn(Section $s): ?string => $s->name(), $meta->getSections())), "\n";
echo 'tag: ', $meta->getTags()[0]->name(), ' (', $meta->getTags()[0]->id(), ")\n";
echo 'published: ', $meta->getPublishedTime()?->format(DATE_ATOM), "\n";
echo 'description: ', var_export($meta->getDescription(), true), "\n";
```

<!-- snippet: output -->
```text
The headline | https://news.example/a/ | https://news.example/a.jpg
author: Eva (https://news.example/#eva)
author: Guest Writer (no @id)
sections: World, Europe
tag: EU (https://news.example/t/eu)
published: 2026-01-15T09:00:00+00:00
description: NULL
```

Popis z `WebSite` sa nepoužije: `NewsArticle` vlastný popis nemá. [Príklad JSON-LD @graph](../examples/json-ld-graph.md) robí to isté s celou stránkou v štýle Yoast cez HTTP a kombinuje ju s Open Graph.

## Sekcie z `articleTerms`

Niektorí vydavatelia opisujú sekcie ako uzly `Category` s `@id`. Keď má uzol zoznam `articleTerms`, použije sa namiesto `articleSection`:

```php
$page = '<script type="application/ld+json">{"@type": "Article", "articleSection": "ignored",
    "articleTerms": [{"@type": "Category", "@id": "https://news.example/c/sport", "name": "Sport"},
                     {"@type": "Thing", "name": "skipped, not a Category"}]}</script>';

foreach (new SchemaParser()->parse($page)->getSections() as $section) {
    echo $section->name(), ' ', $section->id(), "\n";
}
```

<!-- snippet: output -->
```text
Sport https://news.example/c/sport
```

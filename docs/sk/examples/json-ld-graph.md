---
description: Ako prečítať schema.org JSON-LD @graph s odkazmi @id v štýle WordPress/Yoast pomocou SchemaParser z meta-scraper a ako poradie parserov rozhoduje, ktorá hodnota platí.
---

# Príklad: JSON-LD @graph

Spravodajská stránka tak, ako ju vykreslí WordPress s Yoast SEO: jeden blok JSON-LD s `@graph` so šiestimi uzlami (organizácia, web, obrázok, webová stránka, článok, osoba) prepojenými cez `@id`, druhý blok s navigáciou (breadcrumbs) a tagy Open Graph, ktorých titulok a obrázok sa líšia od tých v JSON-LD.

<ProjectSource name="json-ld-graph" :files="['run.php', 'site/news.html', 'e2e.sh', 'expected-output.txt']" />

## Stránka

<<< @/../examples/json-ld-graph/site/news.html

## Kód

<<< @/../examples/json-ld-graph/run.php

## Výstup

<<< @/../examples/json-ld-graph/expected-output.txt{text}

## Čo si všimnúť

- **Správny uzol.** `SchemaParser` použije `NewsArticle` (jeho `@type` je zoznam, `["NewsArticle", "Article"]`) a ignoruje uzly `Organization`, `WebSite`, `ImageObject`, `WebPage` a `BreadcrumbList`, hoci sú skôr a majú vlastné `url`, `name` a `description`. Pozrite [Ktorý uzol sa použije](../guide/json-ld.md#which-node-is-used).
- **Vyriešené odkazy.** Prvý autor je v článku len `{"@id": ".../person/peter"}`; parser nájde uzol `Person` a vráti jeho meno spolu s `@id`. Druhý autor je zapísaný priamo a `@id` nemá. Obrázok je odkaz na uzol `ImageObject`.
- **Žiadny Open Graph z JSON-LD.** `og:title` a názov webu ostanú pri samotnom `SchemaParser` `null`.
- **Rozhoduje poradie.** Keď je prvý `SchemaParser`, titulok je čistý `headline` a obrázok je ten so šírkou 1600 px z JSON-LD. Keď je prvý `OgDomParser`, dostanete `<title>` s príponou webu, meta description a obrázok pre sociálne siete. Autori sú v oboch prípadoch rovnakí: stránka nemá meta tag `author`, takže ich má len JSON-LD. Pozrite [Kombinovanie parserov](../guide/combining.md).

## Čo kontroluje CI

Vybraný uzol, oba vyriešené odkazy, dátumy v UTC, sekcie a tagy s ich `@id` a zlúčený výsledok pri oboch poradiach parserov.

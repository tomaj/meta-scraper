---
description: Unit testy kódu, ktorý volá parseUrl() z meta-scraper, bez siete - s Guzzle MockHandler a middlewarom history.
---

# Príklad: testovanie s mockom

Služba `ArticleImporter` dostane v konštruktore `Scraper` a importuje titulok a obrázok článku z URL; keď sa stránka nedá stiahnuť, vráti `null`. Príklad ju testuje bez akéhokoľvek webového servera: `Scraper` dostane Guzzle klienta, ktorého handler je `MockHandler` s tromi pripravenými odpoveďami, a middleware `history` zaznamenáva požiadavky.

<ProjectSource name="testing" :files="['run.php', 'e2e.sh', 'expected-output.txt']" />

## Kód

<<< @/../examples/testing/run.php

## Výstup

<<< @/../examples/testing/expected-output.txt{text}

## Čo si všimnúť

- Služba dostane `Scraper` zvonka, takže test jej môže odovzdať taký, ktorý má mock klienta. To isté nastavenie funguje aj v testovacom prípade PHPUnit; pozrite [Testovanie vlastného kódu](../guide/testing.md).
- Odpovede sa vracajú v poradí: stránka, 404 (ktorú `parseUrl()` premení na `FetchException`) a `ConnectException` (tiež `FetchException`).
- S vlastným klientom sa z prvého argumentu konštruktora stanú request options: história ukazuje `User-Agent` a `timeout` z neho.
- `getBody()` si drží telo posledného úspešného sťahovania aj po neúspešných volaniach.

## Čo kontroluje CI

Všetkých jedenásť kontrol vypíše `ok`; kontrola, ktorá zlyhá, vypíše `FAIL` a skončí so stavom 1, čím workflow zlyhá.

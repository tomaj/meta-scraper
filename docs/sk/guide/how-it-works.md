---
description: Čo sa deje medzi HTML stránkou a objektom Meta - Scraper, parsery a Meta::merge() krok za krokom.
---

# Ako to funguje

<Diagram name="how-it-works" alt="parseUrl stiahne stránku cez Guzzle, parse dostane HTML, ktoré už máte; parsery bežia v poradí, každý vráti nový Meta, merge ponechá prvú nájdenú hodnotu a výsledkom je jeden objekt Meta" />

Zúčastňujú sa tri druhy objektov:

| Objekt | Úloha |
| --- | --- |
| `Scraper` | Stiahne stránku (voliteľne) a spustí parsery. Drží jeden Guzzle klient a telo poslednej stiahnutej stránky. |
| Parsery (`ParserInterface`) | Z HTML reťazca prečítajú jeden druh metadát a vrátia nový `Meta`. Nemajú stav: jedna inštancia spracuje ľubovoľný počet stránok. |
| `Meta` | Výsledok: jedno pole s možnou hodnotou `null` pre každú hodnotu a zoznamy objektov `Author`, `Section` a `Tag`. |

## Krok za krokom

### 1. Získanie HTML

HTML buď už máte a zavoláte `parse($html, $parsers)`, alebo zavoláte `parseUrl($url, $parsers)`:

1. `parseUrl()` pošle `GET $url` cez Guzzle klient, ktorý scraper vytvoril v konštruktore (alebo ktorý ste mu odovzdali), s voľbami požiadavky.
2. Každá výnimka Guzzle (chyba spojenia, timeout, chyba TLS, príliš veľa presmerovaní, odpoveď 4xx alebo 5xx pri predvolenom `http_errors`) sa zmení na `FetchException` s výnimkou Guzzle v `getPrevious()`. Nič sa nespracuje a `getBody()` ponechá telo z predchádzajúceho úspešného volania.
3. Inak sa telo uloží (vráti ho `getBody()`) a odovzdá sa do `parse()` presne tak, ako ho poslal server: bez prevodu kódovania a bez dekompresie nad rámec toho, čo urobí Guzzle.

### 2. Spustenie parserov

`parse()` vytvorí prázdny `Meta` a pre každý parser v poradí poľa zavolá `$parser->parse($html)` a vrátený `Meta` zlúči do výsledku. Parsery výsledky ostatných parserov nevidia.

Prázdny zoznam parserov je povolený: dostanete prázdny `Meta` (`parseUrl($url, [])` je spôsob, ako stránku iba stiahnuť a potom čítať `getBody()`).

### 3. Zlúčenie

`Meta::merge()` skopíruje hodnotu len do poľa, ktoré je ešte prázdne, takže **platí hodnota z prvého parsera, ktorý ju našiel**:

- textové pole sa považuje za prázdne, keď je `null`, `''` alebo `'0'`;
- autori, sekcie a tagy sa kopírujú ako celý zoznam, z prvého parsera, ktorého zoznam nie je prázdny; zoznamy sa nikdy nemiešajú;
- čas publikovania a čas úpravy sa skopírujú, keď sú `null`.

<Diagram name="merge" alt="SchemaParser beží prvý a nastaví titulok a autorov; OgDomParser beží druhý: jeho titulok a autori sa ignorujú, lebo tieto polia sú už nastavené, jeho obrázok a názov webu vyplnia polia, ktoré boli ešte prázdne" />

Čo to znamená pri skutočných stránkach, ukazuje [Kombinovanie parserov](./combining.md).

## Čo parsery nerobia

- **Nespúšťajú JavaScript.** Metadáta, ktoré stránka pridá až v prehliadači, v HTML zo servera nie sú.
- **Nedopĺňajú relatívne URL.** `og:image` s hodnotou `/img/a.jpg` sa vráti ako `/img/a.jpg`; voči URL stránky si ju doplňte sami (robí to [príklad náhľadu odkazu](../examples/link-preview.md)).
- **Nevalidujú hodnoty.** URL, typy a dátumy sa vracajú tak, ako ich stránka zapísala (dátumy sa spracujú cez `strtotime()`, pozrite si [Objekt Meta](./meta.md#dates)).
- **Nevyhadzujú výnimky pri zlom vstupe.** Poškodené HTML alebo JSON dá tie hodnoty, ktoré sa dali prečítať; pozrite si [Spracovanie chýb](./errors.md).

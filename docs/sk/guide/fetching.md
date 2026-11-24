---
description: Sťahovanie stránok cez Scraper::parseUrl() a Guzzle - timeouty, User-Agent, presmerovania, proxy, vlastný Guzzle klient, getBody() a kódovanie znakov.
---

# Sťahovanie stránok

<!-- snippet: skip -->
```php
public function parseUrl(string $url, array $parsers): Meta;
```

`parseUrl()` pošle cez Guzzle jednu požiadavku `GET` na `$url`, uloží telo odpovede (pozrite `getBody()`) a vráti `parse($body, $parsers)`. Neúspešná požiadavka vyhodí `FetchException` ([Spracovanie chýb](./errors.md)).

## Guzzle klient scrapera

<!-- snippet: skip -->
```php
public function __construct(array $guzzleOptions = [], ?\GuzzleHttp\ClientInterface $httpClient = null);
```

Scraper vytvorí svoj Guzzle klient raz, v konštruktore, a používa ho pri každom volaní `parseUrl()`. Jeden `Scraper` používajte pre veľa stránok.

Bez vlastného klienta `$guzzleOptions` nastavujú klienta, ktorého vytvorí scraper. Zlúčia sa s predvolenými hodnotami cez `array_merge()`:

| Predvolené | Hodnota |
| --- | --- |
| `connect_timeout` | `5` sekúnd |
| `headers` | `['User-Agent' => 'Tomaj\Scraper']` |

`array_merge()` je plytké: pole `headers`, ktoré odovzdáte, predvolené hlavičky **nahradí**, takže ak chcete `User-Agent`, nastavte ho v ňom:

<<< @/snippets/fetch.php

Každá podporovaná voľba s typom a predvolenou hodnotou je v [prehľade konfigurácie](./configuration.md).

::: tip Vždy nastavte timeout
Predvolené nastavenie má timeout pripojenia, ale žiadny celkový `timeout`: server, ktorý spojenie prijme a potom nič nepošle alebo posiela telo donekonečna, zablokuje `parseUrl()` natrvalo. Nastavte `timeout` (a obmedzte presmerovania) vždy, keď sťahujete z hostiteľov, ktoré nemáte pod kontrolou.
:::

## Vlastný Guzzle klient {#your-own-guzzle-client}

Na všetko, čo voľby nepokrývajú (middleware na opakovanie či logovanie, cookies, autentifikácia, kontroly cez `on_redirect`, mock handler v testoch), si Guzzle klienta nastavte sami a odovzdajte ho ako druhý argument:

- predvolené hodnoty scrapera sa na váš klient **nepoužijú**: timeouty a User-Agent nastavte sami;
- z `$guzzleOptions` sa stanú **voľby požiadavky**, ktoré sa posielajú s každou požiadavkou a Guzzle ich zlúči s konfiguráciou vášho klienta.

<<< @/snippets/own-client.php

[Príklad náhľadu odkazu](../examples/link-preview.md) používa vlastný klient, aby skontroloval každý cieľ presmerovania a pripol spojenie ku skontrolovanej IP adrese.

## getBody()

<!-- snippet: skip -->
```php
public function getBody(): string;
```

Vráti telo stiahnuté posledným **úspešným** volaním `parseUrl()` (pred prvým volaním `''`). Neúspešné volanie ho nezmení. Použite ho na uloženie HTML alebo na spustenie ďalších parserov bez opätovného sťahovania stránky:

```php
$scraper->parseUrl($url, []);           // only download
$html = $scraper->getBody();

$social = $scraper->parse($html, [new OgDomParser()]);
$article = $scraper->parse($html, [new SchemaParser()]);
echo $social->getOgSiteName(), ' / ', $article->getTitle(), "\n";
```

<!-- snippet: output -->
```text
Example Blog / Ten tips for PHP 8.4
```

Telo sa drží v objekte `Scraper`, takže jeden `Scraper` používaný naraz z viacerých miest (v asynchrónnom behovom prostredí) by telá pomiešal; používajte jeden `Scraper` na workera.

## Kódovanie znakov {#character-sets}

`parseUrl()` odovzdá parserom telo presne tak, ako prišlo; hlavička `Content-Type` sa nepoužíva. Ako s ním parsery zaobchádzajú:

- `OgDomParser` dekóduje kódovanie deklarované v HTML (`<meta charset>`, `http-equiv`) a dokumenty bez deklarácie číta ako UTF-8.
- `OgParser` a `SchemaParser` vracajú bajty tak, ako sú.

Stránku, ktorá nie je v UTF-8, preveďte skôr, než ju odovzdáte `OgParser` alebo `SchemaParser`. `mbstring` v PHP nepozná Windows-1250, `iconv` áno:

```php
$legacy = iconv('UTF-8', 'Windows-1250', '<title>Žltý kôň</title>');   // a Windows-1250 page

$utf8 = iconv('Windows-1250', 'UTF-8', $legacy);
echo new OgParser()->parse((string) $utf8)->getTitle(), "\n";
```

<!-- snippet: output -->
```text
Žltý kôň
```

[Príklad poškodených stránok](../examples/broken-pages.md) servíruje skutočnú stránku vo Windows-1250 a spracuje ju všetkými tromi parsermi.

## Komprimované a veľké odpovede

Guzzle si gzip a deflate odpovede vyžiada a dekóduje (`decode_content`, predvolene zapnuté). Celé telo sa pred spracovaním načíta do pamäte; obmedzte, čo sťahujete z nedôveryhodných hostiteľov (pozrite [Bezpečnosť](./security.md#resource-limits)).

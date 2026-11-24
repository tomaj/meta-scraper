---
description: Každá voľba konštruktora Scraper v knižnici meta-scraper - typ, predvolená hodnota a význam - a ako sa uplatnia s vlastným Guzzle klientom aj bez neho.
---

# Prehľad konfigurácie

Konštruktor `Scraper` je jediné miesto s konfiguráciou; parsery žiadnu nemajú.

<!-- snippet: skip -->
```php
public function __construct(array $guzzleOptions = [], ?\GuzzleHttp\ClientInterface $httpClient = null);
```

| Argument | Bez `$httpClient` | S `$httpClient` |
| --- | --- | --- |
| `$guzzleOptions` | Konfigurácia Guzzle klienta, ktorého vytvorí scraper, zlúčená s predvolenými hodnotami cez `array_merge()` | Voľby požiadavky posielané s každou požiadavkou `parseUrl()`; predvolené hodnoty sa nepoužijú |
| `$httpClient` | | Použije sa tak, ako je, pre každú požiadavku |

## Voľby {#options}

Voľby sú Guzzle [request options](https://docs.guzzlephp.org/en/stable/request-options.html) s rovnakým významom. Pole je typované (PHPStan tvar `array{…}`) pre tieto kľúče:

| Voľba | Typ | Predvolené v klientovi scrapera | Význam |
| --- | --- | --- | --- |
| `connect_timeout` | `int\|float` | `5` | Počet sekúnd čakania na TCP/TLS spojenie. `0` čaká donekonečna. |
| `timeout` | `int\|float` | žiadne (Guzzle `0`: donekonečna) | Počet sekúnd na celú požiadavku vrátane tela. Nastavte ho. |
| `read_timeout` | `int\|float` | PHP `default_socket_timeout` | Počet sekúnd čakania na dáta pri streamovanom tele. |
| `headers` | `array<string, string\|non-empty-list<string>>` | `['User-Agent' => 'Tomaj\Scraper']` | Hlavičky požiadavky. Nahrádzajú predvolené pole: uveďte aj `User-Agent`. |
| `proxy` | `string\|array{http?: string, https?: string, no?: list<string>}` | žiadne | URL proxy, alebo jedna pre každú schému plus hostitelia, ktorí ju obchádzajú. |
| `allow_redirects` | `bool\|array{max?: int, strict?: bool, referer?: bool, protocols?: non-empty-list<string>, track_redirects?: bool}` | Guzzle: najviac 5 presmerovaní, `http` a `https` | `false` presmerovania vypne (spracuje sa samotná odpoveď 3xx, zvyčajne s prázdnym telom). |
| `verify` | `bool\|string` | `true` | Overovanie TLS certifikátu alebo cesta k balíku CA. V produkcii nenastavujte `false`. |
| `http_errors` | `bool` | `true` | `true`: odpoveď 4xx/5xx vyhodí `FetchException`. `false`: chybová stránka sa spracuje ako každá iná. |
| `decode_content` | `bool\|string` | `true` | Dekódovanie gzip/deflate odpovedí; reťazec nastaví `Accept-Encoding`. |
| `force_ip_resolve` | `string` | žiadne | `'v4'` alebo `'v6'`, ak sa má použiť len IPv4 alebo IPv6. |
| `version` | `string\|float` | `1.1` | Verzia protokolu HTTP. |

Ostatné voľby Guzzle (`curl`, `on_stats`, `cookies`, `auth`, `handler`, …) sa za behu do Guzzle stále odovzdajú, ale statická analýza ich nahlási; nastavte ich radšej na vlastnom klientovi.

## Recepty

Crawler so slušnou identifikáciou, pevnými limitmi a bez presmerovaní na iné schémy:

```php
$scraper = new Scraper([
    'connect_timeout' => 3,
    'timeout' => 10,
    'headers' => [
        'User-Agent' => 'AcmeBot/1.0 (+https://acme.example/bot)',
        'Accept-Language' => 'sk, en;q=0.8',
    ],
    'allow_redirects' => ['max' => 3, 'protocols' => ['https']],
]);
```

Spracovanie chybových stránok namiesto vyhodenia výnimky (stránky „soft 404“ mávajú titulok):

```php
$scraper = new Scraper(['http_errors' => false, 'timeout' => 10]);
```

Všetko posielať cez odchádzajúcu proxy okrem interných hostiteľov:

```php
$scraper = new Scraper([
    'proxy' => ['http' => 'http://proxy.internal:3128', 'https' => 'http://proxy.internal:3128', 'no' => ['.internal']],
    'timeout' => 10,
]);
```

Vlastný klient so zachovanými predvolenými hodnotami scrapera plus voľba požiadavky:

```php
$client = new GuzzleHttp\Client([
    'connect_timeout' => 5,
    'headers' => ['User-Agent' => 'Tomaj\Scraper'],
    'cookies' => true,
]);
$scraper = new Scraper(['timeout' => 10], $client);
```

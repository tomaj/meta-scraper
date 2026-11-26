---
description: Každá výnimka, ktorú meta-scraper vyhadzuje - FetchException, MissingExtensionException a rozhranie ScraperException - kedy nastane a ako ju spracovať.
---

# Spracovanie chýb

meta-scraper vyhadzuje výnimky len v dvoch situáciách: stránku sa nepodarí stiahnuť alebo parser v tomto PHP nemôže fungovať. Samotné spracovanie HTML výnimku nikdy nevyhodí.

| Výnimka | Rozširuje | Vyhadzuje | Kedy |
| --- | --- | --- | --- |
| `Tomaj\Scraper\Exception\FetchException` | `RuntimeException` | `Scraper::parseUrl()` | požiadavka zlyhala |
| `Tomaj\Scraper\Exception\MissingExtensionException` | `LogicException` | `new OgDomParser()` | `ext-dom` alebo `ext-libxml` nie je načítané |
| `Tomaj\Scraper\Exception\ScraperException` | rozhranie (`Throwable`) | | implementujú ho obe, aby sa dalo zachytiť všetko z balíka |

## FetchException {#fetchexception}

<!-- snippet: skip -->
```php
final class FetchException extends \RuntimeException implements ScraperException
{
    public readonly string $url;                 // the URL you passed to parseUrl()
    // getPrevious(): the Guzzle exception
    // getMessage(): 'Cannot fetch "<url>": <Guzzle message>'
}
```

Každá `GuzzleHttp\Exception\GuzzleException` z požiadavky sa zabalí, takže všetky tieto prípady skončia ako `FetchException`:

| Príčina | Predchádzajúca výnimka (Guzzle 7) | Predchádzajúca výnimka (Guzzle 8) |
| --- | --- | --- |
| odpoveď 4xx (so zapnutým `http_errors`, predvolene) | `ClientException` | `ClientException` |
| odpoveď 5xx | `ServerException` | `ServerException` |
| viac presmerovaní ako `allow_redirects.max` | `TooManyRedirectsException` | `TooManyRedirectsException` |
| odmietnuté spojenie, chyba DNS | `ConnectException` | `ConnectException` |
| vypršal `timeout` | `ConnectException` | `NetworkTimeoutException` |
| chyby TLS, neplatné URL, iné chyby prenosu | iná `GuzzleException` | iná `GuzzleException` |

Konkrétne triedy Guzzle sa medzi Guzzle 7 a 8 líšia. Ak chcete reagovať na HTTP stav, kontrolujte `BadResponseException`, ktorú majú obe verzie:

```php
use GuzzleHttp\Exception\BadResponseException;

$scraper = new Scraper(['timeout' => 5]);
$missing = str_replace('article.html', 'missing.html', $url);

try {
    $meta = $scraper->parseUrl($missing, $parsers);
} catch (FetchException $e) {
    $previous = $e->getPrevious();
    if ($previous instanceof BadResponseException) {
        echo 'HTTP ', $previous->getResponse()->getStatusCode(), ' for ', basename($e->url), "\n";
    } else {
        echo 'Network error for ', $e->url, "\n";
    }
}
```

<!-- snippet: output -->
```text
HTTP 404 for missing.html
```

[Príklad chýb pri sťahovaní](../examples/fetch-errors.md) vyvolá každú príčinu voči lokálnemu serveru a beží rovnako na Guzzle 7 aj 8.

Po `FetchException` vracia `getBody()` stále telo z predchádzajúceho úspešného volania.

### Výnimky z vlastného klienta

`FetchException` balí len výnimky Guzzle. Výnimka z vášho vlastného middleware alebo z callbacku `on_redirect` na vašom klientovi (ktorá nie je `GuzzleException`) sa nezabalí a opustí `parseUrl()` tak, ako je. [Príklad náhľadu odkazu](../examples/link-preview.md) sa na to spolieha: jeho `BlockedUrlException` z `on_redirect` sa dostane k volajúcemu nezmenená.

## MissingExtensionException {#missingextensionexception}

`new OgDomParser()` skontroluje `ext-dom` a `ext-libxml` a ak jedno z nich chýba, vyhodí výnimku so správou ako `Tomaj\Scraper\Parser\OgDomParser requires the PHP extension "dom"`. Je to `LogicException`: opravte prostredie alebo použite náhradný `OgParser`:

```php
try {
    $metaTags = new OgDomParser();
} catch (MissingExtensionException) {
    $metaTags = new OgParser();
}
echo $metaTags::class, "\n";
```

<!-- snippet: output -->
```text
Tomaj\Scraper\Parser\OgDomParser
```

## Zachytenie všetkého

Obe výnimky implementujú `ScraperException`:

```php
try {
    $meta = new Scraper(['timeout' => 5])->parseUrl('http://127.0.0.1:9/', [new OgDomParser()]);
} catch (ScraperException $e) {
    echo $e::class, "\n";
}
```

<!-- snippet: output -->
```text
Tomaj\Scraper\Exception\FetchException
```

## Zlé HTML výnimku nevyhodí

Parsery vrátia, čo sa im podarilo prečítať, a zvyšok nechajú `null`:

| Vstup | Výsledok |
| --- | --- |
| `''` (prázdny reťazec) | prázdny `Meta` z každého parsera |
| neuzavreté tagy, tagy veľkými písmenami, atribúty bez úvodzoviek | `OgDomParser` ich prečíta; `OgParser` ich môže prehliadnuť |
| neplatný JSON v bloku JSON-LD | blok sa preskočí, ostatné bloky sa použijú |
| hodnota JSON-LD neočakávaného typu | ignoruje sa (`null`) |
| dátum, ktorý nie je absolútny (`not a date`, `yesterday noon`, `2026-02-30`) | ignoruje sa (`null`) |
| stránka v inom kódovaní | `OgDomParser` dekóduje deklarované kódovanie, ostatné parsery vrátia surové bajty |

```php
$meta = $scraper->parse('<TITLE>Broken page</TITLE><div><p>unclosed<script type="application/ld+json">{oops</script>', $parsers);
var_dump($meta->getTitle(), $meta->getAuthors());
```

<!-- snippet: output -->
```text
string(11) "Broken page"
array(0) {
}
```

Skontrolujte polia, ktoré potrebujete, a použite vlastné predvolené hodnoty: prázdny titulok je bežný výsledok, nie chyba. [Príklad poškodených stránok](../examples/broken-pages.md) ukazuje parsery na skutočných poškodených stránkach.

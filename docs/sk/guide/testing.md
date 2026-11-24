---
description: Testovanie kódu, ktorý používa meta-scraper, bez siete - Guzzle MockHandler, middleware history, HTML fixtures a lokálny server php -S.
---

# Testovanie vlastného kódu

Kód, ktorý volá `parse()`, nepotrebuje nič zvláštne: dajte mu HTML fixtures. Kód, ktorý volá `parseUrl()`, sťahuje stránky; v testoch dajte `Scraper` namiesto toho Guzzle klienta, ktorý odpovedá zo zoznamu pripravených odpovedí.

## Guzzle MockHandler

Ako druhý argument konštruktora odovzdajte klienta, ktorého handler je `MockHandler`. Pridajte middleware `history`, aby ste mohli skontrolovať, čo sa odoslalo:

```php
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;

$mock = new MockHandler([
    new Response(200, [], '<title>Test page</title>'),
    new Response(500),
]);
$history = [];
$stack = HandlerStack::create($mock);
$stack->push(Middleware::history($history));

$scraper = new Scraper(['headers' => ['User-Agent' => 'TestBot']], new Client(['handler' => $stack]));

echo $scraper->parseUrl('https://example.com/', [new OgParser()])->getTitle(), "\n";
try {
    $scraper->parseUrl('https://example.com/broken', [new OgParser()]);
} catch (FetchException $e) {
    echo 'FetchException for ', $e->url, "\n";
}
echo $history[0]['request']->getHeaderLine('User-Agent'), "\n";
```

<!-- snippet: output -->
```text
Test page
FetchException for https://example.com/broken
TestBot
```

`Scraper` do svojich služieb injektujte (cez konštruktor) namiesto toho, aby ste ho v nich vytvárali; testy potom môžu odovzdať tento.

[Príklad testovania](../examples/testing.md) takto testuje malú službu na import: úspech, 404, chybu spojenia, metódu požiadavky, URL, hlavičky a voľby.

## V PHPUnit

To isté nastavenie v testovacom prípade:

<!-- snippet: skip -->
```php
final class ArticleImporterTest extends TestCase
{
    public function testImportsTheOpenGraphTitle(): void
    {
        $mock = new MockHandler([new Response(200, [], file_get_contents(__DIR__ . '/fixtures/article.html'))]);
        $scraper = new Scraper([], new Client(['handler' => HandlerStack::create($mock)]));

        $article = new ArticleImporter($scraper)->import('https://news.example/a');

        self::assertSame('Expected title', $article['title']);
    }
}
```

Skutočné stránky, s ktorými ste mali problém, si nechajte ako fixtures (uložte ich cez `getBody()`), aby sa zmena parsera, ktorá ich pokazí, prejavila vo vašich testoch.

## Lokálny webový server

Pre end-to-end testy, ktoré majú ísť cez skutočné HTTP (presmerovania, timeouty, hlavičky), servírujte testovacie stránky vstavaným webovým serverom PHP a nasmerujte naň `parseUrl()`:

```bash
php -S 127.0.0.1:8080 -t tests/fixtures/site tests/fixtures/site/router.php
```

Takto fungujú všetky príklady tejto dokumentácie: `e2e.sh` každého príkladu spustí `php -S` s adresárom `site/` príkladu, spustí `run.php` a porovná výstup s `expected-output.txt`. `router.php` môže odpovedať s akýmkoľvek stavom, hlavičkou alebo oneskorením (pozrite [`fetch-errors/site/router.php`](../examples/fetch-errors.md#the-fixture-server)).

## Testovanie vlastného parsera

Parser je funkcia reťazca: testujte ho HTML reťazcami vrátane prázdneho reťazca a poškodeného kódu a skontrolujte, že dve volania vrátia dva rôzne objekty `Meta`. Pozrite si [Vlastný parser](./custom-parser.md#testing-a-parser).

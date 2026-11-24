---
description: Bezpečnosť knižnice meta-scraper - podvrhnutie požiadavky na strane servera (SSRF) pri sťahovaní URL od používateľov, limity zdrojov, nedôveryhodné hodnoty a nahlásenie zraniteľnosti.
---

# Bezpečnosť

meta-scraper spracúva HTML, ktoré písali tretie strany, a `parseUrl()` stiahne akúkoľvek URL, ktorú dostane. Na oboch záleží, len čo URL alebo stránky prichádzajú od používateľov.

## Podvrhnutie požiadavky na strane servera (SSRF)

Keď si URL môže vybrať používateľ (náhľad odkazu, „import z URL“, náhľad webhooku), môže prinútiť **váš server** požiadať o adresy, na ktoré sa sám nedostane:

- `http://localhost:6379/`, `http://127.0.0.1:9200/`: služby na samotnom serveri;
- `http://10.0.0.5/admin`, `http://192.168.1.1/`: privátna sieť;
- `http://169.254.169.254/latest/meta-data/`: služba metadát v cloude, ktorá vie vydať prístupové údaje;
- verejná URL, ktorá na niektorú z nich **presmeruje**.

`parseUrl()` URL nekontroluje. Skôr než ho zavoláte s URL od používateľa:

1. **Povoľte len `http` a `https`.** Odmietnite `file:`, `gopher:`, `ftp:` a všetko ostatné.
2. **Preložte hostiteľa a odmietnite privátne, loopback, link-local a rezervované adresy** (`FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE`), pre IPv4 aj IPv6.
3. **Pripnite spojenie k adrese, ktorú ste skontrolovali** (`CURLOPT_RESOLVE`), aby DNS medzi kontrolou a požiadavkou nemohlo vrátiť inú adresu (DNS rebinding).
4. **Rovnako skontrolujte každý cieľ presmerovania** (`on_redirect` na vlastnom Guzzle klientovi) alebo presmerovania vypnite (`'allow_redirects' => false`).
5. **Nastavte timeouty** a obmedzte počet presmerovaní.

Alebo posielajte všetky požiadavky scrapera cez odchádzajúcu proxy, ktorá blokuje interné siete (voľba `proxy`); tá pokryje aj to, na čo váš kód zabudne.

<Diagram name="link-preview" alt="Vloženú URL skontroluje strážca URL: schému, preloženú adresu a privátne rozsahy. Zablokované URL tam končia. Povolené URL sa stiahnu pripnuté ku skontrolovanej adrese, každý cieľ presmerovania sa kontroluje znova a prečítané hodnoty sa escapované vložia do karty náhľadu" />

[Príklad náhľadu odkazu](../examples/link-preview.md) implementuje kroky 1 až 5 v zhruba 60 riadkoch a ukazuje, ako blokujú `169.254.169.254`, `localhost`, privátne adresy, IPv6 loopback, iné schémy a presmerovanie na službu metadát. Ciele presmerovaní sa kontrolujú v `on_redirect`, ale cURL ich prekladá znova; aj túto medzeru uzavrie odchádzajúca proxy.

## Limity zdrojov {#resource-limits}

Nepriateľský alebo pokazený server môže odpovedať pomaly alebo s obrovským telom:

- nastavte `timeout` (celá požiadavka) a `connect_timeout`; predvolené nastavenie má len 5-sekundový timeout pripojenia;
- obmedzte presmerovania (`allow_redirects` s malým `max`);
- celé telo sa načíta do pamäte: ak chcete obmedziť jeho veľkosť, použite vlastný klient s voľbou `on_headers` (odmietnite veľký `Content-Length`) alebo callback `progress`, ktorý požiadavku po N bajtoch preruší;
- sťahovanie spúšťajte vo workeri fronty, nie vo webovej požiadavke používateľa, ktorý URL zadal.

## Nedôveryhodné hodnoty {#untrusted-values}

Všetko v `Meta` pochádza od autora stránky:

- **Escapujte** titulky, popisy, mená a URL predtým, než ich vložíte do HTML (`htmlspecialchars()` alebo automatické escapovanie vášho šablónovacieho systému).
- **Validujte URL** ako `getOgImage()` a `getOgUrl()` predtým, než na ne odkážete, vložíte ich alebo ich stiahnete: povoľte len `http`/`https` (`og:image` s hodnotou `javascript:alert(1)` je platný reťazec) a keď ich sťahuje váš server, považujte ich za vstup od používateľa (opäť SSRF).
- **Nedôverujte** `getOgUrl()` ako dôkazu, kde stránka naozaj je, ani autorovi a dátumom ako faktom: každá stránka môže tvrdiť čokoľvek.

```php
$meta = $scraper->parse('<meta property="og:title" content="&lt;img src=x onerror=alert(1)&gt;">', [new OgDomParser()]);

echo $meta->getOgTitle(), "\n";                                 // raw: never print this into HTML
echo htmlspecialchars((string) $meta->getOgTitle()), "\n";      // escaped
```

<!-- snippet: output -->
```text
<img src=x onerror=alert(1)>
&lt;img src=x onerror=alert(1)&gt;
```

## Náročnosť spracovania

Parsery pracujú s reťazcami v pamäti. `OgDomParser` zostaví DOM celého dokumentu a `SchemaParser` spustí `json_decode()` na každom bloku JSON-LD; ani jeden nenasleduje externé odkazy (žiadne externé entity, žiadne sťahovanie `@context`). Ak stránky pochádzajú z nedôveryhodných hostiteľov, obmedzte veľkosť tela, ako je uvedené vyššie.

## TLS

Guzzle predvolene overuje TLS certifikáty (`verify: true`). Nevypínajte to; pre internú CA odovzdajte ako `verify` cestu k jej balíku certifikátov.

## Nahlásenie zraniteľnosti

Neotvárajte verejný issue. Použite [súkromné hlásenie zraniteľností na GitHube](https://github.com/tomaj/meta-scraper/security/advisories/new) alebo pošlite e-mail správcovi; podrobnosti a podporované verzie sú v [bezpečnostných zásadách](https://github.com/tomaj/meta-scraper/blob/master/SECURITY.md).

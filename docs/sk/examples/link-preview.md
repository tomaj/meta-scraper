---
description: Bezpečné karty náhľadu pre URL, ktoré vkladajú používatelia - ochrana proti SSRF, pevne nastavené DNS, kontrolované presmerovania, timeouty a escapovaný výstup s meta-scraper.
---

# Príklad: náhľad odkazu (bezpečný voči SSRF)

Chat alebo formulár komentárov premení vložené URL na karty náhľadu: titulok, popis, obrázok, názov webu. URL pochádza od používateľa, takže server nesmie sťahovať interné adresy, nesmie na ne nasledovať presmerovanie a musí escapovať všetko, čo o sebe stránka tvrdí. Súvislosti opisuje stránka [Bezpečnosť](../guide/security.md).

<Diagram name="link-preview" alt="Vloženú URL skontroluje UrlGuard: schému, preloženú adresu a privátne rozsahy. Zablokované URL tam končia. Povolené URL sa stiahnu s pevne nastavenou skontrolovanou adresou, každý cieľ presmerovania sa kontroluje znova a prečítané hodnoty sa escapované vložia do karty náhľadu" />

<ProjectSource name="link-preview" :files="['src/UrlGuard.php', 'src/LinkPreview.php', 'run.php', 'site/hostile.html', 'site/router.php', 'expected-output.txt']" />

## Kontrola URL

<<< @/../examples/link-preview/src/UrlGuard.php

Testovací server beží na `127.0.0.1`, ktorú kontrola blokuje ako každú loopback adresu. `run.php` cez `$trustedHosts` dôveruje práve tomuto hostiteľovi a portu; kontrola v produkcii nemá žiadneho.

## Náhľad

<<< @/../examples/link-preview/src/LinkPreview.php

- `CURLOPT_RESOLVE` prinúti cURL pripojiť sa na IP adresu, ktorú kontrola overila, takže odpoveď DNS, ktorá sa po kontrole zmení (DNS rebinding), požiadavku nepresmeruje.
- `on_redirect` spustí kontrolu na každom cieli presmerovania. Jej `BlockedUrlException` nie je výnimka Guzzle, takže ju `parseUrl()` prepustí nezabalenú a volajúci rozlíši „zablokované“ od „nepodarilo sa stiahnuť“.
- URL obrázka sa prevedie na absolútnu a zahodí sa, ak nie je `http` ani `https`; `render()` escapuje každú hodnotu.

## Stránky

::: code-group

<<< @/../examples/link-preview/site/article.html [article.html]

<<< @/../examples/link-preview/site/hostile.html [hostile.html]

<<< @/../examples/link-preview/site/router.php [router.php]

:::

## Kód

<<< @/../examples/link-preview/run.php

## Výstup

<<< @/../examples/link-preview/expected-output.txt{text}

## Čo si všimnúť

- `article.html`: relatívny `og:image` sa prevedie na absolútnu URL; karta uprednostní `og:title` pred `<title>`.
- `plain.html`: vôbec žiadny Open Graph, takže karta použije `<title>` a názov hostiteľa.
- `hostile.html`: `<script>` v `og:title` a úvodzovka v `og:site_name` sa escapujú, obrázok s `javascript:` sa zahodí a dlhý popis sa skráti.
- `/to-metadata` je povolená URL, ktorá presmeruje na `169.254.169.254`: presmerovanie sa zablokuje skôr, než sa naň prejde.
- `/gone.html` neexistuje: `FetchException`, zobrazená ako „bez náhľadu“.
- Ostatné URL kontrola zablokuje ešte pred akoukoľvek požiadavkou: adresu metadát, `localhost`, privátnu adresu, IPv6 loopback, `file:` a `gopher:`.

## Čo kontroluje CI

Vykreslené HTML troch kariet (escapovanie, spracovanie obrázka, náhradné hodnoty) a presný dôvod pri každej zablokovanej URL vrátane presmerovania.

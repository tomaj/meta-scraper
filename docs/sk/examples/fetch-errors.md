---
description: Všetky spôsoby, ako môže parseUrl() v meta-scraper zlyhať - 404, 500, timeout, slučka presmerovaní, odmietnuté spojenie - zachytené ako FetchException, plus http_errors a allow_redirects.
---

# Príklad: chyby pri sťahovaní

Testovací server, ktorý na každú cestu odpovedá inak: stránka, presmerovanie, 404, 500, stránka, ktorá trvá tri sekundy, slučka presmerovaní a stránka, ktorá vráti poslaný `User-Agent`. `run.php` si každú vyžiada s timeoutom 1 sekunda a najviac 3 presmerovaniami a ukáže, v akej podobe chyba príde.

<ProjectSource name="fetch-errors" :files="['run.php', 'site/router.php', 'e2e.sh', 'expected-output.txt']" />

## Testovací server {#the-fixture-server}

<<< @/../examples/fetch-errors/site/router.php

## Kód

<<< @/../examples/fetch-errors/run.php

## Výstup

<<< @/../examples/fetch-errors/expected-output.txt{text}

## Čo si všimnúť

- **Jeden typ výnimky.** Chyby HTTP, timeout, slučka presmerovaní aj odmietnuté spojenie sú všetky `FetchException`. `$e->url` je URL, o ktorú ste žiadali; `getPrevious()` je výnimka Guzzle s podrobnosťami.
- **Guzzle 7 a 8.** Triedy výnimiek Guzzle sa medzi verziami líšia (timeout je v Guzzle 7 `ConnectException` a v Guzzle 8 `NetworkTimeoutException`). Funkcia `reason()` kontroluje `BadResponseException` a `TooManyRedirectsException`, ktoré majú obe verzie; príklad dáva s oboma verziami rovnaký výstup.
- **Vaše hlavičky sa posielajú.** `/echo-user-agent` ukazuje `User-Agent` z options.
- **`http_errors: false`** z 404 urobí bežný výsledok: chybová stránka sa spracuje a vráti sa jej titulok.
- **`allow_redirects: false`** vráti samotnú odpoveď `301`: prázdne telo, takže sa nič nenájde.
- **`ScraperException`** zachytí každú výnimku balíka.

## Čo kontroluje CI

Každý riadok výstupu: nasledované presmerovanie, vlastný `User-Agent`, stav 404 a 500, timeout po 1 sekunde, limit presmerovaní, odmietnuté spojenie a formát správy výnimky.

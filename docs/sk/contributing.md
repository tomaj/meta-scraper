# Ako prispieť

Príspevky sú vítané. Úplné pravidlá sú v [CONTRIBUTING.md](https://github.com/tomaj/meta-scraper/blob/master/CONTRIBUTING.md); projekt sa riadi [Kódexom správania](https://github.com/tomaj/meta-scraper/blob/master/CODE_OF_CONDUCT.md).

## Hlásenie chýb

Založte [issue](https://github.com/tomaj/meta-scraper/issues/new/choose) s čo najmenším HTML, na ktorom je problém vidieť, s parserom, ktorý používate, a s očakávanými a skutočnými hodnotami.

Bezpečnostné zraniteľnosti sa hlásia neverejne: pozrite [bezpečnostné zásady](https://github.com/tomaj/meta-scraper/blob/master/SECURITY.md).

## Vývoj

meta-scraper podporuje PHP 8.4, 8.5 a 8.6.

```bash
git clone https://github.com/tomaj/meta-scraper.git
cd meta-scraper
composer install
composer check      # code style, PHPStan, Rector, tests
composer infection  # mutation testing (needs Xdebug or PCOV)
```

Každá zmena správania potrebuje test a záznam v `CHANGELOG.md`. Testy nikdy nepoužívajú sieť.

## Dokumentácia

Tento web sa zostavuje nástrojom [VitePress](https://vitepress.dev/) z priečinka [`docs/`](https://github.com/tomaj/meta-scraper/tree/master/docs). Anglické stránky sú v koreni `docs/`, slovenské v `docs/sk/` s rovnakými cestami; kontrola v CI (`npm run docs:locales`) zlyhá, keď niektorej stránke chýba náprotivok v druhom jazyku.

```bash
cd docs
npm ci
npm run docs:dev    # local preview
npm run docs:links  # build, check the SVGs, the illustrations and every link
```

Kód na stránkach pochádza z troch miest a všetok je otestovaný:

| Kde | Ako sa dostane na stránku | Čím sa testuje |
| --- | --- | --- |
| `examples/<name>/` | `<<< @/../examples/<name>/run.php` | workflow Examples: `examples/ci/recipe.sh <name>` spustí príklad proti checkoutu a porovná jeho výstup s `expected-output.txt` |
| `docs/snippets/*.php` | `<<< @/snippets/<file>.php` | `DocsSnippetsTest`: snippety so súborom `.out` musia vypísať presne jeho obsah; PHPStan analyzuje všetky |
| PHP bloky kódu v `README.md`, `UPGRADE-5.0.md` a `docs/**/*.md` | priamo v texte | `DocsSnippetsTest`: každý blok sa spustí (s preddefinovanými `$html`, `$url`, `$scraper` a `$parsers`), pokiaľ nenasleduje za `<!-- snippet: skip -->`; blok `text` za `<!-- snippet: output -->` je jeho očakávaný výstup |

Keď zámerne zmeníte výstup príkladu, upravte v tom istom commite aj jeho `expected-output.txt`.

### Ilustrácie a grafika značky

- Úvodnú ilustráciu a diagramy v `docs/public/illustrations/` a `docs/public/diagrams/` generuje v svetlej a tmavej verzii, po anglicky aj po slovensky, skript `python3 docs/scripts/illustrations.py` zo šablón, ktoré obsahuje; `npm run docs:links` zlyhá, keď je niektorý vygenerovaný súbor zastaraný.
- Logo (`logo.svg`, `logo-dark.svg`) a `favicon.svg` sú kreslené ručne. `npm run brand` z nich v headless Chrome vyrenderuje `og.png`, PNG ikony a `favicon.ico` (`CHROME_PATH`, ak Chrome nie je na štandardnom mieste); výsledky commitnite.
- `scripts/check-svg.py` kontroluje, či je každé SVG správne zapísané.

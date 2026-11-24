#!/usr/bin/env python3
"""Write the hand-drawn SVG illustrations and diagrams of the site in a light and a dark variant.

Every drawing is one template with colour placeholders, so both variants always show the same
thing. Run after changing a drawing and commit the generated files:

    python3 docs/scripts/illustrations.py           # write docs/public/{illustrations,diagrams}/*.svg (English and -sk)
    python3 docs/scripts/illustrations.py --check   # fail when a generated file is out of date
"""

from __future__ import annotations

import sys
from pathlib import Path
from string import Template

PUBLIC = Path(__file__).resolve().parents[1] / "public"

PALETTES = {
    "light": {
        "bg": "#ffffff", "panel": "#f8fafc", "line": "#cbd5e1", "ink": "#0f172a", "muted": "#64748b",
        "brand": "#0f766e", "brandSoft": "#ccfbf1", "brandMid": "#5eead4", "accent": "#d97706", "accentSoft": "#fef3c7",
        "tag": "#0f766e", "attr": "#b45309", "str": "#0369a1", "danger": "#dc2626", "dangerSoft": "#fee2e2",
        "sky": "#e0f2fe", "hill": "#5eead4", "hill2": "#14b8a6", "sun": "#fbbf24", "shadow": "#0f172a", "shadowOpacity": "0.08",
    },
    "dark": {
        "bg": "#1e293b", "panel": "#0f172a", "line": "#334155", "ink": "#e2e8f0", "muted": "#94a3b8",
        "brand": "#2dd4bf", "brandSoft": "#134e4a", "brandMid": "#14b8a6", "accent": "#fbbf24", "accentSoft": "#422006",
        "tag": "#5eead4", "attr": "#fbbf24", "str": "#7dd3fc", "danger": "#f87171", "dangerSoft": "#450a0a",
        "sky": "#0c4a6e", "hill": "#0d9488", "hill2": "#115e59", "sun": "#fbbf24", "shadow": "#000000", "shadowOpacity": "0.35",
    },
}

FONT = "font-family=\"ui-monospace, SFMono-Regular, Menlo, Consolas, monospace\""
SANS = "font-family=\"Inter, ui-sans-serif, system-ui, -apple-system, 'Segoe UI', sans-serif\""

ARROW = """<marker id="arrow" viewBox="0 0 10 10" refX="8" refY="5" markerWidth="7" markerHeight="7" orient="auto-start-reverse"><path d="M1 1l8 4-8 4z" fill="$brand"/></marker>"""

def hero(t: dict[str, str]) -> Template:
    return Template(f"""<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 560 480" width="560" height="480" role="img" aria-label="{t['aria']}">
  <defs>{ARROW}</defs>
  <rect x="14" y="16" width="336" height="214" rx="16" fill="$shadow" opacity="$shadowOpacity" transform="translate(4 6)"/>
  <rect x="14" y="16" width="336" height="214" rx="16" fill="$bg" stroke="$line" stroke-width="1.5"/>
  <path d="M14 32a16 16 0 0 1 16-16h304a16 16 0 0 1 16 16v20H14z" fill="$panel"/>
  <path d="M14 52h336" stroke="$line" stroke-width="1.5"/>
  <circle cx="36" cy="34" r="5" fill="#f87171"/><circle cx="52" cy="34" r="5" fill="#fbbf24"/><circle cx="68" cy="34" r="5" fill="#34d399"/>
  <rect x="86" y="25" width="236" height="18" rx="9" fill="$bg" stroke="$line"/>
  <text x="98" y="38" {SANS} font-size="10.5" fill="$muted">{t['url']}</text>
  <g {FONT} font-size="12">
    <text x="32" y="80"><tspan fill="$tag">&lt;title&gt;</tspan><tspan fill="$ink">{t['headline']}</tspan><tspan fill="$tag">&lt;/title&gt;</tspan></text>
    <text x="32" y="104"><tspan fill="$tag">&lt;meta </tspan><tspan fill="$attr">property</tspan><tspan fill="$muted">=</tspan><tspan fill="$str">"og:image"</tspan><tspan fill="$tag"> …&gt;</tspan></text>
    <text x="32" y="128"><tspan fill="$tag">&lt;meta </tspan><tspan fill="$attr">name</tspan><tspan fill="$muted">=</tspan><tspan fill="$str">"author"</tspan><tspan fill="$tag"> …&gt;</tspan></text>
    <text x="32" y="152"><tspan fill="$tag">&lt;script </tspan><tspan fill="$attr">type</tspan><tspan fill="$muted">=</tspan><tspan fill="$str">"application/ld+json"</tspan><tspan fill="$tag">&gt;</tspan></text>
    <text x="32" y="176"><tspan fill="$ink">  {{"@type": </tspan><tspan fill="$str">"NewsArticle"</tspan><tspan fill="$ink">, …}}</tspan></text>
    <text x="32" y="200"><tspan fill="$tag">&lt;/script&gt;</tspan></text>
  </g>
  <path d="M182 236v18" stroke="$brand" stroke-width="2.5" fill="none" marker-end="url(#arrow)"/>
  <g {SANS} font-size="13" font-weight="600" text-anchor="middle">
    <rect x="14" y="262" width="122" height="32" rx="16" fill="$brandSoft" stroke="$brand" stroke-width="1.5"/>
    <text x="75" y="283" fill="$brand">SchemaParser</text>
    <rect x="146" y="262" width="122" height="32" rx="16" fill="$brandSoft" stroke="$brand" stroke-width="1.5"/>
    <text x="207" y="283" fill="$brand">OgDomParser</text>
    <rect x="278" y="262" width="104" height="32" rx="16" fill="$accentSoft" stroke="$accent" stroke-width="1.5"/>
    <text x="330" y="283" fill="$accent">{t['yours']}</text>
  </g>
  <path d="M207 300c0 18 30 22 60 22" stroke="$brand" stroke-width="2.5" fill="none" marker-end="url(#arrow)"/>
  <rect x="276" y="300" width="270" height="164" rx="18" fill="$shadow" opacity="$shadowOpacity" transform="translate(4 6)"/>
  <rect x="276" y="300" width="270" height="164" rx="18" fill="$bg" stroke="$brand" stroke-width="2"/>
  <rect x="292" y="314" width="58" height="22" rx="11" fill="$brand"/>
  <text x="321" y="329.5" {SANS} font-size="12" font-weight="700" fill="$bg" text-anchor="middle">Meta</text>
  <rect x="292" y="348" width="88" height="100" rx="10" fill="$sky"/>
  <circle cx="358" cy="372" r="9" fill="$sun"/>
  <path d="M292 424l26-30 20 20 14-12 28 26v10a10 10 0 0 1-10 10h-68a10 10 0 0 1-10-10z" fill="$hill2"/>
  <path d="M292 438l30-22 22 14 36-6v14a10 10 0 0 1-10 10h-68a10 10 0 0 1-10-10z" fill="$hill"/>
  <g {SANS} font-size="11">
    <text x="394" y="360" fill="$muted">{t['title']}</text>
    <text x="394" y="375" fill="$ink" font-weight="600">{t['headline']}</text>
    <text x="394" y="396" fill="$muted">{t['authors']}</text>
    <text x="394" y="411" fill="$ink" font-weight="600">Zuzana Kováčová</text>
    <text x="394" y="432" fill="$muted">{t['published']}</text>
    <text x="394" y="447" fill="$ink" font-weight="600">2026-11-20 05:30 UTC</text>
  </g>
</svg>
""")


def box(x: int, y: int, w: int, h: int, title: str, sub: str, kind: str = "brand", top: bool = False) -> str:
    fill, stroke, ink = {"brand": ("$brandSoft", "$brand", "$brand"), "accent": ("$accentSoft", "$accent", "$accent"),
                         "plain": ("$bg", "$line", "$ink"), "danger": ("$dangerSoft", "$danger", "$danger")}[kind]
    cx = x + w / 2
    out = f'<rect x="{x}" y="{y}" width="{w}" height="{h}" rx="12" fill="{fill}" stroke="{stroke}" stroke-width="1.5"/>'
    ty = y + 28 if top else y + h / 2 - (3 if sub else -5)
    out += f'<text x="{cx}" y="{ty}" {SANS} font-size="14" font-weight="700" fill="{ink}" text-anchor="middle">{title}</text>'
    if sub:
        out += f'<text x="{cx}" y="{y + h / 2 + 15}" {SANS} font-size="11.5" fill="$muted" text-anchor="middle">{sub}</text>'
    return out


def arrow(d: str, dashed: bool = False) -> str:
    dash = ' stroke-dasharray="5 5"' if dashed else ""
    return f'<path d="{d}" stroke="$brand" stroke-width="2" fill="none"{dash} marker-end="url(#arrow)"/>'


def label(x: float, y: float, text: str, anchor: str = "middle") -> str:
    return f'<text x="{x}" y="{y}" {SANS} font-size="11.5" fill="$muted" text-anchor="{anchor}">{text}</text>'


def svg(width: int, height: int, alt: str, body: str) -> Template:
    return Template(f'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 {width} {height}" width="{width}" height="{height}" role="img" aria-label="{alt}">\n'
                    f'  <defs>{ARROW}</defs>\n  <rect width="{width}" height="{height}" rx="16" fill="$panel"/>\n  {body}\n</svg>\n')


def how_it_works(t: dict[str, str]) -> Template:
    return svg(820, 300, t['aria'], "".join([
    box(24, 40, 170, 64, "parseUrl($$url)", t['get']),
    box(24, 190, 170, 64, "parse($$html)", t['have'], "plain"),
    arrow("M194 72h46"),
    label(110, 140, t['failure']),
    box(244, 40, 132, 64, t['string'], "getBody()", "plain"),
    arrow("M310 104v36c0 14 10 20 34 20"),
    arrow("M194 222h106c22 0 26-14 44-14"),
    box(348, 140, 150, 40, "parser 1", ""),
    box(348, 188, 150, 40, "parser 2", ""),
    box(348, 236, 150, 40, "parser n", "", "accent"),
    arrow("M498 160h46"), arrow("M498 208h46"), arrow("M498 256h46"),
    box(548, 140, 120, 136, "merge()", t['first'], "plain"),
    arrow("M668 208h34"),
    box(706, 160, 96, 96, "Meta", t['getters']),
    label(423, 128, t['each']),
]))

def merge(t: dict[str, str]) -> Template:
    return svg(820, 270, t['aria'], "".join([
    f'<text x="24" y="34" {SANS} font-size="13" font-weight="700" fill="$ink">Scraper::parse($$html, [new SchemaParser(), new OgDomParser()])</text>',
    box(24, 58, 220, 180, "SchemaParser", "", "plain", top=True),
    box(300, 58, 220, 180, "OgDomParser", "", "plain", top=True),
    box(576, 58, 220, 180, "Meta", "", "brand", top=True),
    "".join(f'<text x="40" y="{118 + i * 26}" {SANS} font-size="12" fill="$ink">{t}</text>' for i, t in enumerate([f"{t['title']}: Schema headline", f"{t['authors']}: Peter, Eva", f"{t['image']}: —", f"{t['site']}: —"])),
    "".join(f'<text x="316" y="{118 + i * 26}" {SANS} font-size="12" fill="{c}">{t}</text>' for i, (t, c) in enumerate([(f"{t['title']}: Page | Site", "$muted"), (f"{t['authors']}: Peter", "$muted"), (f"{t['image']}: og.jpg", "$ink"), (f"{t['site']}: Site", "$ink")])),
    f'<path d="M316 114h{t["strike1"]}M316 140h{t["strike2"]}" stroke="$danger" stroke-width="1.5"/>',
    "".join(f'<text x="592" y="{118 + i * 26}" {SANS} font-size="12" fill="$ink">{t}</text>' for i, t in enumerate([f"{t['title']}: Schema headline", f"{t['authors']}: Peter, Eva", f"{t['image']}: og.jpg", f"{t['site']}: Site"])),
    arrow("M244 148h52"), arrow("M520 148h52"),
    label(410, 258, t['struck']),
]))

def link_preview(t: dict[str, str]) -> Template:
    return svg(820, 250, t['aria'], "".join([
    box(20, 90, 120, 64, t['pasted'], t['user'], "plain"),
    arrow("M140 122h34"),
    box(178, 80, 160, 84, "UrlGuard", t['checks']),
    arrow("M258 164v34"),
    box(178, 200, 160, 40, t['blocked'], "", "danger"),
    arrow("M338 122h34"),
    box(376, 80, 160, 84, "parseUrl()", t['pinned']),
    '<path d="M456 80c0-40-70-48-104-48c-30 0-60 10-94 46" stroke="$accent" stroke-width="2" fill="none" stroke-dasharray="5 5" marker-end="url(#arrow)"/>',
    label(372, 26, t['redirects']),
    arrow("M536 122h34"),
    box(574, 80, 100, 84, "Meta", t['untrusted']),
    arrow("M674 122h30"),
    box(708, 80, 96, 84, t['card'], t['escaped']),
]))

# Labels per language. English files keep their names; Slovak files get a -sk suffix
# (illustrations/hero-sk-light.svg, diagrams/merge-sk-dark.svg …) and are used by the pages under docs/sk/.
STRINGS = {
    "": {
        "hero": {
            "aria": "An HTML page with title, Open Graph and JSON-LD tags goes through three parsers and becomes one Meta object with title, image, authors and dates",
            "url": "citydaily.example/night-tram", "headline": "Night tram line", "yours": "your parser",
            "title": "title", "authors": "authors", "published": "published",
        },
        "how-it-works": {
            "aria": "parseUrl downloads the page with Guzzle; parse takes HTML you already have; each parser returns a new Meta; Meta merge keeps the first value found; the result is one Meta object",
            "get": "GET with Guzzle", "have": "HTML you already have", "failure": "FetchException on failure",
            "string": "HTML string", "first": "first value wins", "getters": "getters", "each": "each returns a new Meta",
        },
        "merge": {
            "aria": "SchemaParser runs first and sets the title and authors; OgDomParser runs second: its title is ignored because the field is already set, its image and site name fill empty fields",
            "title": "title", "authors": "authors", "image": "image", "site": "site name", "strike1": "150", "strike2": "100",
            "struck": "struck out: the field is already set, the later value is ignored",
        },
        "link-preview": {
            "aria": "A pasted URL is checked by the URL guard: scheme, resolved address, private ranges. Blocked URLs stop there. Allowed URLs are fetched pinned to the checked address, every redirect target is checked again, then the parsed values are escaped into a preview card",
            "pasted": "pasted URL", "user": "from a user", "checks": "scheme, DNS, private IP", "blocked": "blocked",
            "pinned": "pinned IP, timeouts", "redirects": "every redirect target is checked again",
            "untrusted": "untrusted", "card": "card", "escaped": "escaped",
        },
    },
    "-sk": {
        "hero": {
            "aria": "HTML stránka s titulkom, Open Graph a JSON-LD tagmi prejde tromi parsermi a stane sa z nej jeden objekt Meta s titulkom, obrázkom, autormi a dátumami",
            "url": "dennik.example/nocna-elektricka", "headline": "Nočná električka", "yours": "váš parser",
            "title": "titulok", "authors": "autori", "published": "publikované",
        },
        "how-it-works": {
            "aria": "parseUrl stiahne stránku cez Guzzle; parse dostane HTML, ktoré už máte; každý parser vráti nový Meta; zlúčenie ponechá prvú nájdenú hodnotu; výsledkom je jeden objekt Meta",
            "get": "GET cez Guzzle", "have": "HTML, ktoré už máte", "failure": "FetchException pri chybe",
            "string": "HTML reťazec", "first": "platí prvá hodnota", "getters": "gettery", "each": "každý vráti nový Meta",
        },
        "merge": {
            "aria": "SchemaParser beží prvý a nastaví titulok a autorov; OgDomParser beží druhý: jeho titulok sa ignoruje, lebo pole je už vyplnené, jeho obrázok a názov webu doplnia prázdne polia",
            "title": "titulok", "authors": "autori", "image": "obrázok", "site": "názov webu", "strike1": "134", "strike2": "86",
            "struck": "prečiarknuté: pole je už vyplnené, neskoršia hodnota sa ignoruje",
        },
        "link-preview": {
            "aria": "Vloženú URL overí UrlGuard: schému, preloženú adresu a privátne rozsahy. Zablokované URL tam skončia. Povolené URL sa stiahnu s IP pevne nastavenou na overenú adresu, každý cieľ presmerovania sa overí znova a prečítané hodnoty sa escapujú do karty náhľadu",
            "pasted": "vložená URL", "user": "od používateľa", "checks": "schéma, DNS, privátna IP", "blocked": "zablokované",
            "pinned": "pevná IP, timeouty", "redirects": "každý cieľ presmerovania sa overí znova",
            "untrusted": "nedôveryhodné", "card": "karta", "escaped": "escapované",
        },
    },
}

DRAWINGS = {}
for suffix, strings in STRINGS.items():
    DRAWINGS[f"illustrations/hero{suffix}"] = hero(strings["hero"])
    DRAWINGS[f"diagrams/how-it-works{suffix}"] = how_it_works(strings["how-it-works"])
    DRAWINGS[f"diagrams/merge{suffix}"] = merge(strings["merge"])
    DRAWINGS[f"diagrams/link-preview{suffix}"] = link_preview(strings["link-preview"])


def main(argv: list[str]) -> int:
    check = "--check" in argv
    stale = []
    for name, template in DRAWINGS.items():
        for variant, palette in PALETTES.items():
            path = PUBLIC / f"{name}-{variant}.svg"
            content = template.substitute(palette)
            if check:
                if not path.is_file() or path.read_text(encoding="utf-8") != content:
                    stale.append(str(path))
                continue
            path.parent.mkdir(parents=True, exist_ok=True)
            path.write_text(content, encoding="utf-8")
            print(f"wrote {path.relative_to(PUBLIC.parent)}")
    if stale:
        print("Out of date, run python3 docs/scripts/illustrations.py:\n" + "\n".join(stale), file=sys.stderr)
        return 1
    return 0


if __name__ == "__main__":
    sys.exit(main(sys.argv))

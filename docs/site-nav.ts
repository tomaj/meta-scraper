// Navigation and sidebar. 5.x is the current major and lives at the site root; Slovak mirrors it under /sk/.
import type { DefaultTheme } from 'vitepress'

export const nav: DefaultTheme.NavItem[] = [
  { text: 'Guide', link: '/guide/getting-started', activeMatch: '^/guide/' },
  { text: 'Examples', link: '/examples/', activeMatch: '^/examples/' },
  { text: 'Reference', link: '/api/reference', activeMatch: '^/api/' },
  { text: 'FAQ', link: '/faq' },
  {
    text: '5.x (next)',
    items: [
      { text: 'Changelog', link: '/changelog' },
      { text: 'Upgrade 4.x → 5.0', link: '/upgrade' },
      { text: 'Releases', link: 'https://github.com/tomaj/meta-scraper/releases' },
    ],
  },
]

const g = (text: string, page: string) => ({ text, link: `/guide/${page}` })
const e = (text: string, page: string) => ({ text, link: `/examples/${page}` })

export const sidebar: DefaultTheme.SidebarItem[] = [
  { text: 'Introduction', items: [g('Getting started', 'getting-started'), g('How it works', 'how-it-works')] },
  {
    text: 'Parsers',
    items: [
      g('Choosing a parser', 'parsers'),
      g('Meta tags and Open Graph', 'open-graph'),
      g('schema.org JSON-LD', 'json-ld'),
      g('Combining parsers', 'combining'),
      g('Writing a parser', 'custom-parser'),
    ],
  },
  {
    text: 'Using it',
    items: [
      g('The Meta object', 'meta'),
      g('Fetching pages', 'fetching'),
      g('Configuration reference', 'configuration'),
      g('Error handling', 'errors'),
      g('Testing your code', 'testing'),
      g('Security', 'security'),
    ],
  },
  {
    text: 'Examples',
    items: [
      { text: 'Overview', link: '/examples/' },
      e('Open Graph article', 'open-graph-article'),
      e('JSON-LD @graph', 'json-ld-graph'),
      e('Twitter card parser', 'twitter-card'),
      e('Broken pages', 'broken-pages'),
      e('Fetch errors', 'fetch-errors'),
      e('Link preview (SSRF-safe)', 'link-preview'),
      e('Testing with a mock', 'testing'),
    ],
  },
  {
    text: 'More',
    items: [
      { text: 'API reference', link: '/api/reference' },
      { text: 'FAQ', link: '/faq' },
      { text: 'Upgrade 4.x → 5.0', link: '/upgrade' },
      { text: 'Changelog', link: '/changelog' },
      { text: 'Contributing', link: '/contributing' },
    ],
  },
]

// Slovak mirror under /sk/: the same pages and order as above.
export const navSk: DefaultTheme.NavItem[] = [
  { text: 'Príručka', link: '/sk/guide/getting-started', activeMatch: '^/sk/guide/' },
  { text: 'Príklady', link: '/sk/examples/', activeMatch: '^/sk/examples/' },
  { text: 'Referencia', link: '/sk/api/reference', activeMatch: '^/sk/api/' },
  { text: 'Časté otázky', link: '/sk/faq' },
  {
    text: '5.x (vo vývoji)',
    items: [
      { text: 'Zoznam zmien', link: '/sk/changelog' },
      { text: 'Prechod 4.x → 5.0', link: '/sk/upgrade' },
      { text: 'Vydania', link: 'https://github.com/tomaj/meta-scraper/releases' },
    ],
  },
]

const gs = (text: string, page: string) => ({ text, link: `/sk/guide/${page}` })
const es = (text: string, page: string) => ({ text, link: `/sk/examples/${page}` })

export const sidebarSk: DefaultTheme.SidebarItem[] = [
  { text: 'Úvod', items: [gs('Začíname', 'getting-started'), gs('Ako to funguje', 'how-it-works')] },
  {
    text: 'Parsery',
    items: [
      gs('Výber parsera', 'parsers'),
      gs('Meta tagy a Open Graph', 'open-graph'),
      gs('schema.org JSON-LD', 'json-ld'),
      gs('Kombinovanie parserov', 'combining'),
      gs('Vlastný parser', 'custom-parser'),
    ],
  },
  {
    text: 'Použitie',
    items: [
      gs('Objekt Meta', 'meta'),
      gs('Sťahovanie stránok', 'fetching'),
      gs('Prehľad konfigurácie', 'configuration'),
      gs('Spracovanie chýb', 'errors'),
      gs('Testovanie vlastného kódu', 'testing'),
      gs('Bezpečnosť', 'security'),
    ],
  },
  {
    text: 'Príklady',
    items: [
      { text: 'Prehľad', link: '/sk/examples/' },
      es('Článok s Open Graph', 'open-graph-article'),
      es('JSON-LD @graph', 'json-ld-graph'),
      es('Parser Twitter kariet', 'twitter-card'),
      es('Poškodené stránky', 'broken-pages'),
      es('Chyby pri sťahovaní', 'fetch-errors'),
      es('Náhľad odkazu (bezpečný voči SSRF)', 'link-preview'),
      es('Testovanie s mockom', 'testing'),
    ],
  },
  {
    text: 'Ďalšie',
    items: [
      { text: 'Referencia API', link: '/sk/api/reference' },
      { text: 'Časté otázky', link: '/sk/faq' },
      { text: 'Prechod zo 4.x na 5.0', link: '/sk/upgrade' },
      { text: 'Zoznam zmien', link: '/sk/changelog' },
      { text: 'Ako prispieť', link: '/sk/contributing' },
    ],
  },
]

// Navigation and sidebar. 5.x is the current major and lives at the site root.
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

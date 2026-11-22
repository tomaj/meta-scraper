import { defineConfig } from 'vitepress'

const description = 'PHP library that extracts title, description, Open Graph and schema.org JSON-LD metadata from HTML pages'
const siteUrl = 'https://tomaj.github.io/meta-scraper/'

// https://vitepress.dev/reference/site-config
export default defineConfig({
  title: 'Meta Scraper',
  description,
  base: '/meta-scraper/',
  cleanUrls: false,
  lastUpdated: true,

  head: [
    ['link', { rel: 'icon', type: 'image/svg+xml', href: '/meta-scraper/favicon.svg' }],
    ['meta', { name: 'theme-color', content: '#0f766e' }],
    ['meta', { property: 'og:type', content: 'website' }],
    ['meta', { property: 'og:title', content: 'Meta Scraper - PHP page metadata parser' }],
    ['meta', { property: 'og:description', content: description }],
    ['meta', { property: 'og:url', content: siteUrl }],
    ['meta', { name: 'twitter:card', content: 'summary' }],
  ],

  sitemap: {
    hostname: siteUrl,
  },

  themeConfig: {
    logo: '/logo.svg',

    nav: [
      { text: 'Guide', link: '/guide/getting-started' },
      { text: 'API', link: '/api/reference' },
      {
        text: '5.x (next)',
        items: [
          { text: 'Changelog', link: '/changelog' },
          { text: 'Upgrade 4.x → 5.0', link: 'https://github.com/tomaj/meta-scraper/blob/master/UPGRADE-5.0.md' },
          { text: 'Releases', link: 'https://github.com/tomaj/meta-scraper/releases' },
        ],
      },
    ],

    sidebar: [
      {
        text: 'Guide',
        items: [
          { text: 'Getting Started', link: '/guide/getting-started' },
          { text: 'Parsers', link: '/guide/parsers' },
          { text: 'Fetching Pages', link: '/guide/fetching' },
        ],
      },
      {
        text: 'Reference',
        items: [
          { text: 'API Reference', link: '/api/reference' },
          { text: 'FAQ', link: '/faq' },
          { text: 'Changelog', link: '/changelog' },
          { text: 'Contributing', link: '/contributing' },
        ],
      },
    ],

    socialLinks: [
      { icon: 'github', link: 'https://github.com/tomaj/meta-scraper' },
    ],

    editLink: {
      pattern: 'https://github.com/tomaj/meta-scraper/edit/master/docs/:path',
      text: 'Edit this page on GitHub',
    },

    search: {
      provider: 'local',
    },

    footer: {
      message: 'Released under the MIT License.',
      copyright: 'Copyright © 2015-2026 Tomas Majer',
    },
  },
})

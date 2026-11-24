import { defineConfig } from 'vitepress'
import { nav, sidebar } from '../site-nav'
import { seoHead, siteUrl } from './seo'

const base = process.env.DOCS_BASE || '/meta-scraper/'
const description = 'PHP library that reads the title, description, Open Graph tags and schema.org JSON-LD of any HTML page into one object'

// https://vitepress.dev/reference/site-config
export default defineConfig({
  srcExclude: ['public/**', 'scripts/**', 'snippets/**', 'README.md'],
  title: 'Meta Scraper',
  description,
  lang: 'en',
  base,
  cleanUrls: false,
  lastUpdated: true,

  head: [
    ['link', { rel: 'icon', href: `${base}favicon.ico`, sizes: '48x48' }],
    ['link', { rel: 'icon', type: 'image/svg+xml', href: `${base}favicon.svg` }],
    ['link', { rel: 'icon', type: 'image/png', sizes: '32x32', href: `${base}favicon-32.png` }],
    ['link', { rel: 'icon', type: 'image/png', sizes: '16x16', href: `${base}favicon-16.png` }],
    ['link', { rel: 'apple-touch-icon', sizes: '180x180', href: `${base}apple-touch-icon.png` }],
    ['link', { rel: 'manifest', href: `${base}site.webmanifest` }],
    ['meta', { name: 'theme-color', content: '#0f766e' }],
  ],

  sitemap: {
    hostname: siteUrl,
  },

  transformHead({ pageData, siteConfig }) {
    return seoHead(pageData, siteConfig.srcDir, description)
  },

  markdown: {
    languageAlias: { neon: 'yaml' },
  },

  themeConfig: {
    logo: { light: '/logo.svg', dark: '/logo-dark.svg', alt: 'Meta Scraper' },
    siteTitle: 'Meta Scraper',

    nav,
    sidebar,

    outline: { level: [2, 3] },

    socialLinks: [
      { icon: 'github', link: 'https://github.com/tomaj/meta-scraper' },
    ],

    editLink: {
      pattern: 'https://github.com/tomaj/meta-scraper/edit/master/docs/:path',
      text: 'Edit this page on GitHub',
    },

    lastUpdated: {
      text: 'Last updated',
      formatOptions: { dateStyle: 'medium' },
    },

    search: {
      provider: 'local',
    },

    footer: {
      message: 'Released under the <a href="https://github.com/tomaj/meta-scraper/blob/master/LICENSE.md">MIT License</a>.',
      copyright: 'Copyright © 2015-2026 Tomas Majer',
    },
  },
})

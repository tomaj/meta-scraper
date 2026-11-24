import { defineConfig } from 'vitepress'
import { nav, navSk, sidebar, sidebarSk } from '../site-nav'
import { seoHead, siteUrl } from './seo'

const base = process.env.DOCS_BASE || '/meta-scraper/'
const description = 'PHP library that reads the title, description, Open Graph tags and schema.org JSON-LD of any HTML page into one object'
const descriptionSk = 'PHP knižnica, ktorá z ľubovoľnej HTML stránky prečíta titulok, popis, Open Graph tagy a schema.org JSON-LD do jedného objektu'

// https://vitepress.dev/reference/site-config
export default defineConfig({
  srcExclude: ['public/**', 'scripts/**', 'snippets/**', 'README.md'],
  title: 'Meta Scraper',
  description,
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
    return seoHead(pageData, siteConfig.srcDir, pageData.relativePath.startsWith('sk/') ? descriptionSk : description)
  },

  // English is the default locale at the site root; Slovak mirrors every page under /sk/.
  locales: {
    root: {
      label: 'English',
      lang: 'en-US',
      description,
    },
    sk: {
      label: 'Slovenčina',
      lang: 'sk-SK',
      link: '/sk/',
      description: descriptionSk,
      themeConfig: {
        nav: navSk,
        sidebar: sidebarSk,
        outline: { level: [2, 3], label: 'Na tejto stránke' },
        docFooter: {
          prev: 'Predchádzajúca stránka',
          next: 'Nasledujúca stránka',
        },
        editLink: {
          pattern: 'https://github.com/tomaj/meta-scraper/edit/master/docs/:path',
          text: 'Upraviť túto stránku na GitHube',
        },
        lastUpdated: {
          text: 'Naposledy upravené',
          formatOptions: { dateStyle: 'medium' },
        },
        darkModeSwitchLabel: 'Vzhľad',
        lightModeSwitchTitle: 'Prepnúť na svetlý režim',
        darkModeSwitchTitle: 'Prepnúť na tmavý režim',
        sidebarMenuLabel: 'Ponuka',
        returnToTopLabel: 'Späť na začiatok',
        langMenuLabel: 'Zmeniť jazyk',
        skipToContentLabel: 'Preskočiť na obsah',
        footer: {
          message: 'Vydané pod <a href="https://github.com/tomaj/meta-scraper/blob/master/LICENSE.md">licenciou MIT</a>.',
          copyright: 'Copyright © 2015-2026 Tomas Majer',
        },
        notFound: {
          code: '404',
          title: 'STRÁNKA SA NENAŠLA',
          quote: 'Táto stránka neexistuje alebo sa presunula. Skúste vyhľadávanie alebo začnite od úvodu.',
          linkLabel: 'prejsť na úvod',
          linkText: 'Späť na úvod',
        },
      },
    },
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
      options: {
        locales: {
          sk: {
            translations: {
              button: {
                buttonText: 'Hľadať',
                buttonAriaLabel: 'Hľadať',
              },
              modal: {
                displayDetails: 'Zobraziť podrobný zoznam',
                resetButtonTitle: 'Vymazať hľadanie',
                backButtonTitle: 'Zavrieť hľadanie',
                noResultsText: 'Žiadne výsledky pre',
                footer: {
                  selectText: 'vybrať',
                  selectKeyAriaLabel: 'enter',
                  navigateText: 'prejsť',
                  navigateUpKeyAriaLabel: 'šípka hore',
                  navigateDownKeyAriaLabel: 'šípka dole',
                  closeText: 'zavrieť',
                  closeKeyAriaLabel: 'escape',
                },
              },
            },
          },
        },
      },
    },

    footer: {
      message: 'Released under the <a href="https://github.com/tomaj/meta-scraper/blob/master/LICENSE.md">MIT License</a>.',
      copyright: 'Copyright © 2015-2026 Tomas Majer',
    },
  },
})

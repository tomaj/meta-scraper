// Per-page SEO: title, canonical URL, Open Graph and Twitter tags, hreflang alternates and JSON-LD. Used by transformHead in config.mts.
import { existsSync, readFileSync } from 'node:fs'
import { resolve } from 'node:path'
import type { HeadConfig, PageData } from 'vitepress'

const base = process.env.DOCS_BASE || '/meta-scraper/'
export const siteUrl = `https://tomaj.github.io${base}`
const ogImage = `${siteUrl}og.png`
// Slovak pages get the social card with the Slovak tagline.
const ogImageSk = `${siteUrl}og-sk.png`
const siteName = 'Meta Scraper'
const repoUrl = 'https://github.com/tomaj/meta-scraper'

const person = {
  '@type': 'Person',
  '@id': 'https://github.com/tomaj#person',
  name: 'Tomas Majer',
  url: 'https://github.com/tomaj',
}

export function pageUrl(relativePath: string): string {
  return siteUrl + relativePath.replace(/(^|\/)index\.md$/, '$1').replace(/\.md$/, '.html')
}

// English pages live at the site root, Slovak pages mirror them under sk/.
export function localeOf(relativePath: string): { prefix: string, lang: string, ogLocale: string } {
  return relativePath.startsWith('sk/')
    ? { prefix: 'sk/', lang: 'sk-SK', ogLocale: 'sk_SK' }
    : { prefix: '', lang: 'en-US', ogLocale: 'en_US' }
}

// Section names for the breadcrumb, from the first path segment after the locale.
const sections: Record<string, Record<string, [string, string]>> = {
  '': {
    guide: ['Guide', 'guide/getting-started.html'],
    examples: ['Examples', 'examples/'],
    api: ['Reference', 'api/reference.html'],
  },
  'sk/': {
    guide: ['Príručka', 'guide/getting-started.html'],
    examples: ['Príklady', 'examples/'],
    api: ['Referencia', 'api/reference.html'],
  },
}

// The same page in the other locale, when it exists, for hreflang links.
export function alternates(relativePath: string, srcDir: string): { lang: string, href: string }[] {
  const { prefix } = localeOf(relativePath)
  const base = relativePath.slice(prefix.length)
  const pairs: [string, string][] = [['en', base], ['sk', `sk/${base}`]]
  const found = pairs.filter(([, path]) => existsSync(resolve(srcDir, path)))
  if (found.length < 2) return []
  return [
    ...found.map(([lang, path]) => ({ lang, href: pageUrl(path) })),
    { lang: 'x-default', href: pageUrl(base) },
  ]
}

// Markdown to plain text, enough for JSON-LD answers.
function plain(markdown: string): string {
  return markdown
    .replace(/```[\s\S]*?```/g, '')
    .replace(/^<<< .*$/gm, '')
    .replace(/\[([^\]]+)\]\([^)]*\)/g, '$1')
    .replace(/<[^>]+>/g, '')
    .replace(/[*`]/g, '')
    .replace(/\s+/g, ' ')
    .trim()
}

// faq.md: every `##` heading is a question; other pages: the `###` headings under `## FAQ`
// (Slovak: `## Časté otázky {#faq}`).
function faqEntries(source: string, relativePath: string): { q: string, a: string }[] {
  let section = source
  let level = '## '
  if (relativePath !== 'faq.md' && relativePath !== 'sk/faq.md') {
    const heading = /\n## (FAQ|Časté otázky(?: \{#faq\})?)\n/.exec(source)
    if (!heading) return []
    section = source.slice(heading.index + heading[0].length)
    const next = section.search(/\n## /)
    if (next >= 0) section = section.slice(0, next)
    level = '### '
  }
  return ('\n' + section).split('\n' + level).slice(1)
    .map((part) => {
      const [q, ...rest] = part.split('\n')
      return { q: plain(q), a: plain(rest.join('\n').split(/\n#{2,3} /)[0]) }
    })
    .filter((e) => e.q && e.a)
}

export function seoHead(page: PageData, srcDir: string, siteDescription: string): HeadConfig[] {
  if (page.isNotFound || !page.relativePath) {
    return [['meta', { name: 'robots', content: 'noindex' }]]
  }
  const url = pageUrl(page.relativePath)
  const locale = localeOf(page.relativePath)
  const sk = locale.prefix === 'sk/'
  const image = sk ? ogImageSk : ogImage
  const isHome = page.frontmatter.layout === 'home'
  const homeTitle = sk ? 'Meta Scraper – Open Graph a JSON-LD metadáta pre PHP' : 'Meta Scraper – Open Graph and JSON-LD metadata for PHP'
  const title = isHome ? homeTitle : `${page.title} | ${siteName}`
  const description = page.description || page.frontmatter.description || siteDescription
  const source = readFileSync(resolve(srcDir, page.relativePath), 'utf8')
  const modified = page.lastUpdated ? new Date(page.lastUpdated).toISOString() : undefined

  const head: HeadConfig[] = [
    ['link', { rel: 'canonical', href: url }],
    ['meta', { property: 'og:type', content: isHome ? 'website' : 'article' }],
    ['meta', { property: 'og:site_name', content: siteName }],
    ['meta', { property: 'og:locale', content: locale.ogLocale }],
    ['meta', { property: 'og:locale:alternate', content: sk ? 'en_US' : 'sk_SK' }],
    ['meta', { property: 'og:title', content: title }],
    ['meta', { property: 'og:description', content: description }],
    ['meta', { property: 'og:url', content: url }],
    ['meta', { property: 'og:image', content: image }],
    ['meta', { property: 'og:image:width', content: '1200' }],
    ['meta', { property: 'og:image:height', content: '630' }],
    ['meta', { property: 'og:image:alt', content: sk ? 'Meta Scraper: HTML stránka prejde parsermi a stane sa z nej jeden objekt Meta' : 'Meta Scraper: an HTML page goes through parsers and becomes one Meta object' }],
    ['meta', { name: 'twitter:card', content: 'summary_large_image' }],
    ['meta', { name: 'twitter:title', content: title }],
    ['meta', { name: 'twitter:description', content: description }],
    ['meta', { name: 'twitter:image', content: image }],
    ['meta', { name: 'author', content: person.name }],
  ]
  if (modified && !isHome) head.push(['meta', { property: 'article:modified_time', content: modified }])
  for (const alt of alternates(page.relativePath, srcDir)) {
    head.push(['link', { rel: 'alternate', hreflang: alt.lang, href: alt.href }])
  }

  const website = { '@type': 'WebSite', '@id': `${siteUrl}#website`, name: siteName, url: siteUrl, inLanguage: ['en-US', 'sk-SK'], publisher: { '@id': person['@id'] } }
  const graph: Record<string, unknown>[] = [person, website]
  if (isHome) {
    graph.push({
      '@type': ['SoftwareSourceCode', 'SoftwareApplication'],
      '@id': `${siteUrl}#software`,
      name: 'tomaj/meta-scraper',
      description: siteDescription,
      url: siteUrl + locale.prefix,
      inLanguage: locale.lang,
      codeRepository: repoUrl,
      programmingLanguage: { '@type': 'ComputerLanguage', name: 'PHP' },
      runtimePlatform: 'PHP 8.4+',
      applicationCategory: 'DeveloperApplication',
      operatingSystem: 'Linux, macOS, Windows',
      license: 'https://opensource.org/licenses/MIT',
      author: { '@id': person['@id'] },
      image,
      offers: { '@type': 'Offer', price: '0', priceCurrency: 'USD' },
    })
  } else {
    graph.push({
      '@type': 'TechArticle',
      '@id': `${url}#article`,
      headline: page.title,
      description,
      url,
      mainEntityOfPage: url,
      inLanguage: locale.lang,
      image,
      author: { '@id': person['@id'] },
      publisher: { '@id': person['@id'] },
      isPartOf: { '@id': `${siteUrl}#website` },
      about: { '@type': 'SoftwareSourceCode', name: 'tomaj/meta-scraper', codeRepository: repoUrl },
      ...(modified ? { dateModified: modified } : {}),
    })
    const home = siteUrl + locale.prefix
    const items = [{ name: siteName, item: home }]
    const section = sections[locale.prefix][page.relativePath.slice(locale.prefix.length).split('/')[0]]
    if (section && home + section[1] !== url) items.push({ name: section[0], item: home + section[1] })
    items.push({ name: page.title, item: url })
    graph.push({
      '@type': 'BreadcrumbList',
      itemListElement: items.map((entry, i) => ({ '@type': 'ListItem', position: i + 1, name: entry.name, item: entry.item })),
    })
  }

  const faq = faqEntries(source, page.relativePath)
  if (faq.length > 0) {
    graph.push({
      '@type': 'FAQPage',
      '@id': `${url}#faq`,
      mainEntity: faq.map((e) => ({ '@type': 'Question', name: e.q, acceptedAnswer: { '@type': 'Answer', text: e.a } })),
    })
  }

  head.push(['script', { type: 'application/ld+json' }, JSON.stringify({ '@context': 'https://schema.org', '@graph': graph })])
  return head
}

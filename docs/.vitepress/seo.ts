// Per-page SEO: title, canonical URL, Open Graph and Twitter tags, and JSON-LD. Used by transformHead in config.mts.
import { readFileSync } from 'node:fs'
import { resolve } from 'node:path'
import type { HeadConfig, PageData } from 'vitepress'

const base = process.env.DOCS_BASE || '/meta-scraper/'
export const siteUrl = `https://tomaj.github.io${base}`
const ogImage = `${siteUrl}og.png`
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

const sections: Record<string, [string, string]> = {
  guide: ['Guide', 'guide/getting-started.html'],
  examples: ['Examples', 'examples/'],
  api: ['Reference', 'api/reference.html'],
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

// faq.md: every `##` heading is a question; other pages: the `###` headings under `## FAQ`.
function faqEntries(source: string, relativePath: string): { q: string, a: string }[] {
  let section = source
  let level = '## '
  if (relativePath !== 'faq.md') {
    const start = source.indexOf('\n## FAQ\n')
    if (start < 0) return []
    section = source.slice(start + 8)
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
  const isHome = page.frontmatter.layout === 'home'
  const title = isHome ? 'Meta Scraper – Open Graph and JSON-LD metadata for PHP' : `${page.title} | ${siteName}`
  const description = page.description || page.frontmatter.description || siteDescription
  const source = readFileSync(resolve(srcDir, page.relativePath), 'utf8')
  const modified = page.lastUpdated ? new Date(page.lastUpdated).toISOString() : undefined

  const head: HeadConfig[] = [
    ['link', { rel: 'canonical', href: url }],
    ['meta', { property: 'og:type', content: isHome ? 'website' : 'article' }],
    ['meta', { property: 'og:site_name', content: siteName }],
    ['meta', { property: 'og:locale', content: 'en_US' }],
    ['meta', { property: 'og:title', content: title }],
    ['meta', { property: 'og:description', content: description }],
    ['meta', { property: 'og:url', content: url }],
    ['meta', { property: 'og:image', content: ogImage }],
    ['meta', { property: 'og:image:width', content: '1200' }],
    ['meta', { property: 'og:image:height', content: '630' }],
    ['meta', { property: 'og:image:alt', content: 'Meta Scraper: an HTML page goes through parsers and becomes one Meta object' }],
    ['meta', { name: 'twitter:card', content: 'summary_large_image' }],
    ['meta', { name: 'twitter:title', content: title }],
    ['meta', { name: 'twitter:description', content: description }],
    ['meta', { name: 'twitter:image', content: ogImage }],
    ['meta', { name: 'author', content: person.name }],
  ]
  if (modified && !isHome) head.push(['meta', { property: 'article:modified_time', content: modified }])

  const website = { '@type': 'WebSite', '@id': `${siteUrl}#website`, name: siteName, url: siteUrl, inLanguage: 'en', publisher: { '@id': person['@id'] } }
  const graph: Record<string, unknown>[] = [person, website]
  if (isHome) {
    graph.push({
      '@type': ['SoftwareSourceCode', 'SoftwareApplication'],
      '@id': `${siteUrl}#software`,
      name: 'tomaj/meta-scraper',
      description: siteDescription,
      url: siteUrl,
      codeRepository: repoUrl,
      programmingLanguage: { '@type': 'ComputerLanguage', name: 'PHP' },
      runtimePlatform: 'PHP 8.4+',
      applicationCategory: 'DeveloperApplication',
      operatingSystem: 'Linux, macOS, Windows',
      license: 'https://opensource.org/licenses/MIT',
      author: { '@id': person['@id'] },
      image: ogImage,
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
      inLanguage: 'en',
      image: ogImage,
      author: { '@id': person['@id'] },
      publisher: { '@id': person['@id'] },
      isPartOf: { '@id': `${siteUrl}#website` },
      about: { '@type': 'SoftwareSourceCode', name: 'tomaj/meta-scraper', codeRepository: repoUrl },
      ...(modified ? { dateModified: modified } : {}),
    })
    const items = [{ name: siteName, item: siteUrl }]
    const section = sections[page.relativePath.split('/')[0]]
    if (section && siteUrl + section[1] !== url) items.push({ name: section[0], item: siteUrl + section[1] })
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

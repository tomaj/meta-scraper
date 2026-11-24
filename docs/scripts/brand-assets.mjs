// Renders the bitmap brand assets from the SVG sources in public/ with headless Chrome:
//   public/og.png and og-sk.png (1200 × 630 social cards, English and Slovak), favicon-16/32.png, favicon.ico (16, 32, 48),
//   apple-touch-icon.png (180), icon-192.png and icon-512.png (site.webmanifest).
// Run after changing logo.svg, favicon.svg or the hero illustration, and commit the files:
//   CHROME_PATH=/path/to/chrome npm run brand
import { existsSync, readFileSync, writeFileSync } from 'node:fs'
import puppeteer from 'puppeteer-core'

const pub = (path) => new URL(`../public/${path}`, import.meta.url)
const read = (path) => readFileSync(pub(path), 'utf8')
const chrome = process.env.CHROME_PATH
  || ['/Applications/Google Chrome.app/Contents/MacOS/Google Chrome', '/usr/bin/google-chrome', '/usr/bin/chromium'].find(existsSync)

const browser = await puppeteer.launch({ executablePath: chrome, args: ['--no-sandbox'] })
const page = await browser.newPage()

async function render(html, width, height, path) {
  await page.setViewport({ width, height, deviceScaleFactor: 1 })
  await page.setContent(`<html><body style="margin:0;width:${width}px;height:${height}px;overflow:hidden">${html}</body></html>`)
  const png = await page.screenshot({ omitBackground: true, type: 'png' })
  if (path) writeFileSync(pub(path), png)
  return Buffer.from(png)
}

// favicons: the small mark on a transparent background
const favicon = read('favicon.svg')
const icon = (size) => favicon.replace('<svg', `<svg style="display:block;width:${size}px;height:${size}px"`)
const icons = {}
for (const size of [16, 32, 48]) icons[size] = await render(icon(size), size, size, size === 48 ? null : `favicon-${size}.png`)

// app icons: the logo on a full square, as launchers mask the corners themselves
const logo = read('logo.svg')
for (const [size, name] of [[180, 'apple-touch-icon.png'], [192, 'icon-192.png'], [512, 'icon-512.png']]) {
  await render(`<div style="width:${size}px;height:${size}px;background:#0f766e;display:flex;align-items:center;justify-content:center">${logo.replace('<svg', `<svg style="width:${Math.round(size * 1.12)}px;height:${Math.round(size * 1.12)}px"`)}</div>`, size, size, name)
}

// favicon.ico with PNG images (supported by every browser since Windows Vista)
const sizes = [16, 32, 48]
const header = Buffer.alloc(6 + 16 * sizes.length)
header.writeUInt16LE(0, 0)
header.writeUInt16LE(1, 2)
header.writeUInt16LE(sizes.length, 4)
let offset = header.length
sizes.forEach((size, i) => {
  const entry = 6 + 16 * i
  header.writeUInt8(size, entry)
  header.writeUInt8(size, entry + 1)
  header.writeUInt16LE(1, entry + 4)
  header.writeUInt16LE(32, entry + 6)
  header.writeUInt32LE(icons[size].length, entry + 8)
  header.writeUInt32LE(offset, entry + 12)
  offset += icons[size].length
})
writeFileSync(pub('favicon.ico'), Buffer.concat([header, ...sizes.map((size) => icons[size])]))

// social cards: og.png (English) and og-sk.png (Slovak, for the /sk/ pages)
const cards = [
  { file: 'og.png', suffix: '', tagline: 'Title, description, Open Graph and schema.org JSON-LD from any HTML page, in PHP' },
  { file: 'og-sk.png', suffix: '-sk', tagline: 'Titulok, popis, Open Graph a schema.org JSON-LD z ľubovoľnej HTML stránky, v PHP' },
]
for (const card of cards) {
  const hero = read(`illustrations/hero${card.suffix}-light.svg`).replace('<svg', '<svg style="width:560px;height:480px"')
  await render(`<div style="width:1200px;height:630px;background:linear-gradient(135deg,#f0fdfa 0%,#ffffff 55%,#fffbeb 100%);display:flex;align-items:center;gap:40px;padding:0 56px;box-sizing:border-box;font-family:Inter,Helvetica,Arial,sans-serif">
  <div style="flex:1">
    ${logo.replace('<svg', '<svg style="width:120px;height:120px"')}
    <div style="font-size:76px;font-weight:800;color:#134e4a;line-height:1.05;margin-top:22px">Meta Scraper</div>
    <div style="font-size:30px;color:#334155;margin-top:18px;line-height:1.3">${card.tagline}</div>
    <div style="font-size:22px;color:#0f766e;margin-top:26px;font-weight:600">composer require tomaj/meta-scraper</div>
  </div>
  ${hero}
</div>`, 1200, 630, card.file)
}

await browser.close()
console.log('public/og.png, og-sk.png, favicon-16.png, favicon-32.png, favicon.ico, apple-touch-icon.png, icon-192.png, icon-512.png written')

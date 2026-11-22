---
layout: home

hero:
  name: Meta Scraper
  text: Page metadata for PHP
  tagline: Title, description, Open Graph and schema.org JSON-LD from any HTML page, as one object.
  image:
    src: /logo.svg
    alt: Meta Scraper
  actions:
    - theme: brand
      text: Get Started
      link: /guide/getting-started
    - theme: alt
      text: Parsers
      link: /guide/parsers
    - theme: alt
      text: View on GitHub
      link: https://github.com/tomaj/meta-scraper

features:
  - title: Open Graph and meta tags
    details: og:title, og:description, og:image, og:url, og:site_name, og:type, article:section, published and modified time, plus title, description, keywords and author.
  - title: schema.org JSON-LD
    details: Reads every application/ld+json block, @graph arrays and @id references, and picks the article (NewsArticle, BlogPosting, …) or the web page object.
  - title: Combine parsers
    details: Run several parsers in the order you choose; the first one that finds a value wins, the next ones fill the gaps.
  - title: Fetch with Guzzle
    details: parseUrl() downloads the page with Guzzle 7 or 8, with your timeouts, User-Agent or your own configured client. Failures throw one package exception.
  - title: Regular expressions or DOM
    details: OgParser needs no extension; OgDomParser uses ext-dom and handles any attribute order and character set.
  - title: Small and tested
    details: PHP 8.4+, PHPStan level max, 99 % coverage and mutation testing; a custom parser is one interface with one method.
---

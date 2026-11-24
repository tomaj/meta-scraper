---
description: Build link preview cards for URLs that users paste, safely - an SSRF guard, DNS pinning, checked redirects, timeouts and escaped output with meta-scraper.
---

# Example: link preview (SSRF-safe)

A chat or comment form turns pasted URLs into preview cards: title, description, image, site name. The URL comes from a user, so the server must not fetch internal addresses, must not follow a redirect there, and must escape what the page says about itself. See [Security](../guide/security.md) for the background.

<Diagram name="link-preview" alt="A pasted URL is checked by the URL guard: scheme, resolved address, private ranges. Blocked URLs stop there. Allowed URLs are fetched pinned to the checked address, every redirect target is checked again, and the parsed values are escaped into a preview card" />

<ProjectSource name="link-preview" :files="['src/UrlGuard.php', 'src/LinkPreview.php', 'run.php', 'site/hostile.html', 'site/router.php', 'expected-output.txt']" />

## The guard

<<< @/../examples/link-preview/src/UrlGuard.php

The fixture server runs on `127.0.0.1`, which the guard blocks like every loopback address. `run.php` trusts exactly that host and port through `$trustedHosts`; a production guard has none.

## The preview

<<< @/../examples/link-preview/src/LinkPreview.php

- `CURLOPT_RESOLVE` makes cURL connect to the IP address the guard checked, so a DNS answer that changes after the check (DNS rebinding) cannot redirect the request.
- `on_redirect` runs the guard on every redirect target. Its `BlockedUrlException` is not a Guzzle exception, so `parseUrl()` lets it through unwrapped and the caller can tell "blocked" from "could not download".
- The image URL is made absolute and dropped unless it is `http` or `https`; `render()` escapes every value.

## The pages

::: code-group

<<< @/../examples/link-preview/site/article.html [article.html]

<<< @/../examples/link-preview/site/hostile.html [hostile.html]

<<< @/../examples/link-preview/site/router.php [router.php]

:::

## The code

<<< @/../examples/link-preview/run.php

## Output

<<< @/../examples/link-preview/expected-output.txt{text}

## What to notice

- `article.html`: the relative `og:image` is made absolute; the card prefers `og:title` over `<title>`.
- `plain.html`: no Open Graph at all, so the card falls back to `<title>` and the host name.
- `hostile.html`: the `<script>` in `og:title` and the quote in `og:site_name` are escaped, the `javascript:` image is dropped, the long description is shortened.
- `/to-metadata` is an allowed URL that redirects to `169.254.169.254`: the redirect is blocked before it is followed.
- `/gone.html` does not exist: a `FetchException`, shown as "no preview".
- The other URLs are blocked by the guard before any request: the metadata address, `localhost`, a private address, IPv6 loopback, `file:` and `gopher:`.

## What CI checks

The rendered HTML of the three cards (escaping, image handling, fallbacks) and the exact reason for every blocked URL, including the redirect.

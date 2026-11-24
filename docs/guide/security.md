---
description: Security considerations for meta-scraper - server-side request forgery when scraping user-supplied URLs, resource limits, untrusted values, and how to report a vulnerability.
---

# Security

meta-scraper parses HTML written by third parties and `parseUrl()` downloads whatever URL it gets. Both matter as soon as URLs or pages come from users.

## Server-side request forgery (SSRF)

When a user can choose the URL (a link preview, "import from URL", a webhook preview), they can make **your server** request addresses they cannot reach themselves:

- `http://localhost:6379/`, `http://127.0.0.1:9200/`: services on the server itself;
- `http://10.0.0.5/admin`, `http://192.168.1.1/`: the private network;
- `http://169.254.169.254/latest/meta-data/`: the cloud metadata service, which can hand out credentials;
- a public URL that **redirects** to any of these.

`parseUrl()` does not check URLs. Before you call it with a URL from a user:

1. **Allow only `http` and `https`.** Reject `file:`, `gopher:`, `ftp:` and everything else.
2. **Resolve the host and reject private, loopback, link-local and reserved addresses** (`FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE`), for IPv4 and IPv6.
3. **Pin the connection to the address you checked** (`CURLOPT_RESOLVE`), so DNS cannot return a different address between your check and the request (DNS rebinding).
4. **Check every redirect target the same way** (`on_redirect` on your own Guzzle client), or disable redirects (`'allow_redirects' => false`).
5. **Set timeouts** and limit the number of redirects.

Or send all scraping requests through an egress proxy that blocks internal networks (`proxy` option), which also covers what your code forgets.

<Diagram name="link-preview" alt="A pasted URL is checked by the URL guard: scheme, resolved address, private ranges. Blocked URLs stop there. Allowed URLs are fetched pinned to the checked address, every redirect target is checked again, and the parsed values are escaped into a preview card" />

The [link preview example](../examples/link-preview.md) implements steps 1 to 5 in about 60 lines and shows them blocking `169.254.169.254`, `localhost`, private addresses, IPv6 loopback, other schemes and a redirect to the metadata service. Redirect targets are checked in `on_redirect` but resolved again by cURL; an egress proxy closes that gap too.

## Resource limits

A hostile or broken server can answer slowly or with a huge body:

- set `timeout` (whole request) and `connect_timeout`; the default only has a 5 second connect timeout;
- limit redirects (`allow_redirects` with a small `max`);
- the whole body is read into memory: to cap its size, use your own client with the `on_headers` option (reject a large `Content-Length`) or a `progress` callback that aborts after N bytes;
- run scraping in a queue worker, not in the web request of the user who submitted the URL.

## Untrusted values

Everything in `Meta` comes from the page author:

- **Escape** titles, descriptions, names and URLs before you put them into HTML (`htmlspecialchars()` or your template engine's auto-escaping).
- **Validate URLs** such as `getOgImage()` and `getOgUrl()` before you link, embed or download them: allow only `http`/`https` (an `og:image` of `javascript:alert(1)` is a valid string), and treat them as user input when your server fetches them (SSRF again).
- **Do not trust** `getOgUrl()` as proof of where the page lives, or the author and dates as facts: any page can claim anything.

```php
$meta = $scraper->parse('<meta property="og:title" content="&lt;img src=x onerror=alert(1)&gt;">', [new OgDomParser()]);

echo $meta->getOgTitle(), "\n";                                 // raw: never print this into HTML
echo htmlspecialchars((string) $meta->getOgTitle()), "\n";      // escaped
```

<!-- snippet: output -->
```text
<img src=x onerror=alert(1)>
&lt;img src=x onerror=alert(1)&gt;
```

## Parsing cost

The parsers work on strings in memory. `OgDomParser` builds a DOM of the whole document and `SchemaParser` runs `json_decode()` on every JSON-LD block; neither follows external references (no external entities, no `@context` downloads). Limit the body size as above if pages come from untrusted hosts.

## TLS

Guzzle verifies TLS certificates by default (`verify: true`). Do not turn it off; for an internal CA, pass the path of its bundle as `verify`.

## Reporting a vulnerability

Do not open a public issue. Use [GitHub private vulnerability reporting](https://github.com/tomaj/meta-scraper/security/advisories/new) or email the maintainer; the [security policy](https://github.com/tomaj/meta-scraper/blob/master/SECURITY.md) has the details and the supported versions.

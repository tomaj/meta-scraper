---
description: Every option of the meta-scraper Scraper constructor - type, default and meaning - and how they apply with and without your own Guzzle client.
---

# Configuration reference

The `Scraper` constructor is the only place with configuration; parsers have none.

<!-- snippet: skip -->
```php
public function __construct(array $guzzleOptions = [], ?\GuzzleHttp\ClientInterface $httpClient = null);
```

| Argument | Without `$httpClient` | With `$httpClient` |
| --- | --- | --- |
| `$guzzleOptions` | Configuration of the Guzzle client the scraper creates, merged over the defaults with `array_merge()` | Request options sent with every `parseUrl()` request; the defaults are not used |
| `$httpClient` | | Used as it is, for every request |

## Options

The options are Guzzle [request options](https://docs.guzzlephp.org/en/stable/request-options.html) with the same meaning. The array is typed (a PHPStan `array{…}` shape) for these keys:

| Option | Type | Default in the scraper's client | Meaning |
| --- | --- | --- | --- |
| `connect_timeout` | `int\|float` | `5` | Seconds to wait for the TCP/TLS connection. `0` waits forever. |
| `timeout` | `int\|float` | none (Guzzle `0`: forever) | Seconds for the whole request, including the body. Set it. |
| `read_timeout` | `int\|float` | PHP `default_socket_timeout` | Seconds to wait for data on a streamed body. |
| `headers` | `array<string, string\|non-empty-list<string>>` | `['User-Agent' => 'Tomaj\Scraper']` | Request headers. Replaces the default array: include a `User-Agent`. |
| `proxy` | `string\|array{http?: string, https?: string, no?: list<string>}` | none | Proxy URL, or one per scheme plus hosts that bypass it. |
| `allow_redirects` | `bool\|array{max?: int, strict?: bool, referer?: bool, protocols?: non-empty-list<string>, track_redirects?: bool}` | Guzzle: up to 5 redirects, `http` and `https` | `false` disables redirects (the 3xx response itself is parsed, usually an empty body). |
| `verify` | `bool\|string` | `true` | TLS certificate verification, or a path to a CA bundle. Do not set `false` in production. |
| `http_errors` | `bool` | `true` | `true`: a 4xx/5xx response throws `FetchException`. `false`: the error page is parsed like any other. |
| `decode_content` | `bool\|string` | `true` | Decode gzip/deflate responses; a string sets the `Accept-Encoding`. |
| `force_ip_resolve` | `string` | none | `'v4'` or `'v6'` to use only IPv4 or IPv6. |
| `version` | `string\|float` | `1.1` | HTTP protocol version. |

Other Guzzle options (`curl`, `on_stats`, `cookies`, `auth`, `handler`, …) are still passed to Guzzle at runtime, but static analysis reports them; configure them on your own client instead.

## Recipes

A crawler with a polite identity, hard limits and no redirects to other schemes:

```php
$scraper = new Scraper([
    'connect_timeout' => 3,
    'timeout' => 10,
    'headers' => [
        'User-Agent' => 'AcmeBot/1.0 (+https://acme.example/bot)',
        'Accept-Language' => 'sk, en;q=0.8',
    ],
    'allow_redirects' => ['max' => 3, 'protocols' => ['https']],
]);
```

Parse error pages instead of throwing (soft 404 pages often still have a title):

```php
$scraper = new Scraper(['http_errors' => false, 'timeout' => 10]);
```

Send everything through an egress proxy, except internal hosts:

```php
$scraper = new Scraper([
    'proxy' => ['http' => 'http://proxy.internal:3128', 'https' => 'http://proxy.internal:3128', 'no' => ['.internal']],
    'timeout' => 10,
]);
```

Your own client with the scraper's defaults kept, plus a request option:

```php
$client = new GuzzleHttp\Client([
    'connect_timeout' => 5,
    'headers' => ['User-Agent' => 'Tomaj\Scraper'],
    'cookies' => true,
]);
$scraper = new Scraper(['timeout' => 10], $client);
```

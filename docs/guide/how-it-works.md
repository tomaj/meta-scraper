---
description: What happens between an HTML page and the Meta object - the Scraper, the parsers and Meta::merge(), step by step.
---

# How it works

<Diagram name="how-it-works" alt="parseUrl downloads the page with Guzzle, parse takes HTML you already have; the parsers run in order, each returns a new Meta, merge keeps the first value found, and the result is one Meta object" />

Three kinds of objects take part:

| Object | Role |
| --- | --- |
| `Scraper` | Downloads the page (optional) and runs the parsers. Holds one Guzzle client and the last downloaded body. |
| Parsers (`ParserInterface`) | Read one kind of metadata from an HTML string and return a new `Meta`. Stateless: one instance can parse any number of pages. |
| `Meta` | The result: one nullable field per value, lists of `Author`, `Section` and `Tag`. |

## Step by step

### 1. Getting the HTML

You either have the HTML already and call `parse($html, $parsers)`, or you call `parseUrl($url, $parsers)`:

1. `parseUrl()` sends `GET $url` with the Guzzle client the scraper created in its constructor (or the one you gave it) and the request options.
2. Any Guzzle exception (connection error, timeout, TLS error, too many redirects, a 4xx or 5xx response with the default `http_errors`) becomes a `FetchException` with the Guzzle exception as `getPrevious()`. Nothing is parsed and `getBody()` keeps the body of the previous successful call.
3. Otherwise the body is stored (`getBody()` returns it) and passed to `parse()`, exactly as the server sent it: no charset conversion, no decompression beyond what Guzzle does.

### 2. Running the parsers

`parse()` creates an empty `Meta` and, for each parser in the order of the array, calls `$parser->parse($html)` and merges the returned `Meta` into the result. The parsers never see each other's results.

An empty parser list is allowed: you get an empty `Meta` (`parseUrl($url, [])` is a way to only download a page, then read `getBody()`).

### 3. Merging

`Meta::merge()` copies a value only into a field that is still empty, so **the first parser that finds a value wins**:

- a text field counts as empty when it is `null`, `''` or `'0'`;
- authors, sections and tags are copied as a whole list, from the first parser whose list is not empty; lists are never mixed;
- the published and modified times are copied when they are `null`.

<Diagram name="merge" alt="SchemaParser runs first and sets the title and the authors; OgDomParser runs second: its title and authors are ignored because those fields are already set, its image and site name fill fields that were still empty" />

[Combining parsers](./combining.md) shows what this means for real pages.

## What the parsers do not do

- **Run JavaScript.** Metadata that a page adds in the browser is not in the HTML the server returns.
- **Resolve relative URLs.** An `og:image` of `/img/a.jpg` is returned as `/img/a.jpg`; resolve it against the page URL yourself (the [link preview example](../examples/link-preview.md) does).
- **Validate values.** URLs, types and dates are returned as the page wrote them (dates keep the page's offset and must be absolute, see [The Meta object](./meta.md#dates)).
- **Throw on bad input.** Malformed HTML or JSON gives the values that could be read; see [Error handling](./errors.md).

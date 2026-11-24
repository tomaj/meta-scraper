---
description: Unit-test code that calls meta-scraper's parseUrl() without network, with a Guzzle MockHandler and the history middleware.
---

# Example: testing with a mock

An `ArticleImporter` service takes a `Scraper` in its constructor and imports the title and image of an article URL, returning `null` when the page cannot be downloaded. The example tests it without any web server: the `Scraper` gets a Guzzle client whose handler is a `MockHandler` with three canned responses, and the `history` middleware records the requests.

<ProjectSource name="testing" :files="['run.php', 'e2e.sh', 'expected-output.txt']" />

## The code

<<< @/../examples/testing/run.php

## Output

<<< @/../examples/testing/expected-output.txt{text}

## What to notice

- The service receives the `Scraper`, so a test can pass one with a mock client. The same setup works in a PHPUnit test case; see [Testing your code](../guide/testing.md).
- Responses are returned in order: a page, a 404 (which `parseUrl()` turns into a `FetchException`) and a `ConnectException` (also a `FetchException`).
- With your own client, the first constructor argument becomes request options: the history shows the `User-Agent` and the `timeout` from it.
- `getBody()` keeps the body of the last successful download, even after the failed calls.

## What CI checks

All eleven checks print `ok`; a failing check prints `FAIL` and exits with status 1, which fails the workflow.

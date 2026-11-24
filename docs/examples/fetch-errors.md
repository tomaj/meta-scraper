---
description: Every way meta-scraper's parseUrl() can fail - 404, 500, timeout, redirect loop, refused connection - caught as FetchException, plus http_errors and allow_redirects.
---

# Example: fetch errors

A fixture server that answers every path differently: a page, a redirect, a 404, a 500, a page that takes three seconds, a redirect loop and a page that echoes the `User-Agent`. `run.php` requests each one with a 1 second timeout and at most 3 redirects and shows how the failure arrives.

<ProjectSource name="fetch-errors" :files="['run.php', 'site/router.php', 'e2e.sh', 'expected-output.txt']" />

## The fixture server

<<< @/../examples/fetch-errors/site/router.php

## The code

<<< @/../examples/fetch-errors/run.php

## Output

<<< @/../examples/fetch-errors/expected-output.txt{text}

## What to notice

- **One exception type.** HTTP errors, the timeout, the redirect loop and the refused connection are all `FetchException`. `$e->url` is the URL you asked for; `getPrevious()` is the Guzzle exception with the details.
- **Guzzle 7 and 8.** The Guzzle exception classes differ between the versions (a timeout is a `ConnectException` in Guzzle 7 and a `NetworkTimeoutException` in Guzzle 8). The `reason()` function checks `BadResponseException` and `TooManyRedirectsException`, which both have; the example gives the same output with both versions.
- **Your headers are sent.** `/echo-user-agent` shows the `User-Agent` from the options.
- **`http_errors: false`** turns the 404 into a normal result: the error page is parsed and its title returned.
- **`allow_redirects: false`** returns the `301` response itself: an empty body, so nothing is found.
- **`ScraperException`** catches every exception of the package.

## What CI checks

Each line of the output: the redirect followed, the custom `User-Agent`, the status of the 404 and 500, the timeout after 1 second, the redirect limit, the refused connection and the exception message format.

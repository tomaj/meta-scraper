<?php

declare(strict_types=1);

namespace Tomaj\Scraper\Exception;

/**
 * Downloading a page failed: connection error, timeout, TLS error, too many redirects or a 4xx/5xx response.
 * The original Guzzle exception is available through getPrevious().
 */
final class FetchException extends \RuntimeException implements ScraperException
{
    public function __construct(
        public readonly string $url,
        \Throwable $previous,
    ) {
        parent::__construct(sprintf('Cannot fetch "%s": %s', $url, $previous->getMessage()), 0, $previous);
    }
}

<?php

declare(strict_types=1);

namespace Tomaj\Scraper\Exception;

/**
 * A parser needs a PHP extension that is not loaded.
 */
final class MissingExtensionException extends \LogicException implements ScraperException
{
    public function __construct(string $parser, string $extension)
    {
        parent::__construct(sprintf('%s requires the PHP extension "%s"', $parser, $extension));
    }
}

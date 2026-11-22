<?php

declare(strict_types=1);

namespace Tomaj\Scraper\Test;

use PHPUnit\Framework\TestCase;
use Tomaj\Scraper\Exception\MissingExtensionException;
use Tomaj\Scraper\Parser\OgDomParser;

final class ExceptionTest extends TestCase
{
    public function testMissingExtensionException(): void
    {
        $e = new MissingExtensionException(OgDomParser::class, 'dom');

        self::assertSame('Tomaj\Scraper\Parser\OgDomParser requires the PHP extension "dom"', $e->getMessage());
    }
}

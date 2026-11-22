<?php

declare(strict_types=1);

namespace Tomaj\Scraper\Test;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * The code on the documentation site is imported from docs/snippets. Snippets with a .out file are run
 * and must print exactly that; the others download pages and are only parsed (PHPStan analyses all of them).
 */
final class DocsSnippetsTest extends TestCase
{
    /**
     * @return iterable<string, array{string}>
     */
    public static function snippets(): iterable
    {
        $files = glob(__DIR__ . '/../docs/snippets/*.php');
        self::assertNotFalse($files);
        foreach ($files as $file) {
            yield basename($file) => [$file];
        }
    }

    #[DataProvider('snippets')]
    public function testSnippet(string $file): void
    {
        $code = file_get_contents($file);
        self::assertIsString($code);
        // TOKEN_PARSE throws ParseError on a syntax error
        self::assertNotSame([], token_get_all($code, TOKEN_PARSE));

        $expected = $file . '.out';
        if (!is_file($expected)) {
            self::assertStringContainsString('parseUrl(', $code, 'A snippet that does not download a page needs a .out file with its output');
            return;
        }

        $this->expectOutputString((string) file_get_contents($expected));
        (static function (string $file): void {
            require $file;
        })($file);
    }
}

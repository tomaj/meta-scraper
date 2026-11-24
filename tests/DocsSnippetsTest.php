<?php

declare(strict_types=1);

namespace Tomaj\Scraper\Test;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tomaj\Scraper\Author;
use Tomaj\Scraper\Exception\FetchException;
use Tomaj\Scraper\Exception\MissingExtensionException;
use Tomaj\Scraper\Exception\ScraperException;
use Tomaj\Scraper\Meta;
use Tomaj\Scraper\Parser\OgDomParser;
use Tomaj\Scraper\Parser\OgParser;
use Tomaj\Scraper\Parser\ParserInterface;
use Tomaj\Scraper\Parser\SchemaParser;
use Tomaj\Scraper\Scraper;
use Tomaj\Scraper\Section;
use Tomaj\Scraper\Tag;

/**
 * Checks the PHP code of the documentation against the current code.
 *
 * docs/snippets/*.php are included on the site with `<<<`. A snippet with a .out file is run and must
 * print exactly that; the others download pages and are only parsed (PHPStan analyses all of them).
 *
 * Every ```php block of README.md, UPGRADE-5.0.md and docs/ (inline snippets) runs in its own PHP
 * process and must exit with status 0 without writing to stderr:
 *
 * - The process has the Composer autoloader and these variables: `$html` (tests/fixtures/docs/article.html),
 *   `$url` (the same page served by a local `php -S`), `$scraper` (a Scraper) and `$parsers`
 *   (SchemaParser and OgDomParser). The classes of the package are imported unless the block imports them.
 * - A block directly after `<!-- snippet: skip -->` is only parsed: code that shows an older version
 *   or cannot run on its own. A block of method signatures (ending with `;`) is parsed inside an interface.
 * - A ```text block directly after `<!-- snippet: output -->` is the output of the PHP block before it.
 *
 * The runnable examples in examples/ are tested by the examples workflow, not here.
 */
final class DocsSnippetsTest extends TestCase
{
    private const string SKIP = '<!-- snippet: skip -->';
    private const string OUTPUT = '<!-- snippet: output -->';

    private const array IMPORTS = [
        'Scraper' => Scraper::class,
        'Meta' => Meta::class,
        'Author' => Author::class,
        'Section' => Section::class,
        'Tag' => Tag::class,
        'OgParser' => OgParser::class,
        'OgDomParser' => OgDomParser::class,
        'SchemaParser' => SchemaParser::class,
        'ParserInterface' => ParserInterface::class,
        'FetchException' => FetchException::class,
        'MissingExtensionException' => MissingExtensionException::class,
        'ScraperException' => ScraperException::class,
    ];

    /** @var resource|null */
    private static $server;

    private static string $url = '';

    #[\Override]
    public static function setUpBeforeClass(): void
    {
        $socket = stream_socket_server('tcp://127.0.0.1:0');
        self::assertIsResource($socket);
        $address = (string) stream_socket_get_name($socket, false);
        fclose($socket);
        $port = (int) substr($address, (int) strrpos($address, ':') + 1);

        $server = proc_open(
            [PHP_BINARY, '-S', $address, '-t', __DIR__ . '/fixtures/docs'],
            [0 => ['file', '/dev/null', 'r'], 1 => ['file', '/dev/null', 'w'], 2 => ['file', '/dev/null', 'w']],
            $pipes,
        );
        self::assertIsResource($server);
        self::$server = $server;
        self::$url = 'http://' . $address . '/article.html';

        for ($i = 0; $i < 50; $i++) {
            $connection = @fsockopen('127.0.0.1', $port);
            if ($connection !== false) {
                fclose($connection);
                return;
            }
            usleep(100_000);
        }
        self::fail('The PHP built-in server for the snippets did not start');
    }

    #[\Override]
    public static function tearDownAfterClass(): void
    {
        if (self::$server !== null) {
            proc_terminate(self::$server);
            proc_close(self::$server);
            self::$server = null;
        }
    }

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

    /**
     * @return iterable<string, array{string, ?string, bool}>
     */
    public static function markdownBlocks(): iterable
    {
        $root = dirname(__DIR__);
        $files = [$root . '/README.md', $root . '/UPGRADE-5.0.md'];
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root . '/docs', \FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $file) {
            \assert($file instanceof \SplFileInfo);
            $path = $file->getPathname();
            if ($file->getExtension() === 'md' && !str_contains($path, '/node_modules/') && !str_contains($path, '/.vitepress/')) {
                $files[] = $path;
            }
        }
        sort($files);

        foreach ($files as $path) {
            $relative = substr($path, strlen($root) + 1);
            foreach (self::blocks((string) file_get_contents($path)) as $line => $block) {
                yield $relative . ':' . $line => $block;
            }
        }
    }

    public function testDocumentationHasInlineSnippets(): void
    {
        $run = 0;
        $outputs = 0;
        foreach (self::markdownBlocks() as [, $output, $skip]) {
            $run += $skip ? 0 : 1;
            $outputs += $output === null ? 0 : 1;
        }

        self::assertGreaterThan(15, $run);
        self::assertGreaterThan(3, $outputs);
    }

    #[DataProvider('markdownBlocks')]
    public function testMarkdownSnippet(string $code, ?string $expectedOutput, bool $skip): void
    {
        if ($skip) {
            // skipped blocks must still be valid PHP; method signatures are checked inside an interface
            try {
                $tokens = token_get_all("<?php\n" . $code, TOKEN_PARSE);
            } catch (\ParseError) {
                $tokens = token_get_all("<?php\ninterface Signatures\n{\n" . $code . "}\n", TOKEN_PARSE);
            }
            self::assertNotSame([], $tokens);
            return;
        }
        self::assertNotSame([], token_get_all("<?php\n" . $code, TOKEN_PARSE));

        $script = tempnam(sys_get_temp_dir(), 'snippet');
        $prepend = tempnam(sys_get_temp_dir(), 'prepend');
        self::assertIsString($script);
        self::assertIsString($prepend);

        try {
            file_put_contents($script, "<?php\n\ndeclare(strict_types=1);\n\n" . $this->imports($code) . $code);
            file_put_contents($prepend, '<?php require ' . var_export(__DIR__ . '/../vendor/autoload.php', true) . ';'
                . ' $html = file_get_contents(' . var_export(__DIR__ . '/fixtures/docs/article.html', true) . ');'
                . ' $url = ' . var_export(self::$url, true) . ';'
                . ' $scraper = new Tomaj\Scraper\Scraper(["timeout" => 5]);'
                . ' $parsers = [new Tomaj\Scraper\Parser\SchemaParser(), new Tomaj\Scraper\Parser\OgDomParser()];');

            $process = proc_open(
                [PHP_BINARY, '-d', 'auto_prepend_file=' . $prepend, '-d', 'display_errors=stderr', '-d', 'error_reporting=-1', $script],
                [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
                $pipes,
            );
            self::assertIsResource($process);
            $stdout = (string) stream_get_contents($pipes[1]);
            $stderr = (string) stream_get_contents($pipes[2]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            $status = proc_close($process);
        } finally {
            unlink($script);
            unlink($prepend);
        }

        self::assertSame('', $stderr, "The snippet wrote to stderr:\n" . $code);
        self::assertSame(0, $status, "The snippet failed:\n" . $code . "\n" . $stdout);
        if ($expectedOutput !== null) {
            self::assertSame(rtrim($expectedOutput), rtrim($stdout), "The documented output differs:\n" . $code);
        }
    }

    /**
     * `use` statements for the classes of the package the block does not import itself.
     */
    private function imports(string $code): string
    {
        $imports = '';
        foreach (self::IMPORTS as $short => $class) {
            if (preg_match('/^use [^;]*\b' . $short . ';/m', $code) !== 1 && preg_match('/^(final |abstract |readonly )*(class|interface) ' . $short . '\b/m', $code) !== 1) {
                $imports .= 'use ' . $class . ";\n";
            }
        }

        return $imports;
    }

    /**
     * @return array<int, array{string, ?string, bool}> line number => [php code, expected output, skip]
     */
    private static function blocks(string $markdown): array
    {
        $lines = explode("\n", $markdown);
        $code = [];
        $skip = [];
        $outputs = [];
        $lastPhp = null;
        $previous = '';
        $count = count($lines);
        for ($i = 0; $i < $count; $i++) {
            if (preg_match('/^```(\w+)/', $lines[$i], $match) !== 1) {
                if (trim($lines[$i]) !== '') {
                    $previous = trim($lines[$i]);
                }
                continue;
            }
            $start = $i + 1;
            $body = [];
            for ($i++; $i < $count && !str_starts_with($lines[$i], '```'); $i++) {
                $body[] = $lines[$i];
            }
            $text = implode("\n", $body) . "\n";
            if ($match[1] === 'php') {
                $code[$start] = (string) preg_replace('/^<\?php\s*(declare\(strict_types=1\);\s*)?/', '', $text);
                $skip[$start] = $previous === self::SKIP;
                $lastPhp = $start;
            } elseif ($match[1] === 'text' && $previous === self::OUTPUT) {
                self::assertNotNull($lastPhp, 'An output block must follow a PHP block (line ' . $start . ').');
                $outputs[$lastPhp] = $text;
            }
            $previous = '```';
        }

        $blocks = [];
        foreach ($code as $line => $php) {
            $blocks[$line] = [$php, $outputs[$line] ?? null, $skip[$line]];
        }

        return $blocks;
    }
}

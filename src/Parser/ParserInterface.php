<?php
declare(strict_types=1);

namespace Tomaj\Scraper\Parser;

use Tomaj\Scraper\Meta;

interface ParserInterface
{
    public function parse(string $content): Meta;
}

<?php

declare(strict_types=1);

// Link previews for URLs that users paste into a chat or a comment.

require __DIR__ . '/vendor/autoload.php';

use App\BlockedUrlException;
use App\LinkPreview;
use App\UrlGuard;
use Tomaj\Scraper\Exception\FetchException;

$base = $argv[1] ?? 'http://127.0.0.1:8406';

// The fixture server runs on 127.0.0.1, which the guard blocks like any private address.
// Trust exactly that host and port here; a production guard has no trusted hosts.
$previews = new LinkPreview(new UrlGuard(trustedHosts: [parse_url($base, PHP_URL_HOST) . ':' . parse_url($base, PHP_URL_PORT)]));

$urls = [
    $base . '/article.html',
    $base . '/plain.html',
    $base . '/hostile.html',
    $base . '/to-metadata',
    $base . '/gone.html',
    'http://169.254.169.254/latest/meta-data/',
    'http://localhost:6379/',
    'http://10.0.0.5/admin',
    'http://[::1]:8080/',
    'file:///etc/passwd',
    'gopher://127.0.0.1:25/',
];

foreach ($urls as $url) {
    echo str_replace($base, '{fixture}', $url), "\n";
    try {
        $card = $previews->fetch($url);
        echo '  ', LinkPreview::render($card), "\n";
    } catch (BlockedUrlException $e) {
        echo '  blocked: ', $e->getMessage(), "\n";
    } catch (FetchException $e) {
        echo '  no preview: the page cannot be downloaded', "\n";
    }
}

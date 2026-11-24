<?php

declare(strict_types=1);

namespace App;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\UriInterface;
use Tomaj\Scraper\Exception\FetchException;
use Tomaj\Scraper\Parser\OgDomParser;
use Tomaj\Scraper\Parser\SchemaParser;
use Tomaj\Scraper\Scraper;

/**
 * Builds a link preview card (title, description, image, site name) for a URL a user pasted.
 */
final readonly class LinkPreview
{
    public function __construct(private UrlGuard $guard) {}

    /**
     * @return array{url: string, title: string, description: ?string, image: ?string, site: string}
     * @throws BlockedUrlException when the URL or a redirect points somewhere the server must not go
     * @throws FetchException when the page cannot be downloaded
     */
    public function fetch(string $url): array
    {
        $ip = $this->guard->check($url);
        $host = (string) parse_url($url, PHP_URL_HOST);
        $port = parse_url($url, PHP_URL_PORT) ?? (parse_url($url, PHP_URL_SCHEME) === 'https' ? 443 : 80);

        $client = new Client([
            RequestOptions::CONNECT_TIMEOUT => 2,
            RequestOptions::TIMEOUT => 5,
            RequestOptions::HEADERS => ['User-Agent' => 'AcmePreview/1.0 (+https://acme.example/bot)'],
            // follow at most 2 redirects, and check every target before it is requested
            RequestOptions::ALLOW_REDIRECTS => [
                'max' => 2,
                'protocols' => ['http', 'https'],
                'on_redirect' => fn($request, $response, UriInterface $target) => $this->guard->check((string) $target),
            ],
            // connect to the address the guard checked, not to what DNS says a moment later
            'curl' => [CURLOPT_RESOLVE => ["{$host}:{$port}:{$ip}"]],
        ]);

        $meta = new Scraper([], $client)->parseUrl($url, [new OgDomParser(), new SchemaParser()]);

        $description = $meta->getOgDescription() ?? $meta->getDescription();
        if ($description !== null && mb_strlen($description) > 100) {
            $description = rtrim(mb_substr($description, 0, 99)) . '…';
        }

        return [
            'url' => $url,
            'title' => $meta->getOgTitle() ?? $meta->getTitle() ?? $host,
            'description' => $description,
            'image' => $this->imageUrl($meta->getOgImage(), $url),
            'site' => $meta->getOgSiteName() ?? $host,
        ];
    }

    /**
     * og:image may be relative; anything but an http(s) URL (javascript:, data:, file:) is dropped.
     */
    private function imageUrl(?string $image, string $pageUrl): ?string
    {
        if ($image === null) {
            return null;
        }
        if (str_starts_with($image, '/') && !str_starts_with($image, '//')) {
            $page = parse_url($pageUrl);
            $image = $page['scheme'] . '://' . $page['host'] . (isset($page['port']) ? ':' . $page['port'] : '') . $image;
        }
        $scheme = strtolower((string) parse_url($image, PHP_URL_SCHEME));

        return in_array($scheme, ['http', 'https'], true) ? $image : null;
    }

    /**
     * Every value comes from the page author: escape it.
     *
     * @param array{url: string, title: string, description: ?string, image: ?string, site: string} $card
     */
    public static function render(array $card): string
    {
        $e = static fn(string $value): string => htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $html = '<a class="card" href="' . $e($card['url']) . '">';
        if ($card['image'] !== null) {
            $html .= '<img src="' . $e($card['image']) . '" alt="">';
        }
        $html .= '<strong>' . $e($card['title']) . '</strong>';
        if ($card['description'] !== null) {
            $html .= '<p>' . $e($card['description']) . '</p>';
        }

        return $html . '<small>' . $e($card['site']) . '</small></a>';
    }
}

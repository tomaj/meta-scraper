<?php

declare(strict_types=1);

namespace App;

/**
 * Decides whether the server may request a URL that came from a user (SSRF protection).
 *
 * Allows only http and https, resolves the host and rejects loopback, private, link-local and
 * reserved addresses. check() returns the address it checked, so the request can be pinned to it
 * (CURLOPT_RESOLVE) and a DNS change between the check and the request cannot bypass it.
 */
final readonly class UrlGuard
{
    /**
     * @param list<string> $trustedHosts "host:port" pairs allowed even on a private address. Empty in production;
     *                                   this example needs one for its fixture server on 127.0.0.1.
     */
    public function __construct(private array $trustedHosts = []) {}

    /**
     * @return string the IP address the host resolves to
     * @throws BlockedUrlException
     */
    public function check(string $url): string
    {
        $parts = parse_url($url);
        $scheme = strtolower($parts['scheme'] ?? '');
        if (!in_array($scheme, ['http', 'https'], true)) {
            throw new BlockedUrlException("scheme \"{$scheme}\" is not allowed");
        }
        $host = trim($parts['host'] ?? '', '[]');
        if ($host === '') {
            throw new BlockedUrlException('the URL has no host');
        }
        $port = $parts['port'] ?? ($scheme === 'https' ? 443 : 80);

        $ip = filter_var($host, FILTER_VALIDATE_IP) !== false ? $host : gethostbyname($host);
        if (filter_var($ip, FILTER_VALIDATE_IP) === false) {
            throw new BlockedUrlException("cannot resolve {$host}");
        }
        if (in_array("{$host}:{$port}", $this->trustedHosts, true)) {
            return $ip;
        }
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            throw new BlockedUrlException("{$host} is a private or reserved address ({$ip})");
        }

        return $ip;
    }
}

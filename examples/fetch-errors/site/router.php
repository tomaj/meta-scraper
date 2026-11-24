<?php

declare(strict_types=1);

// A small fake website for the examples: every path answers in a different way.
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

switch ($path) {
    case '/ok':
        echo '<title>It works</title><meta property="og:site_name" content="Fixture site">';
        break;
    case '/missing':
        http_response_code(404);
        echo '<title>Page not found | Fixture site</title><meta name="robots" content="noindex">';
        break;
    case '/error':
        http_response_code(500);
        echo '<title>Internal Server Error</title>';
        break;
    case '/slow':
        // answers after 3 seconds, longer than the timeout of the client
        sleep(3);
        echo '<title>Too late</title>';
        break;
    case '/moved':
        header('Location: /ok', true, 301);
        break;
    case '/loop':
        // redirects to itself forever
        header('Location: /loop', true, 302);
        break;
    case '/echo-user-agent':
        echo '<title>' . htmlspecialchars($_SERVER['HTTP_USER_AGENT'] ?? '') . '</title>';
        break;
    default:
        http_response_code(404);
}

return true;

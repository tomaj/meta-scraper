<?php

declare(strict_types=1);

// The built-in server sends .html files as UTF-8; this one page announces its real charset.
if (parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) === '/windows-1250.html') {
    header('Content-Type: text/html; charset=windows-1250');
    readfile(__DIR__ . '/windows-1250.html');
    return true;
}

return false;

<?php

declare(strict_types=1);

// A redirect to the cloud metadata address: a classic SSRF trick that a URL check alone misses.
if (parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) === '/to-metadata') {
    header('Location: http://169.254.169.254/latest/meta-data/', true, 302);
    return true;
}

return false;

<?php

header('Content-Type: text/plain; charset=utf-8');

echo "OK\n";
echo 'PHP: ' . PHP_VERSION . "\n";

$prepend = ini_get('auto_prepend_file');
echo 'auto_prepend_file: ' . ($prepend !== '' ? $prepend : '(none)') . "\n";

if ($prepend !== '' && !is_file($prepend)) {
    echo "ERROR: auto_prepend_file points to missing file: {$prepend}\n";
    echo "Remove auto_prepend_file from .htaccess / .user.ini on the server.\n";
    exit(1);
}

$autoload = __DIR__ . '/../vendor/autoload.php';
echo 'vendor/autoload.php: ' . (is_file($autoload) ? 'present' : 'MISSING') . "\n";

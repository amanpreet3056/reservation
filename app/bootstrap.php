<?php
declare(strict_types=1);

session_start();

$appConfig = require __DIR__ . '/../config/app.php';
$GLOBALS['app_config'] = $appConfig;

spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/';
    $len = strlen($prefix);

    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

require __DIR__ . '/Support/helpers.php';
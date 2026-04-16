<?php

declare(strict_types=1);

// Chargement des variables d'environnement
$envFile = dirname(__DIR__) . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#')) continue;
        if (!str_contains($line, '=')) continue;
        [$key, $value] = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value);
    }
}

// Autoloader simple PSR-4
spl_autoload_register(function (string $class): void {
    $prefix = 'Trail\\Src\\';
    $baseDir = __DIR__ . '/';

    if (!str_starts_with($class, $prefix)) return;

    $relativeClass = substr($class, strlen($prefix));
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

// Gestion des erreurs
if (($_ENV['APP_ENV'] ?? 'prod') === 'dev') {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

// Démarrer la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

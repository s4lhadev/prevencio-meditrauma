<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

// Load cached env vars if the .env.local.php file exists
// Run "composer dump-env prod" to create it (requires symfony/flex >=1.2)
if (is_array($env = @include dirname(__DIR__).'/.env.local.php') && ($_SERVER['APP_ENV'] ?? $_ENV['APP_ENV'] ?? $env['APP_ENV'] ?? null) === ($env['APP_ENV'] ?? null)) {
    foreach ($env as $k => $v) {
        $_ENV[$k] = $_ENV[$k] ?? (isset($_SERVER[$k]) && 0 !== strpos($k, 'HTTP_') ? $_SERVER[$k] : $v);
    }
    // El deploy sincroniza estos valores en .env (admin_agent → Symfony, APP_CACHE_DIR por run).
    // Sin esta sobreescritura, .env.local.php / $_SERVER / process env mantienen valores viejos
    // y provocan agent_unauthorized aunque .env esté correcto.
    $envFile = dirname(__DIR__).'/.env';
    if (is_readable($envFile)) {
        $dynamicKeys = ['ADMIN_AGENT_INTERNAL_URL', 'ADMIN_AGENT_SECRET', 'ADMIN_AGENT_PAGE_KEY', 'ADMIN_AGENT_DEV_KEY', 'APP_CACHE_DIR'];
        $keysSet = array_flip($dynamicKeys);
        foreach (file($envFile, \FILE_IGNORE_NEW_LINES | \FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
            $trimmed = ltrim($line);
            if ('' === $trimmed || '#' === $trimmed[0]) {
                continue;
            }
            if (!preg_match('/^(?:export\\s+)?([A-Z_][A-Z0-9_]*)=(.*)$/', trim($line), $m)) {
                continue;
            }
            $key = $m[1];
            if (!isset($keysSet[$key])) {
                continue;
            }
            $val = trim($m[2]);
            if ('' !== $val && ('"' === $val[0] || "'" === $val[0])) {
                $val = trim($val, $val[0]);
            }
            $_ENV[$key] = $val;
            $_SERVER[$key] = $val;
            putenv($key.'='.$val);
        }
    }
} elseif (!class_exists(Dotenv::class)) {
    throw new RuntimeException('Please run "composer require symfony/dotenv" to load the ".env" files configuring the application.');
} else {
    // load all the .env files
    (new Dotenv(false))->loadEnv(dirname(__DIR__).'/.env');
}

$_SERVER += $_ENV;
$_SERVER['APP_ENV'] = $_ENV['APP_ENV'] = ($_SERVER['APP_ENV'] ?? $_ENV['APP_ENV'] ?? null) ?: 'dev';
$_SERVER['APP_DEBUG'] = $_SERVER['APP_DEBUG'] ?? $_ENV['APP_DEBUG'] ?? 'prod' !== $_SERVER['APP_ENV'];
$_SERVER['APP_DEBUG'] = $_ENV['APP_DEBUG'] = (int) $_SERVER['APP_DEBUG'] || filter_var($_SERVER['APP_DEBUG'], FILTER_VALIDATE_BOOLEAN) ? '1' : '0';

<?php

(function () {
    $envFile = dirname(__DIR__) . '/.env';
    if (file_exists($envFile) && is_readable($envFile)) {
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            if (strpos($line, '=') !== false) {
                list($key, $val) = explode('=', $line, 2);
                $key = trim($key);
                $val = trim($val);
                if (preg_match('/^"(.*)"$/', $val, $m) || preg_match("/^'(.*)'$/", $val, $m)) {
                    $val = $m[1];
                }
                if (getenv($key) === false) {
                    putenv("{$key}={$val}");
                    $_ENV[$key] = $val;
                    $_SERVER[$key] = $val;
                }
            }
        }
    }
})();

function app_env(string $key, $default = null)
{
    $value = getenv($key);
    return $value === false ? $default : $value;
}

function app_env_bool(string $key, bool $default = false): bool
{
    $value = getenv($key);
    if ($value === false) {
        return $default;
    }

    $normalized = strtolower(trim((string)$value));
    return in_array($normalized, ['1', 'true', 'yes', 'on'], true);
}

function app_is_production(): bool
{
    return strtolower((string)app_env('APP_ENV', 'development')) === 'production';
}

function app_configure_errors(): void
{
    if (app_is_production()) {
        ini_set('display_errors', '0');
    } else {
        ini_set('display_errors', '1');
    }

    error_reporting(E_ALL);
}

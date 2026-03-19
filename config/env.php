<?php

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

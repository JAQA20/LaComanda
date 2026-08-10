<?php
require_once __DIR__ . '/env.php';

return [
    'enabled' => app_env_bool('MAIL_ENABLED', app_env_bool('MAILTRAP_ENABLED', true)),
    'transport' => app_env('MAIL_MAILER', app_env('MAILTRAP_TRANSPORT', 'smtp')),

    'smtp_host' => app_env('MAIL_HOST', app_env('MAILTRAP_SMTP_HOST', 'smtp.gmail.com')),
    'smtp_port' => (int)app_env('MAIL_PORT', app_env('MAILTRAP_SMTP_PORT', 587)),
    'smtp_username' => app_env('MAIL_USERNAME', app_env('MAILTRAP_SMTP_USERNAME', '')),
    'smtp_password' => app_env('MAIL_PASSWORD', app_env('MAILTRAP_SMTP_PASSWORD', '')),
    'smtp_encryption' => app_env('MAIL_ENCRYPTION', app_env('MAILTRAP_SMTP_ENCRYPTION', 'tls')),

    'token' => app_env('MAILTRAP_TOKEN', ''),
    'inbox_id' => app_env('MAILTRAP_INBOX_ID', ''),
    'from_email' => app_env('MAIL_FROM_ADDRESS', app_env('MAILTRAP_FROM_EMAIL', '')),
    'from_name' => app_env('MAIL_FROM_NAME', app_env('MAILTRAP_FROM_NAME', 'La Comanda')),
    'endpoint_base' => app_env('MAILTRAP_ENDPOINT_BASE', 'https://sandbox.api.mailtrap.io/api/send/'),
];

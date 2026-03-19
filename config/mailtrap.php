<?php
require_once __DIR__ . '/env.php';

return [
    'enabled' => app_env_bool('MAILTRAP_ENABLED', false),
    'transport' => app_env('MAILTRAP_TRANSPORT', 'smtp'),

    'smtp_host' => app_env('MAILTRAP_SMTP_HOST', 'sandbox.smtp.mailtrap.io'),
    'smtp_port' => (int)app_env('MAILTRAP_SMTP_PORT', 587),
    'smtp_username' => app_env('MAILTRAP_SMTP_USERNAME', ''),
    'smtp_password' => app_env('MAILTRAP_SMTP_PASSWORD', ''),
    'smtp_encryption' => app_env('MAILTRAP_SMTP_ENCRYPTION', 'tls'),

    'token' => app_env('MAILTRAP_TOKEN', ''),
    'inbox_id' => app_env('MAILTRAP_INBOX_ID', ''),
    'from_email' => app_env('MAILTRAP_FROM_EMAIL', 'no-reply@lacomanda.local'),
    'from_name' => app_env('MAILTRAP_FROM_NAME', 'La Comanda'),
    'endpoint_base' => app_env('MAILTRAP_ENDPOINT_BASE', 'https://sandbox.api.mailtrap.io/api/send/'),
];

<?php
require_once __DIR__ . '/../config/env.php';
app_configure_errors();

class MailtrapMailer
{
    public static function send(string $toEmail, string $toName, string $subject, string $text, ?string $html = null): bool
    {
        $configPath = __DIR__ . '/../config/mailtrap.php';
        if (!file_exists($configPath)) {
            throw new Exception('Archivo de configuración Mailtrap no encontrado');
        }

        $config = require $configPath;
        $enabled = (bool)($config['enabled'] ?? false);
        if (!$enabled) {
            throw new Exception('Mailtrap está deshabilitado en configuración');
        }

        $transport = strtolower(trim((string)($config['transport'] ?? 'smtp')));

        if ($transport === 'smtp') {
            return self::sendViaSmtp($config, $toEmail, $toName, $subject, $text, $html);
        }

        return self::sendViaApi($config, $toEmail, $toName, $subject, $text, $html);
    }

    private static function sendViaApi(array $config, string $toEmail, string $toName, string $subject, string $text, ?string $html): bool
    {
        $token = trim((string)($config['token'] ?? ''));
        $inboxId = trim((string)($config['inbox_id'] ?? ''));
        $fromEmail = trim((string)($config['from_email'] ?? 'no-reply@lacomanda.com'));
        $fromName = trim((string)($config['from_name'] ?? 'La Comanda'));
        $endpointBase = rtrim((string)($config['endpoint_base'] ?? 'https://sandbox.api.mailtrap.io/api/send/'), '/') . '/';

        if ($token === '' || $inboxId === '') {
            throw new Exception('Mailtrap no configurado: falta token o inbox_id');
        }

        if (!function_exists('curl_init')) {
            throw new Exception('cURL no está disponible en PHP');
        }

        $endpoint = $endpointBase . rawurlencode($inboxId);
        $payload = [
            'from' => [
                'email' => $fromEmail,
                'name' => $fromName,
            ],
            'to' => [
                [
                    'email' => $toEmail,
                    'name' => $toName !== '' ? $toName : $toEmail,
                ]
            ],
            'subject' => $subject,
            'text' => $text,
            'html' => $html ?? nl2br(htmlspecialchars($text, ENT_QUOTES, 'UTF-8')),
            'category' => 'password-reset',
        ];

        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $token,
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
            CURLOPT_TIMEOUT => 15,
        ]);

        $response = curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            throw new Exception('Error de red al enviar correo: ' . $curlErr);
        }

        if ($httpCode < 200 || $httpCode >= 300) {
            throw new Exception('Mailtrap respondió HTTP ' . $httpCode . ': ' . $response);
        }

        return true;
    }

    private static function sendViaSmtp(array $config, string $toEmail, string $toName, string $subject, string $text, ?string $html): bool
    {
        if (!function_exists('curl_init')) {
            throw new Exception('cURL no está disponible en PHP');
        }

        $host = trim((string)($config['smtp_host'] ?? 'sandbox.smtp.mailtrap.io'));
        $port = (int)($config['smtp_port'] ?? 587);
        $username = trim((string)($config['smtp_username'] ?? ''));
        $password = trim((string)($config['smtp_password'] ?? ''));
        $encryption = strtolower(trim((string)($config['smtp_encryption'] ?? 'tls')));
        $fromEmail = trim((string)($config['from_email'] ?? ''));
        if ($fromEmail === '') {
            $fromEmail = $username !== '' ? $username : 'no-reply@lacomanda.com';
        }
        $fromName = trim((string)($config['from_name'] ?? 'La Comanda'));

        if ($host === '' || $port <= 0 || $username === '' || $password === '') {
            throw new Exception('SMTP no configurado: falta host, puerto, usuario o contraseña');
        }

        $toDisplayName = $toName !== '' ? $toName : $toEmail;
        $boundary = 'b1_' . bin2hex(random_bytes(8));
        $htmlBody = $html ?? nl2br(htmlspecialchars($text, ENT_QUOTES, 'UTF-8'));

        $message = "From: {$fromName} <{$fromEmail}>\r\n";
        $message .= "To: {$toDisplayName} <{$toEmail}>\r\n";
        $message .= "Subject: {$subject}\r\n";
        $message .= "MIME-Version: 1.0\r\n";
        $message .= "Content-Type: multipart/alternative; boundary=\"{$boundary}\"\r\n\r\n";
        $message .= "--{$boundary}\r\n";
        $message .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $message .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
        $message .= $text . "\r\n\r\n";
        $message .= "--{$boundary}\r\n";
        $message .= "Content-Type: text/html; charset=UTF-8\r\n";
        $message .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
        $message .= $htmlBody . "\r\n\r\n";
        $message .= "--{$boundary}--\r\n";

        $smtpUrl = "smtp://{$host}:{$port}";
        $encModes = [];
        if ($encryption === 'tls') {
            $encModes = [CURLUSESSL_ALL];
        } elseif ($encryption === 'ssl') {
            $smtpUrl = "smtps://{$host}:{$port}";
            $encModes = [CURLUSESSL_ALL];
        } elseif ($encryption === 'none') {
            $encModes = [CURLUSESSL_NONE];
        } else {
            $encModes = [CURLUSESSL_ALL, CURLUSESSL_NONE];
        }

        $lastError = '';
        foreach ($encModes as $sslMode) {
            $messageLines = preg_split('/\r\n|\n|\r/', $message);
            $index = 0;

            $readCallback = function ($curl, $fd, $length) use (&$messageLines, &$index) {
                if ($index >= count($messageLines)) {
                    return '';
                }
                $line = $messageLines[$index++] . "\r\n";
                return $line;
            };

            $ch = curl_init($smtpUrl);
            curl_setopt_array($ch, [
                CURLOPT_USERNAME => $username,
                CURLOPT_PASSWORD => $password,
                CURLOPT_USE_SSL => $sslMode,
                CURLOPT_MAIL_FROM => '<' . $fromEmail . '>',
                CURLOPT_MAIL_RCPT => ['<' . $toEmail . '>'],
                CURLOPT_UPLOAD => true,
                CURLOPT_READFUNCTION => $readCallback,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 20,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
            ]);

            $response = curl_exec($ch);
            $curlErr = curl_error($ch);
            $httpCode = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
            curl_close($ch);

            if ($response !== false && $httpCode >= 200 && $httpCode < 400) {
                return true;
            }

            $lastError = $curlErr !== '' ? $curlErr : ('SMTP code ' . $httpCode . ' response: ' . (string)$response);
        }

        throw new Exception('No se pudo enviar por SMTP Mailtrap: ' . $lastError);
    }
}

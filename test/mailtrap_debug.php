<?php
require_once __DIR__ . '/../model/MailtrapMailer.php';

header('Content-Type: text/plain; charset=utf-8');

$to = $argv[1] ?? 'admin@lacomanda.com';

try {
    $ok = MailtrapMailer::send(
        $to,
        'Debug User',
        'Debug Mailtrap SMTP',
        "Correo de prueba técnica para validar entrega en Mailtrap.",
        '<p>Correo de prueba técnica para validar entrega en <strong>Mailtrap</strong>.</p>'
    );
    echo $ok ? "SEND_OK\n" : "SEND_FALSE\n";
} catch (Throwable $e) {
    echo 'SEND_ERR: ' . $e->getMessage() . "\n";
}

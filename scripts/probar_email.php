<?php
require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../model/MailtrapMailer.php';

$destinatario = $argv[1] ?? null;

if (!$destinatario) {
    echo "\nUso: php scripts/probar_email.php correo_destino@ejemplo.com\n\n";
    exit(1);
}

echo "Intentando enviar correo de prueba a: {$destinatario}...\n";

try {
    $enviado = MailtrapMailer::send(
        $destinatario,
        'Usuario de Prueba',
        'Prueba de Correo - La Comanda',
        "¡Hola!\n\nEste es un correo de prueba enviado desde La Comanda utilizando Gmail SMTP.\nSi estás leyendo esto, la configuración de correo está funcionando correctamente.\n",
        "<h3>¡Hola!</h3><p>Este es un correo de prueba enviado desde <strong>La Comanda</strong> utilizando <strong>Gmail SMTP</strong>.</p><p style='color: green;'>✔ Si estás leyendo esto, la configuración de correo está funcionando correctamente.</p>"
    );

    if ($enviado) {
        echo "✅ ¡Correo enviado exitosamente!\n";
    } else {
        echo "❌ No se pudo enviar el correo.\n";
    }
} catch (Exception $e) {
    echo "❌ Error al enviar correo: " . $e->getMessage() . "\n";
}

// Ejecutaer el script desde la terminal:
// php scripts/probar_email.php "tu_correo@gmail.com"
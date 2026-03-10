<?php
header("Content-Type: application/json; charset=utf-8");
require_once __DIR__ . "/../middleware/auth.php";
require_once __DIR__ . "/../middleware/roles.php";
require_once __DIR__ . "/../model/MailtrapMailer.php";

verificarRol([1]);

try {
    $to = trim($_GET['to'] ?? ($_SESSION['email'] ?? ''));
    if ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Email inválido para prueba');
    }

    $ok = MailtrapMailer::send(
        $to,
        (string)($_SESSION['nombre'] ?? 'Admin'),
        'Prueba Mailtrap - La Comanda',
        "Este es un correo de prueba de Mailtrap para validar el módulo de recuperación.",
        "<p>Este es un correo de prueba de <strong>Mailtrap</strong> para validar el módulo de recuperación.</p>"
    );

    echo json_encode([
        'status' => $ok ? 'OK' : 'ERROR',
        'message' => $ok ? 'Correo de prueba enviado' : 'No se pudo enviar'
    ]);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'ERROR',
        'message' => $e->getMessage()
    ]);
}

<?php
header("Content-Type: application/json; charset=utf-8");

require_once __DIR__ . "/../middleware/auth.php";
require_once __DIR__ . "/../middleware/roles.php";
require_once __DIR__ . "/../model/Barista.php";
require_once __DIR__ . "/../config/rutas.php";

// Endpoint de acciones de barista.
// Ahora usa el modelo Barista.php para que este módulo quede realmente separado.

verificarRol([1, 4]);

$numero = isset($_POST['numero']) ? (int)$_POST['numero'] : 0;
$accion = trim((string)($_POST['accion'] ?? ''));

if ($numero <= 0 || $accion === '') {
    http_response_code(400);
    echo json_encode([
        'status' => 'ERROR',
        'message' => 'Datos inválidos'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    if ($accion === 'preparacion') {
        Barista::marcarEnPreparacion($numero);
    } elseif ($accion === 'lista') {
        Barista::marcarLista($numero);
    } else {
        throw new RuntimeException('Acción no soportada para barista.');
    }

    echo json_encode([
        'status' => 'OK'
    ], JSON_UNESCAPED_UNICODE);
    exit;
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'ERROR',
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

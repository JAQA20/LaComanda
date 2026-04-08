<?php
require_once __DIR__ . "/../middleware/auth.php";
require_once __DIR__ . "/../middleware/roles.php";
require_once __DIR__ . "/../model/OrdenesSync.php";
require_once __DIR__ . "/../config/rutas.php";

// ========JARVIS UPDATE========
// Cocina ahora usa un endpoint por acciones igual que barista:
// preparacion | lista. La entrega final sigue siendo responsabilidad del mesero.
//
// Además este controlador soporta dos modos:
// 1) Form normal desde la vista cocina -> redirige de vuelta a cocina.
// 2) AJAX/fetch -> responde JSON.

verificarRol([1, 3]);

$numero = isset($_POST['numero']) ? (int)$_POST['numero'] : 0;
$accion = trim((string)($_POST['accion'] ?? ''));
$isAjax = (
    (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower((string)$_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') ||
    (isset($_SERVER['HTTP_ACCEPT']) && str_contains((string)$_SERVER['HTTP_ACCEPT'], 'application/json'))
);

if ($numero <= 0 || $accion === '') {
    if ($isAjax) {
        header("Content-Type: application/json; charset=utf-8");
        http_response_code(400);
        echo json_encode([
            'status' => 'ERROR',
            'message' => 'Datos inválidos'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    header("Location: " . BASE_URL . "views/cocina.php");
    exit;
}

try {
    if ($accion === 'preparacion') {
        OrdenesSync::marcarOrdenCocinaEnPreparacion($numero);
    } elseif ($accion === 'lista') {
        OrdenesSync::marcarOrdenCocinaLista($numero);
    } else {
        throw new RuntimeException('Acción no soportada para cocina.');
    }

    if ($isAjax) {
        header("Content-Type: application/json; charset=utf-8");
        echo json_encode([
            'status' => 'OK'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    header("Location: " . BASE_URL . "views/cocina.php");
    exit;
} catch (Throwable $e) {
    if ($isAjax) {
        header("Content-Type: application/json; charset=utf-8");
        http_response_code(400);
        echo json_encode([
            'status' => 'ERROR',
            'message' => $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    error_log('cocinaAccionController ERROR: ' . $e->getMessage());
    header("Location: " . BASE_URL . "views/cocina.php");
    exit;
}

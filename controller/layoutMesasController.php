<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../model/LayoutConfig.php';
app_configure_errors();

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function responder_layout(int $code, array $payload): void
{
    http_response_code($code);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

function usuario_es_admin(): bool
{
    return isset($_SESSION['rol_id']) && (int)$_SESSION['rol_id'] === 1;
}

try {
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    if ($method === 'GET') {
        responder_layout(200, [
            'status' => 'OK',
            'data' => LayoutConfig::obtenerCroquisPrincipal(),
            'isAdmin' => usuario_es_admin(),
        ]);
    }

    if (!usuario_es_admin()) {
        responder_layout(403, [
            'status' => 'ERROR',
            'message' => 'Solo admin puede modificar el layout.',
        ]);
    }

    $usuarioId = isset($_SESSION['usuario_id']) ? (int)$_SESSION['usuario_id'] : null;

    if ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        $items = $input['items'] ?? null;

        if (!is_array($items)) {
            responder_layout(422, [
                'status' => 'ERROR',
                'message' => 'Payload inválido para layout.',
            ]);
        }

        LayoutConfig::guardarCroquisPrincipal(normalizar_layout($items), $usuarioId);
        responder_layout(200, [
            'status' => 'OK',
            'message' => 'Layout guardado correctamente.',
        ]);
    }

    if ($method === 'DELETE') {
        LayoutConfig::restablecerCroquisPrincipal($usuarioId);
        responder_layout(200, [
            'status' => 'OK',
            'message' => 'Layout restablecido correctamente.',
        ]);
    }

    responder_layout(405, [
        'status' => 'ERROR',
        'message' => 'Método no permitido.',
    ]);
} catch (Throwable $e) {
    responder_layout(500, [
        'status' => 'ERROR',
        'message' => $e->getMessage(),
    ]);
}

function normalizar_layout(array $items): array
{
    $normalizado = [];

    foreach ($items as $id => $pos) {
        if (!is_string($id) || $id === '' || !is_array($pos)) {
            continue;
        }

        if (!isset($pos['left'], $pos['top'])) {
            continue;
        }

        $left = max(-2, min(98, (float)$pos['left']));
        $top = max(-2, min(98, (float)$pos['top']));

        $normalizado[$id] = [
            'left' => $left,
            'top' => $top,
        ];
    }

    return $normalizado;
}

<?php
header("Content-Type: application/json; charset=utf-8");

require_once __DIR__ . "/../../middleware/auth.php";
require_once __DIR__ . "/../../middleware/roles.php";
require_once __DIR__ . "/../../model/SesionesActivas.php";

verificarRol([1]);

$sesionesActivas = SesionesActivas::listarActivas();
$salida = [];

foreach ($sesionesActivas as $sesion) {
    $estadoActividad = SesionesActivas::obtenerEstadoActividad($sesion['ultima_actividad'] ?? 0);

    $salida[] = [
        'usuario_id' => (int)($sesion['usuario_id'] ?? 0),
        'nombre' => (string)($sesion['nombre'] ?? 'Sin nombre'),
        'email' => (string)($sesion['email'] ?? 'Sin email'),
        'rol' => SesionesActivas::nombreRol($sesion['rol_id'] ?? 0),
        'login_at' => SesionesActivas::formatearHora($sesion['login_at'] ?? null),
        'ultima_actividad' => SesionesActivas::formatearHora($sesion['ultima_actividad'] ?? null),
        'dispositivo' => SesionesActivas::resumirUserAgent($sesion['user_agent'] ?? ''),
        'pagina_actual' => (string)($sesion['pagina_actual'] ?? 'N/A'),
        'ip' => (string)($sesion['ip'] ?? 'N/A'),
        'logout_at' => SesionesActivas::obtenerUltimoLogout($sesion['usuario_id'] ?? 0),
        'estado_label' => $estadoActividad['label'],
        'estado_class' => $estadoActividad['class'],
    ];
}

echo json_encode([
    'ok' => true,
    'total' => count($salida),
    'sesiones' => $salida,
], JSON_UNESCAPED_UNICODE);

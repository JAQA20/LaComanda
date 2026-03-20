<?php
header("Content-Type: application/json; charset=utf-8");

require_once __DIR__ . "/../../config/text.php";
require_once __DIR__ . "/../../middleware/auth.php";
require_once __DIR__ . "/../../middleware/roles.php";
require_once __DIR__ . "/../../model/Productos.php";
require_once __DIR__ . "/../../model/Usuarios.php";

verificarRol([1]);

$archivo = __DIR__ . "/../../controller/ordenes.json";
$ordenes = file_exists($archivo)
    ? json_decode(file_get_contents($archivo), true)
    : [];

if (!is_array($ordenes)) {
    $ordenes = [];
}

function normalizarEstadoOrden($estado, $entregada = null)
{
    $estado = app_normalize_text((string)($estado ?? ''));
    $estado = mb_strtolower(trim($estado), 'UTF-8');

    if ($entregada === true || strpos($estado, 'entreg') !== false) {
        return 'entregada';
    }

    if (strpos($estado, 'cancel') !== false) {
        return 'cancelada';
    }

    if (strpos($estado, 'lista') !== false) {
        return 'lista';
    }

    if (strpos($estado, 'proceso') !== false || strpos($estado, 'prepar') !== false) {
        return 'en_proceso';
    }

    return 'pendiente';
}

function formatearFechaOrden($orden)
{
    $candidatos = [
        $orden['hora_entrega'] ?? null,
        $orden['hora_lista'] ?? null,
        $orden['hora'] ?? null,
        $orden['fecha'] ?? null,
        $orden['fecha_hora'] ?? null,
    ];

    foreach ($candidatos as $valor) {
        if (!empty($valor)) {
            $timestamp = strtotime((string)$valor);
            if ($timestamp !== false) {
                return date('d/m/Y h:i A', $timestamp);
            }
        }
    }

    if (!empty($orden['timestamp']) && is_numeric($orden['timestamp'])) {
        return date('d/m/Y h:i A', (int)$orden['timestamp']);
    }

    return 'N/A';
}

function claveProductoOrden($texto)
{
    $texto = app_normalize_text((string)$texto);
    $texto = mb_strtolower(trim($texto), 'UTF-8');

    if (function_exists('iconv')) {
        $sinAcentos = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texto);
        if (is_string($sinAcentos) && $sinAcentos !== '') {
            $texto = $sinAcentos;
        }
    }

    $texto = preg_replace('/[^a-z0-9]+/i', ' ', $texto);
    return trim((string)$texto);
}

function construirMapaPreciosProductos()
{
    $productos = Productos::listar();
    $mapa = [];

    foreach ($productos as $producto) {
        $nombre = $producto['nombre'] ?? '';
        $clave = claveProductoOrden($nombre);
        if ($clave === '') {
            continue;
        }

        $mapa[$clave] = (float)($producto['precio'] ?? 0);
    }

    return $mapa;
}

function desglosarItemsOrden($itemsTexto, $mapaPrecios)
{
    $itemsTexto = app_normalize_text((string)$itemsTexto);
    $lineas = preg_split('/\r\n|\r|\n/', $itemsTexto);
    $items = [];

    foreach ($lineas as $linea) {
        $linea = trim((string)$linea);
        if ($linea === '') {
            continue;
        }

        $nombre = $linea;
        $cantidad = 1;

        if (preg_match('/^(.*?)\s*x\s*(\d+)$/u', $linea, $m)) {
            $nombre = trim($m[1]);
            $cantidad = max(1, (int)$m[2]);
        }

        $clave = claveProductoOrden($nombre);
        $precio = (float)($mapaPrecios[$clave] ?? 0);

        $items[] = [
            'nombre' => app_normalize_text($nombre),
            'cantidad' => $cantidad,
            'precio_unitario' => $precio,
            'subtotal' => $precio * $cantidad,
        ];
    }

    return $items;
}

function calcularTotalOrden($items)
{
    $total = 0.0;

    foreach ($items as $item) {
        $total += (float)($item['subtotal'] ?? 0);
    }

    return $total;
}

function construirMapaUsuarios()
{
    $usuarios = Usuarios::listar();
    $mapa = [];

    foreach ($usuarios as $usuario) {
        $id = (int)($usuario['id'] ?? 0);
        if ($id <= 0) {
            continue;
        }

        $nombre = trim(app_normalize_text((string)($usuario['nombre'] ?? '')));
        $apellido = trim(app_normalize_text((string)($usuario['apellido'] ?? '')));
        $nombreCompleto = trim($nombre . ' ' . $apellido);

        $mapa[$id] = $nombreCompleto !== '' ? $nombreCompleto : 'Usuario #' . $id;
    }

    return $mapa;
}

$stats = [
    'total_ordenes' => 0,
    'entregadas' => 0,
    'pendientes' => 0,
    'en_proceso' => 0,
    'total_vendido' => 0,
];

$mapaPrecios = construirMapaPreciosProductos();
$mapaUsuarios = construirMapaUsuarios();
$salida = [];

foreach ($ordenes as $orden) {
    if (!is_array($orden)) {
        continue;
    }

    $estadoNormalizado = normalizarEstadoOrden($orden['estado'] ?? '', $orden['entregada'] ?? null);
    $detalleItems = desglosarItemsOrden($orden['items'] ?? '', $mapaPrecios);
    $total = isset($orden['total']) && is_numeric($orden['total'])
        ? (float)$orden['total']
        : calcularTotalOrden($detalleItems);

    $usuarioId = isset($orden['usuario_id']) ? (int)$orden['usuario_id'] : 0;
    $salida[] = [
        'id_mostrado' => $orden['numero'] ?? $orden['id'] ?? 'N/A',
        'mesa' => app_normalize_text((string)($orden['mesa'] ?? 'N/A')),
        'usuario_nombre' => $mapaUsuarios[$usuarioId] ?? ($usuarioId > 0 ? ('Usuario #' . $usuarioId) : 'Sin usuario'),
        'total' => $total,
        'estado_normalizado' => $estadoNormalizado,
        'fecha_formateada' => formatearFechaOrden($orden),
        'notas' => trim(app_normalize_text((string)($orden['notas'] ?? ''))) !== '' ? app_normalize_text((string)$orden['notas']) : 'Sin notas',
        'items_detalle' => $detalleItems,
    ];

    $stats['total_ordenes']++;
    $stats['total_vendido'] += $total;

    if ($estadoNormalizado === 'entregada') {
        $stats['entregadas']++;
    } elseif ($estadoNormalizado === 'en_proceso' || $estadoNormalizado === 'lista') {
        $stats['en_proceso']++;
    } else {
        $stats['pendientes']++;
    }
}

usort($salida, function ($a, $b) {
    return strnatcmp((string)$b['id_mostrado'], (string)$a['id_mostrado']);
});

echo json_encode([
    'ok' => true,
    'stats' => $stats,
    'ordenes' => $salida,
], JSON_UNESCAPED_UNICODE);

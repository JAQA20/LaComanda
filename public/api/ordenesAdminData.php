<?php
header("Content-Type: application/json; charset=utf-8");

require_once __DIR__ . "/../../middleware/auth.php";
require_once __DIR__ . "/../../middleware/roles.php";
require_once __DIR__ . "/../../model/Conexion.php";

verificarRol([1]);

// Este endpoint deja de leer controller/ordenes.json.
// Ahora construye el historial administrativo 100% desde MySQL, manteniendo
// la forma de salida que las vistas admin ya esperan.

function normalizarEstadoDesdeItems(array $estados): string
{
    if (empty($estados)) {
        return 'pendiente';
    }

    $total = count($estados);
    $entregados = count(array_filter($estados, fn($estado) => $estado === 'entregado'));
    $listosOEntregados = count(array_filter($estados, fn($estado) => in_array($estado, ['listo', 'entregado'], true)));
    $enPreparacion = count(array_filter($estados, fn($estado) => $estado === 'en_preparacion'));

    if ($entregados === $total) {
        return 'entregada';
    }

    if ($listosOEntregados === $total) {
        return 'lista';
    }

    if ($enPreparacion > 0) {
        return 'en_proceso';
    }

    return 'pendiente';
}

function formatearFechaOrden(?string $fechaCreacion, ?string $fechaLista, ?string $fechaEntrega): string
{
    $candidatos = [$fechaEntrega, $fechaLista, $fechaCreacion];
    foreach ($candidatos as $valor) {
        if (!empty($valor)) {
            $timestamp = strtotime($valor);
            if ($timestamp !== false) {
                return date('d/m/Y h:i A', $timestamp);
            }
        }
    }
    return 'N/A';
}

try {
    $conexion = Conexion::conectar();

    $sql = "
        SELECT
            o.id_orden,
            o.numero_orden,
            m.numero AS mesa_numero,
            o.total,
            o.notas,
            o.fecha_creacion,
            o.fecha_lista,
            o.fecha_entrega,
            u.nombre,
            u.apellido,
            d.id_detalle,
            d.cantidad,
            d.precio_unitario,
            d.estado_item,
            d.opciones_json,
            d.observaciones AS notas_item,
            p.nombre AS producto_nombre,
            c.slug AS categoria_slug
        FROM ordenes o
        INNER JOIN mesas m ON m.id = o.mesa_id
        LEFT JOIN usuarios u ON u.id = o.id_usuario
        LEFT JOIN detalle_orden d ON d.id_orden = o.id_orden
        LEFT JOIN productos p ON p.id = d.id_producto
        LEFT JOIN categorias c ON c.id = p.categoria_id
        ORDER BY o.fecha_creacion DESC, d.id_detalle ASC
    ";

    $result = $conexion->query($sql);
    $ordenesAgrupadas = [];

    while ($row = $result->fetch_assoc()) {
        $idOrden = (int)$row['id_orden'];

        if (!isset($ordenesAgrupadas[$idOrden])) {
            $nombreUsuario = trim((string)(($row['nombre'] ?? '') . ' ' . ($row['apellido'] ?? '')));
            $ordenesAgrupadas[$idOrden] = [
                'id_mostrado' => (int)$row['numero_orden'],
                'mesa' => (string)$row['mesa_numero'],
                'usuario_nombre' => $nombreUsuario !== '' ? $nombreUsuario : 'Sin usuario',
                'total' => (float)($row['total'] ?? 0),
                'fecha_formateada' => formatearFechaOrden(
                    $row['fecha_creacion'] ?? null,
                    $row['fecha_lista'] ?? null,
                    $row['fecha_entrega'] ?? null,
                ),
                'notas' => trim((string)($row['notas'] ?? '')) !== '' ? (string)$row['notas'] : 'Sin notas',
                'items_detalle' => [],
                'subordenes' => [
                    'cocina' => [],
                    'barista' => [],
                ],
                '_estados' => [],
            ];
        }

        if (!empty($row['id_detalle'])) {
            $cantidad = (int)($row['cantidad'] ?? 0);
            $precioUnitario = (float)($row['precio_unitario'] ?? 0);
            $categoriaSlug = (string)($row['categoria_slug'] ?? '');
            $area = in_array($categoriaSlug, ['cafes', 'bebidas', 'especialidades'], true) ? 'barista' : 'cocina';
            $estadoItem = (string)($row['estado_item'] ?? 'pendiente');

            $ordenesAgrupadas[$idOrden]['items_detalle'][] = [
                'nombre' => (string)($row['producto_nombre'] ?? 'Producto'),
                'cantidad' => $cantidad,
                'precio_unitario' => $precioUnitario,
                'subtotal' => $cantidad * $precioUnitario,
                'estado_item' => $estadoItem,
                'area' => $area,
                'opciones_json' => !empty($row['opciones_json']) ? json_decode((string)$row['opciones_json'], true) : [],
                'notas_item' => (string)($row['notas_item'] ?? ''),
            ];
            $ordenesAgrupadas[$idOrden]['subordenes'][$area][] = $estadoItem;
            $ordenesAgrupadas[$idOrden]['_estados'][] = $estadoItem;
        }
    }

    $stats = [
        'total_ordenes' => 0,
        'entregadas' => 0,
        'pendientes' => 0,
        'en_proceso' => 0,
        'total_vendido' => 0,
    ];

    $salida = [];
    foreach ($ordenesAgrupadas as $orden) {
        $orden['estado_normalizado'] = normalizarEstadoDesdeItems($orden['_estados']);
        $orden['estado_subordenes'] = [
            'cocina' => normalizarEstadoDesdeItems($orden['subordenes']['cocina']),
            'barista' => normalizarEstadoDesdeItems($orden['subordenes']['barista']),
        ];
        unset($orden['_estados']);

        $stats['total_ordenes']++;
        $stats['total_vendido'] += (float)($orden['total'] ?? 0);

        if ($orden['estado_normalizado'] === 'entregada') {
            $stats['entregadas']++;
        } elseif (in_array($orden['estado_normalizado'], ['en_proceso', 'lista'], true)) {
            $stats['en_proceso']++;
        } else {
            $stats['pendientes']++;
        }

        $salida[] = $orden;
    }

    usort($salida, fn($a, $b) => ((int)$b['id_mostrado']) <=> ((int)$a['id_mostrado']));

    echo json_encode([
        'ok' => true,
        'stats' => $stats,
        'ordenes' => $salida,
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'message' => $e->getMessage(),
    ], JSON_UNESCAPED_UNICODE);
}

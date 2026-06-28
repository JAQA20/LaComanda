<?php
require_once __DIR__ . "/../../middleware/auth.php";
require_once __DIR__ . "/../../middleware/roles.php";
require_once __DIR__ . "/../../model/Conexion.php";

header("Content-Type: application/json; charset=UTF-8");
verificarRol([1]); // Solo administradores pueden hacer esto

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'ERROR', 'message' => 'Método no permitido']);
    exit;
}

$numero = $_POST['numero'] ?? null;
$estado = $_POST['estado'] ?? null;
$area = $_POST['area'] ?? null;

if (!$numero || !$estado || !$area) {
    http_response_code(400);
    echo json_encode(['status' => 'ERROR', 'message' => 'Datos incompletos']);
    exit;
}

// Mapear el estado general a los estados de items
$estadoItemMap = [
    'pendiente' => 'pendiente',
    'en_proceso' => 'en_preparacion',
    'lista' => 'listo',
    'entregada' => 'entregado',
    'cancelada' => 'cancelado'
];

if (!isset($estadoItemMap[$estado])) {
    http_response_code(400);
    echo json_encode(['status' => 'ERROR', 'message' => 'Estado no válido']);
    exit;
}

$estadoItem = $estadoItemMap[$estado];
$fechaAhora = date('Y-m-d H:i:s');

try {
    $conexion = Conexion::conectar();
    $conexion->begin_transaction();

    // Actualizar el estado de los items en detalle_orden que pertenezcan al area correspondiente
    $areaCondition = "";
    if ($area === 'barista') {
        $areaCondition = "AND c.slug IN ('cafes', 'bebidas')";
    } elseif ($area === 'cocina') {
        $areaCondition = "AND c.slug NOT IN ('cafes', 'bebidas')";
    } else {
        throw new Exception("Área inválida");
    }

    $stmt = $conexion->prepare("
        UPDATE detalle_orden d
        INNER JOIN ordenes o ON o.id_orden = d.id_orden
        INNER JOIN productos p ON p.id = d.id_producto
        INNER JOIN categorias c ON c.id = p.categoria_id
        SET d.estado_item = ?
        WHERE o.numero_orden = ? $areaCondition
    ");
    $stmt->bind_param("si", $estadoItem, $numero);
    $stmt->execute();

    // Marcar fechas dependiendo del estado
    if ($estadoItem === 'en_preparacion') {
        $conexion->query("UPDATE detalle_orden d INNER JOIN ordenes o ON o.id_orden = d.id_orden INNER JOIN productos p ON p.id = d.id_producto INNER JOIN categorias c ON c.id = p.categoria_id SET d.fecha_inicio_preparacion = COALESCE(d.fecha_inicio_preparacion, '$fechaAhora') WHERE o.numero_orden = $numero AND d.estado_item = 'en_preparacion' $areaCondition");
    } elseif ($estadoItem === 'listo') {
        $conexion->query("UPDATE detalle_orden d INNER JOIN ordenes o ON o.id_orden = d.id_orden INNER JOIN productos p ON p.id = d.id_producto INNER JOIN categorias c ON c.id = p.categoria_id SET d.fecha_lista = COALESCE(d.fecha_lista, '$fechaAhora') WHERE o.numero_orden = $numero AND d.estado_item = 'listo' $areaCondition");
    } elseif ($estadoItem === 'entregado') {
        $conexion->query("UPDATE detalle_orden d INNER JOIN ordenes o ON o.id_orden = d.id_orden INNER JOIN productos p ON p.id = d.id_producto INNER JOIN categorias c ON c.id = p.categoria_id SET d.fecha_entrega = COALESCE(d.fecha_entrega, '$fechaAhora') WHERE o.numero_orden = $numero AND d.estado_item = 'entregado' $areaCondition");
    }

    // Actualizar campos globales en ordenes si TODA la orden se completó, pero como es por áreas lo dejaremos así.
    // Solo actualizamos las subordenes.


    // Elimino esta actualización global, para que solo afecte cuando las dos esten listas,
    // o el cron / sync normal lo hará.


    $conexion->commit();
    echo json_encode(['status' => 'OK']);
} catch (Throwable $e) {
    if (isset($conexion)) {
        $conexion->rollback();
    }
    http_response_code(500);
    echo json_encode(['status' => 'ERROR', 'message' => $e->getMessage()]);
}

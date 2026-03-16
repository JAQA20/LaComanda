<?php
require_once __DIR__ . "../model/Conexion.php";

$ordenes = [];

// Pendientes - SOLO BEBIDAS (tipo_preparacion='barista')
$stmt = $conexion->prepare("
    SELECT DISTINCT o.id, o.mesa_id, o.id_estado, o.creado_en 
    FROM ordenes o
    JOIN orden_items oi ON o.id = oi.orden_id
    JOIN productos p ON oi.producto_id = p.id
    WHERE o.id_estado = 1 AND p.tipo_preparacion = 'barista'
    ORDER BY o.creado_en ASC
");
$stmt->execute();
$pendientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($pendientes as $o) {
    $items = [];
    // Solo traer items que sean bebidas (barista)
    $s2 = $conexion->prepare("
        SELECT oi.cantidad, oi.precio_unitario, p.nombre 
        FROM orden_items oi 
        JOIN productos p ON oi.producto_id = p.id 
        WHERE oi.orden_id = :id AND p.tipo_preparacion = 'barista'
    ");
    $s2->execute([':id' => $o['id']]);
    $rows = $s2->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $r) {
        $items[] = ['producto_id' => $r['nombre'], 'cantidad' => $r['cantidad'], 'precio' => $r['precio_unitario']];
    }

    if (count($items) > 0) {
        $ordenes[] = [
            'id_orden' => $o['id'],
            'mesa_id' => $o['mesa_id'],
            'items' => $items,
            'estado' => 'pendiente',
            'creado_en' => $o['creado_en']
        ];
    }
}

// Entregadas (últimas 20) - SOLO BEBIDAS
$stmt = $conexion->prepare("
    SELECT DISTINCT o.id, o.mesa_id, o.id_estado, o.hora_entrega 
    FROM ordenes o
    JOIN orden_items oi ON o.id = oi.orden_id
    JOIN productos p ON oi.producto_id = p.id
    WHERE o.id_estado = 4 AND p.tipo_preparacion = 'barista'
    ORDER BY o.hora_entrega DESC LIMIT 20
");
$stmt->execute();
$entregadas = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($entregadas as $o) {
    $items = [];
    $s2 = $conexion->prepare("
        SELECT oi.cantidad, oi.precio_unitario, p.nombre 
        FROM orden_items oi 
        JOIN productos p ON oi.producto_id = p.id 
        WHERE oi.orden_id = :id AND p.tipo_preparacion = 'barista'
    ");
    $s2->execute([':id' => $o['id']]);
    $rows = $s2->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $r) {
        $items[] = ['producto_id' => $r['nombre'], 'cantidad' => $r['cantidad'], 'precio' => $r['precio_unitario']];
    }

    if (count($items) > 0) {
        $ordenes[] = [
            'id_orden' => $o['id'],
            'mesa_id' => $o['mesa_id'],
            'items' => $items,
            'estado' => 'entregada',
            'hora_entrega' => $o['hora_entrega']
        ];
    }
}

echo json_encode($ordenes);

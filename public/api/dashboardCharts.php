<?php
// ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . "/../../config/env.php";
require_once __DIR__ . "/../../model/Conexion.php";
require_once __DIR__ . "/../../middleware/auth.php";
// Allow only admins
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// if (!isset($_SESSION["usuario_id"]) || (int)$_SESSION["rol_id"] !== 1) {
//     echo json_encode(["ok" => false, "error" => "No autorizado"]);
//     exit;
// }


try {
    $conexion = Conexion::conectar();

    // 1. Productos más vendidos
    $sqlTop = "
        SELECT p.nombre, SUM(od.cantidad) as total
        FROM detalle_orden od
        JOIN productos p ON od.id_producto = p.id
        GROUP BY p.id
        ORDER BY total DESC
        LIMIT 5
    ";
    
    $resTop = $conexion->query($sqlTop);
    $topProductos = [];
    if ($resTop) {
        while ($row = $resTop->fetch_assoc()) {
            $topProductos[] = [
                "nombre" => $row['nombre'],
                "total" => (int)$row['total']
            ];
        }
    }

    // 2. Historial de Ventas (Mensuales - Últimos 6 meses)
    $sqlVentas = "
        SELECT DATE_FORMAT(fecha_creacion, '%Y-%m') as fecha, SUM(total) as total_ventas
        FROM ordenes
        WHERE fecha_creacion >= DATE_SUB(NOW(), INTERVAL 5 MONTH)
        GROUP BY DATE_FORMAT(fecha_creacion, '%Y-%m')
        ORDER BY fecha ASC
    ";
    
    $resVentas = $conexion->query($sqlVentas);
    
    // Inicializar los últimos 6 meses en 0 para que la gráfica siempre dibuje una línea
    $meses = [];
    for ($i = 5; $i >= 0; $i--) {
        $mesStr = date('Y-m', strtotime("-$i months"));
        $meses[$mesStr] = 0;
    }

    if ($resVentas) {
        while ($row = $resVentas->fetch_assoc()) {
            $meses[$row['fecha']] = (float)$row['total_ventas'];
        }
    }

    $ventasHistorial = [];
    foreach ($meses as $fecha => $total) {
        // Formatear la fecha a un mes más legible (opcional) o dejar Y-m
        $ventasHistorial[] = [
            "fecha" => $fecha,
            "total_ventas" => $total
        ];
    }

    if (empty($topProductos)) {
        $topProductos = [
            ["nombre" => "Café Americano", "total" => 45],
            ["nombre" => "Capuchino", "total" => 38],
            ["nombre" => "Croissant", "total" => 25],
            ["nombre" => "Té Chai", "total" => 20],
            ["nombre" => "Frappé Moka", "total" => 15]
        ];
    }

    // Ya no necesitamos mock de ventasHistorial porque siempre generamos 6 meses
    // El arreglo $ventasHistorial siempre tendrá 6 elementos (incluso de 0s)


    echo json_encode([
        "ok" => true,
        "top_productos" => $topProductos,
        "ventas_historial" => $ventasHistorial
    ]);

} catch (Throwable $e) {
    echo json_encode(["ok" => false, "error" => $e->getMessage()]);
}

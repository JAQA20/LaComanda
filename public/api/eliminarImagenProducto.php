<?php
require_once __DIR__ . "/../../middleware/auth.php";
require_once __DIR__ . "/../../middleware/roles.php";
require_once __DIR__ . "/../../model/Productos.php";

header('Content-Type: application/json');

try {
    verificarRol([1]);

    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    
    if ($id <= 0) {
        throw new Exception("ID de producto inválido.");
    }

    $producto = Productos::obtenerPorId($id);
    if (!$producto) {
        throw new Exception("Producto no encontrado.");
    }

    Productos::actualizar($id, $producto['categoria_id'], $producto['nombre'], $producto['precio'], "", $producto['activo']);

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

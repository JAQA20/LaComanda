<?php
require_once __DIR__ . "/../middleware/auth.php";
require_once __DIR__ . "/../middleware/roles.php";
require_once __DIR__ . "/../config/rutas.php";
verificarRol([1]);


// ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . "/../model/Productos.php";

try {
    $id = (int)($_POST["id"] ?? 0);
    Productos::eliminar($id);
    header("Location: " . BASE_URL . "views/admin/productos.php?deleted=1");
    exit;
} catch (Throwable $e) {
    header("Location: " . BASE_URL . "views/admin/productos.php?error=" . urlencode($e->getMessage()));
    exit;
}

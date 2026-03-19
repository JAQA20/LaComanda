<?php
require_once __DIR__ . "/../config/env.php";
app_configure_errors();
require_once __DIR__ . "/../middleware/auth.php";
require_once __DIR__ . "/../middleware/roles.php";
require_once __DIR__ . "/../config/rutas.php";
require_once __DIR__ . "/../model/Categorias.php";

verificarRol([1]);

try {
    $id = (int)($_POST['id'] ?? 0);
    Categorias::eliminar($id);
    header("Location: " . BASE_URL . "views/admin/productos.php?categoryDeleted=1");
    exit;
} catch (Throwable $e) {
    header("Location: " . BASE_URL . "views/admin/productos.php?error=" . urlencode($e->getMessage()));
    exit;
}

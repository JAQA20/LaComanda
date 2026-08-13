<?php
require_once __DIR__ . "/../config/env.php";
app_configure_errors();
require_once __DIR__ . "/../middleware/auth.php";
require_once __DIR__ . "/../middleware/roles.php";
require_once __DIR__ . "/../config/rutas.php";
require_once __DIR__ . "/../model/Categorias.php";

verificarRol([1]);

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido.');
    }

    Categorias::crear(
        $_POST['nombre'] ?? '',
        $_POST['slug'] ?? '',
        $_POST['icono'] ?? 'fa-tags',
        $_POST['area'] ?? 'cocina',
        (int)($_POST['orden'] ?? 1),
        isset($_POST['activo']) ? 1 : 0
    );

    header("Location: " . BASE_URL . "views/admin/productos.php?categoryCreated=1");
    exit;
} catch (Throwable $e) {
    header("Location: " . BASE_URL . "views/admin/productos.php?error=" . urlencode($e->getMessage()));
    exit;
}

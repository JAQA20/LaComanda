<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . "/../middleware/auth.php";
require_once __DIR__ . "/../middleware/roles.php";
require_once __DIR__ . "/../config/rutas.php";
require_once __DIR__ . "/../model/Productos.php";
verificarRol([1]);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: " . BASE_URL . "views/admin/nuevoProducto.php");
    exit;
}

$old = [
    "categoria_id" => trim($_POST["categoria_id"] ?? ""),
    "nombre" => trim($_POST["nombre"] ?? ""),
    "precio" => trim($_POST["precio"] ?? ""),
    "icono" => trim($_POST["icono"] ?? "fa-mug-hot"),
    "activo" => isset($_POST["activo"]) ? "1" : "0"
];

try {
    if ($old["categoria_id"] === "" || $old["nombre"] === "" || $old["precio"] === "") {
        throw new Exception("Todos los campos obligatorios deben completarse.");
    }

    Productos::crear(
        (int)$old["categoria_id"],
        $old["nombre"],
        (int)$old["precio"],
        $old["icono"],
        (int)$old["activo"]
    );

    $_SESSION["nuevo_producto_success"] = "Producto creado correctamente.";
    header("Location: " . BASE_URL . "views/admin/productos.php?created=1");
    exit;
} catch (Throwable $e) {
    $_SESSION["nuevo_producto_errors"] = [$e->getMessage()];
    $_SESSION["nuevo_producto_old"] = $old;
    header("Location: " . BASE_URL . "views/admin/nuevoProducto.php");
    exit;
}

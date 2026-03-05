<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . "/../middleware/auth.php";
require_once __DIR__ . "/../middleware/roles.php";
verificarRol([1]);

require_once __DIR__ . "/../model/Productos.php";

$errors = [];
$old = [
    "categoria_id" => "",
    "nombre" => "",
    "precio" => "",
    "icono" => "fa-mug-hot",
    "activo" => "1"
];

try {
    $categorias = Productos::listarCategoriasActivas();
} catch (Throwable $e) {
    $categorias = [];
    $errors[] = "Error cargando categorías: " . $e->getMessage();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $old["categoria_id"] = $_POST["categoria_id"] ?? "";
    $old["nombre"] = $_POST["nombre"] ?? "";
    $old["precio"] = $_POST["precio"] ?? "";
    $old["icono"] = $_POST["icono"] ?? "fa-mug-hot";
    $old["activo"] = isset($_POST["activo"]) ? "1" : "0";

    try {
        Productos::crear(
            (int)$old["categoria_id"],
            $old["nombre"],
            (int)$old["precio"],
            $old["icono"],
            (int)$old["activo"]
        );

        header("Location: /LaComanda-main/views/admin/productos.php?created=1");
        exit;
    } catch (Throwable $e) {
        $errors[] = $e->getMessage();
    }
}

// Render vista HTML
require_once __DIR__ . "/../views/admin/nuevoProducto.php";

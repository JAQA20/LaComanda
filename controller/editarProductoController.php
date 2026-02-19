<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . "/../middleware/auth.php";
require_once __DIR__ . "/../middleware/roles.php";
verificarRol([1]);

require_once __DIR__ . "/../model/Productos.php";

$errors = [];
$id = (int)($_GET["id"] ?? 0);

try {
    $categorias = Productos::listarCategoriasActivas();
} catch (Throwable $e) {
    $categorias = [];
    $errors[] = "Error cargando categorías: " . $e->getMessage();
}

$producto = null;
try {
    $producto = Productos::obtenerPorId($id);
    if (!$producto) throw new Exception("Producto no encontrado.");
} catch (Throwable $e) {
    $errors[] = $e->getMessage();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $idPost = (int)($_POST["id"] ?? 0);

    $categoria_id = (int)($_POST["categoria_id"] ?? 0);
    $nombre = $_POST["nombre"] ?? "";
    $precio = (int)($_POST["precio"] ?? 0);
    $icono = $_POST["icono"] ?? "fa-mug-hot";
    $activo = isset($_POST["activo"]) ? 1 : 0;

    try {
        Productos::actualizar($idPost, $categoria_id, $nombre, $precio, $icono, $activo);
        header("Location: /LaComanda/views/admin/productos.php?updated=1");
        exit;
    } catch (Throwable $e) {
        $errors[] = $e->getMessage();
        // Para repintar el form con lo que intentó guardar:
        $producto = [
            "id" => $idPost,
            "categoria_id" => $categoria_id,
            "nombre" => $nombre,
            "precio" => $precio,
            "icono" => $icono,
            "activo" => $activo
        ];
    }
}

require_once __DIR__ . "/../views/admin/editarProducto.php";

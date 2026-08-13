<?php
// ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . "/../middleware/auth.php";
require_once __DIR__ . "/../middleware/roles.php";
require_once __DIR__ . "/../config/rutas.php";
require_once __DIR__ . "/../model/Productos.php";

verificarRol([1]);

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: " . BASE_URL . "views/admin/productos.php");
    exit;
}

$idPost = (int)($_POST["id"] ?? 0);
$categoria_id = (int)($_POST["categoria_id"] ?? 0);
$nombre = trim($_POST["nombre"] ?? "");
$precio = (int)($_POST["precio"] ?? 0);
$imagen_url = trim($_POST["imagen_url"] ?? "");
$activo = isset($_POST["activo"]) ? 1 : 0;

try {
    if ($idPost === 0 || $categoria_id === 0 || $nombre === "" || $precio <= 0) {
        throw new Exception("Todos los campos obligatorios deben completarse y ser válidos.");
    }

    $productoOriginal = Productos::obtenerPorId($idPost);
    if (!$productoOriginal) {
        throw new Exception("Producto no encontrado.");
    }

    $imagenFinal = $productoOriginal['imagen'] ?? null;
    $eliminar_imagen = isset($_POST["eliminar_imagen"]) ? 1 : 0;

    if ($eliminar_imagen) {
        $imagenFinal = null;
    }
    
    // 1. Check if a new file was uploaded
    if (isset($_FILES['imagen_file']) && $_FILES['imagen_file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['imagen_file'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
        
        if (!in_array($file['type'], $allowedTypes)) {
            throw new Exception("El archivo debe ser una imagen válida (JPG, PNG, WebP).");
        }

        if ($file['size'] > 20971520) { // 20 MB
            throw new Exception("La imagen es demasiado grande. El tamaño máximo permitido es 20 MB.");
        }

        $uploadDir = __DIR__ . "/../public/img/productos/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('prod_') . '.' . $ext;
        $destination = $uploadDir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            throw new Exception("No se pudo guardar la imagen subida.");
        }

        $imagenFinal = '/public/img/productos/' . $filename;
    } 
    // 2. Fallback to URL if provided and no file uploaded
    elseif ($imagen_url !== "") {
        $imagenFinal = $imagen_url;
    }

    Productos::actualizar($idPost, $categoria_id, $nombre, $precio, $imagenFinal, $activo);
    header("Location: " . BASE_URL . "views/admin/productos.php?updated=1");
    exit;
} catch (Throwable $e) {
    header("Location: " . BASE_URL . "views/admin/productos.php?error=" . urlencode($e->getMessage()));
    exit;
}

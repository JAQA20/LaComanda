<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . "/../middleware/auth.php";
require_once __DIR__ . "/../middleware/roles.php";
require_once __DIR__ . "/../config/rutas.php";
verificarRol([1]); // Solo Admin

require_once __DIR__ . "/../model/Usuarios.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: /../views/admin/usuarios.php");
    exit;
}

$id = isset($_POST["id"]) ? (int)$_POST["id"] : 0;
if ($id <= 0) {
    header("Location: " . BASE_URL . "views/admin/usuarios.php?error=bad_id");
    exit;
}


if ($id === 1) {
    header("Location: " . BASE_URL . "views/admin/usuarios.php?error=root_delete");
    exit;
}

// Evitar que el admin se borre a sí mismo
$miId = isset($_SESSION["user_id"]) ? (int)$_SESSION["user_id"] : 0;
if ($miId > 0 && $id === $miId) {
    header("Location: " . BASE_URL . "views/admin/usuarios.php?error=self_delete");
    exit;
}

try {
    Usuarios::eliminar($id);
    header("Location: " . BASE_URL . "views/admin/usuarios.php?deleted=1");
    exit;
} catch (Throwable $e) {
    header("Location: " . BASE_URL . "views/admin/usuarios.php?error=delete_failed");
    exit;
}

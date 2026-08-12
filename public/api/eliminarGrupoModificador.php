<?php
ini_set('display_errors', 0);
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . "/../../middleware/auth.php";
require_once __DIR__ . "/../../middleware/roles.php";
require_once __DIR__ . "/../../model/Modificadores.php";

try {
    verificarRol([1]); // Solo admins
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        throw new Exception("Method not allowed");
    }

    $id = intval($_POST['id'] ?? 0);
    if ($id <= 0) {
        throw new Exception("ID inválido");
    }

    $res = Modificadores::eliminarGrupo($id);
    if (!$res) {
        throw new Exception("No se pudo eliminar el grupo");
    }

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

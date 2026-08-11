<?php
ini_set('display_errors', 0);
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . "/../../middleware/auth.php";
require_once __DIR__ . "/../../middleware/roles.php";
require_once __DIR__ . "/../../config/rutas.php";
require_once __DIR__ . "/../../model/Conexion.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    verificarRol([1]); // Solo admins pueden hacer esto
    
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        throw new Exception("Method not allowed");
    }

    $input = json_decode(file_get_contents("php://input"), true);
    
    $otpCode = "";
    if (is_array($input) && isset($input["otpCode"])) {
        $otpCode = $input["otpCode"];
    } elseif (isset($_POST["otpCode"])) {
        $otpCode = $_POST["otpCode"];
    }

    $otpCode = trim($otpCode);

    if (empty($otpCode)) {
        echo json_encode(["success" => false, "message" => "Código vacío"]);
        exit;
    }

    $adminId = $_SESSION["usuario_id"] ?? 0;
    if ($adminId <= 0) {
        throw new Exception("Sesión inválida");
    }

    $conexion = Conexion::conectar();
    
    // Buscar un OTP válido para este admin
    // Usamos el hash para buscarlo
    $tokenStored = hash('sha256', $otpCode);

    $stmt = $conexion->prepare("
        SELECT id 
        FROM password_resets 
        WHERE usuario_id = ? 
          AND token = ? 
          AND usado = 0 
          AND expira_en > NOW()
        LIMIT 1
    ");
    $stmt->bind_param("is", $adminId, $tokenStored);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {
        // Código válido
        $row = $res->fetch_assoc();
        $resetId = $row["id"];

        // Marcar como usado
        $stmtUpdate = $conexion->prepare("UPDATE password_resets SET usado = 1 WHERE id = ?");
        $stmtUpdate->bind_param("i", $resetId);
        $stmtUpdate->execute();

        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "message" => "Código incorrecto o expirado"]);
    }

} catch (Throwable $e) {
    echo json_encode(["success" => false, "message" => "Error interno: " . $e->getMessage()]);
}

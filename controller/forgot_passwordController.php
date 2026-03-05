<?php
header("Content-Type: application/json; charset=utf-8");
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once __DIR__ . "/../model/Conexion.php";

// crear un objeto de conexión para evitar depender de la variable global
global $conexion;
$conexion = Conexion::conectar();

try {
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        throw new Exception("Método no permitido");
    }

    $email = isset($_POST['email']) ? trim($_POST['email']) : null;

    if (!$email) {
        throw new Exception("Email requerido");
    }

    // Validar que sea un email válido
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Email inválido");
    }

    // Buscar usuario por email
    $stmt = $conexion->prepare("SELECT id, nombre FROM usuarios WHERE email = ? AND activo = 1 LIMIT 1");
    if (!$stmt) {
        throw new Exception("Error en consulta: " . $conexion->error);
    }

    $stmt->bind_param("s", $email);
    if (!$stmt->execute()) {
        throw new Exception("Error al ejecutar consulta: " . $stmt->error);
    }

    $result = $stmt->get_result();
    $usuario = $result->fetch_assoc();

    if (!$usuario) {
        // No devolvemos que el email no existe por seguridad
        echo json_encode([
            "status" => "OK",
            "message" => "Si el email existe en nuestros registros, recibirás un enlace de recuperación",
            "found" => false
        ]);
        exit;
    }

    // Generar token único
    $token = bin2hex(random_bytes(32));
    $expira_en = date('Y-m-d H:i:s', time() + 3600); // 1 hora

    // Insertar en tabla password_resets
    $stmt_insert = $conexion->prepare("
        INSERT INTO password_resets (usuario_id, token, expira_en, usado) 
        VALUES (?, ?, ?, 0)
    ");
    if (!$stmt_insert) {
        throw new Exception("Error al preparar inserción: " . $conexion->error);
    }

    $stmt_insert->bind_param("iss", $usuario['id'], $token, $expira_en);
    if (!$stmt_insert->execute()) {
        throw new Exception("Error al guardar token: " . $stmt_insert->error);
    }

    // Construir enlace de reset
    $reset_link = "http://localhost/LaComanda-main/views/resetPassword.php?token=" . urlencode($token) . "&email=" . urlencode($email);

    // Enviar email (usando mail() o SMTP)
    $asunto = "Recuperar contraseña - La Comanda";
    $mensaje = "Hola " . htmlspecialchars($usuario['nombre']) . ",\n\n";
    $mensaje .= "Haz clic en el siguiente enlace para recuperar tu contraseña:\n";
    $mensaje .= $reset_link . "\n\n";
    $mensaje .= "Este enlace expira en 1 hora.\n\n";
    $mensaje .= "Si no solicitaste esto, ignora este correo.\n";

    $headers = "From: noreply@lacomanda.local\r\n";
    $headers .= "Content-Type: text/plain; charset=utf-8\r\n";

    // Intentar enviar (puede fallar si no hay SMTP configurado)
    $email_sent = @mail($email, $asunto, $mensaje, $headers);

    // Retornar éxito con el enlace para que el usuario lo use directamente
    echo json_encode([
        "status" => "OK",
        "message" => "Enlace de recuperación generado. Haz clic en el botón de abajo para cambiar tu contraseña.",
        "reset_link" => $reset_link,
        "found" => true,
        "email_sent" => $email_sent
    ]);
    exit;
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        "status" => "ERROR",
        "message" => $e->getMessage()
    ]);
    exit;
}
?>

<?php
header("Content-Type: application/json; charset=utf-8");
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once __DIR__ . "/../db/conexion.php";

try {
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        throw new Exception("Método no permitido");
    }

    $email = isset($_POST['email']) ? trim($_POST['email']) : null;

    if (!$email) {
        throw new Exception("Email requerido");
    }

    // Buscar usuario por email y verificar que esté activo
    $stmt = $conexion->prepare("SELECT id, nombre, activo FROM usuarios WHERE email = :email AND activo = 1 LIMIT 1");
    $stmt->execute([':email' => $email]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

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
    $stmt = $conexion->prepare("
        INSERT INTO password_resets (usuario_id, token, expira_en) 
        VALUES (:usuario_id, :token, :expira_en)
    ");
    $stmt->execute([
        ':usuario_id' => $usuario['id'],
        ':token' => $token,
        ':expira_en' => $expira_en
    ]);

    // Construir enlace de reset
    $reset_link = "http://localhost/LaComanda-main/views/reset_password.php?token=" . urlencode($token) . "&email=" . urlencode($email);

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
        "found" => true
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

<<<<<<< Updated upstream
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
=======
<?php
header("Content-Type: application/json; charset=utf-8");
ini_set('display_errors', 0);
error_reporting(E_ALL);

function resetResponse()
{
    echo json_encode([
        "status" => "OK",
        "message" => "Si el correo está registrado, recibirás instrucciones para restablecer tu contraseña."
    ]);
    exit;
}

try {
    require_once __DIR__ . "/../config/rutas.php";
    require_once __DIR__ . "/../model/Conexion.php";
    require_once __DIR__ . "/../model/MailtrapMailer.php";

    $conexion = Conexion::conectar();

    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        http_response_code(405);
        echo json_encode([
            "status" => "ERROR",
            "message" => "Método no permitido"
        ]);
        exit;
    }

    $email = trim($_POST['email'] ?? '');
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        resetResponse();
    }

    $maxAttempts = 3;

    $stmtRate = $conexion->prepare("SELECT COUNT(*) AS total FROM password_resets WHERE usado = 0 AND expira_en > NOW()");
    $stmtRate->execute();
    $rateRes = $stmtRate->get_result();
    $rate = $rateRes ? (int)($rateRes->fetch_assoc()['total'] ?? 0) : 0;

    if ($rate >= 200) {
        resetResponse();
    }

    $stmt = $conexion->prepare("SELECT id, nombre, activo FROM usuarios WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();
    $usuario = $res ? $res->fetch_assoc() : null;

    if (!$usuario || (isset($usuario['activo']) && (int)$usuario['activo'] !== 1)) {
        resetResponse();
    }

    $stmtLimit = $conexion->prepare("SELECT COUNT(*) AS total FROM password_resets WHERE usuario_id = ? AND usado = 0 AND expira_en > NOW()");
    $uid = (int)$usuario['id'];
    $stmtLimit->bind_param("i", $uid);
    $stmtLimit->execute();
    $limitRes = $stmtLimit->get_result();
    $userAttempts = $limitRes ? (int)($limitRes->fetch_assoc()['total'] ?? 0) : 0;

    if ($userAttempts >= $maxAttempts) {
        resetResponse();
    }

    $tokenPlain = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
    $tokenStored = hash('sha256', $tokenPlain);

    $stmtInvalidate = $conexion->prepare("UPDATE password_resets SET usado = 1 WHERE usuario_id = ? AND usado = 0");
    $stmtInvalidate->bind_param("i", $uid);
    $stmtInvalidate->execute();

    $stmtInsert = $conexion->prepare("INSERT INTO password_resets (usuario_id, token, expira_en, usado) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 60 MINUTE), 0)");
    $stmtInsert->bind_param("is", $uid, $tokenStored);
    $stmtInsert->execute();

    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $resetLink = $scheme . '://' . $host . rtrim(BASE_URL, '/') . '/views/resetPassword.php?token=' . urlencode($tokenPlain);

    $subject = "Recuperar contraseña - La Comanda";
    $name = trim(($usuario['nombre'] ?? 'usuario'));

    $message = "Hola {$name},\n\n";
    $message .= "Recibimos una solicitud para restablecer tu contraseña.\n";
    $message .= "Usa este enlace (expira en 60 minutos):\n{$resetLink}\n\n";
    $message .= "Si no solicitaste este cambio, puedes ignorar este mensaje.\n";

    try {
        MailtrapMailer::send(
            $email,
            (string)($usuario['nombre'] ?? ''),
            $subject,
            $message,
            '<p>Hola ' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . ',</p>' .
                '<p>Recibimos una solicitud para restablecer tu contraseña.</p>' .
                '<p>Usa este enlace (expira en 60 minutos):<br><a href="' . htmlspecialchars($resetLink, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($resetLink, ENT_QUOTES, 'UTF-8') . '</a></p>' .
                '<p>Si no solicitaste este cambio, puedes ignorar este mensaje.</p>'
        );
    } catch (Throwable $mailError) {
        error_log("Error Mailtrap reset {$email}: " . $mailError->getMessage());
    }

    resetResponse();
} catch (Throwable $e) {
    error_log("Forgot password error: " . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        "status" => "ERROR",
        "message" => $e->getMessage()
    ]);
    exit;
}
>>>>>>> Stashed changes

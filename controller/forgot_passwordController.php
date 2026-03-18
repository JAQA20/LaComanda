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
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    $basePath = preg_replace('#/controller$#', '', rtrim($scriptDir, '/'));
    $resetLink = $scheme . '://' . $host . $basePath . '/views/resetPassword.php?token=' . urlencode($tokenPlain);

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
?>

// header("Content-Type: application/json; charset=utf-8");
// ini_set('display_errors', 0);
// error_reporting(E_ALL);

// function resetResponse()
// {
// echo json_encode([
// "status" => "OK",
// "message" => "Si el correo está registrado, recibirás instrucciones para restablecer tu contraseña."
// ]);
// exit;
// }

// try {
// require_once __DIR__ . "/../config/rutas.php";
// require_once __DIR__ . "/../model/Conexion.php";
// require_once __DIR__ . "/../model/MailtrapMailer.php";

// $conexion = Conexion::conectar();

// if ($_SERVER["REQUEST_METHOD"] !== "POST") {
// http_response_code(405);
// echo json_encode([
// "status" => "ERROR",
// "message" => "Método no permitido"
// ]);
// exit;
// }

// $email = trim($_POST['email'] ?? '');
// if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
// resetResponse();
// }

// $maxAttempts = 3;

// $stmtRate = $conexion->prepare("SELECT COUNT(*) AS total FROM password_resets WHERE usado = 0 AND expira_en > NOW()");
// $stmtRate->execute();
// $rateRes = $stmtRate->get_result();
// $rate = $rateRes ? (int)($rateRes->fetch_assoc()['total'] ?? 0) : 0;

// if ($rate >= 200) {
// resetResponse();
// }

// $stmt = $conexion->prepare("SELECT id, nombre, activo FROM usuarios WHERE email = ? LIMIT 1");
// $stmt->bind_param("s", $email);
// $stmt->execute();
// $res = $stmt->get_result();
// $usuario = $res ? $res->fetch_assoc() : null;

// if (!$usuario || (isset($usuario['activo']) && (int)$usuario['activo'] !== 1)) {
// resetResponse();
// }

// $stmtLimit = $conexion->prepare("SELECT COUNT(*) AS total FROM password_resets WHERE usuario_id = ? AND usado = 0 AND expira_en > NOW()");
// $uid = (int)$usuario['id'];
// $stmtLimit->bind_param("i", $uid);
// $stmtLimit->execute();
// $limitRes = $stmtLimit->get_result();
// $userAttempts = $limitRes ? (int)($limitRes->fetch_assoc()['total'] ?? 0) : 0;

// if ($userAttempts >= $maxAttempts) {
// resetResponse();
// }

// $tokenPlain = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
// $tokenStored = hash('sha256', $tokenPlain);

// $stmtInvalidate = $conexion->prepare("UPDATE password_resets SET usado = 1 WHERE usuario_id = ? AND usado = 0");
// $stmtInvalidate->bind_param("i", $uid);
// $stmtInvalidate->execute();

// $stmtInsert = $conexion->prepare("INSERT INTO password_resets (usuario_id, token, expira_en, usado) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 60 MINUTE), 0)");
// $stmtInsert->bind_param("is", $uid, $tokenStored);
// $stmtInsert->execute();

// $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
// $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
// $resetLink = $scheme . '://' . $host . rtrim(BASE_URL, '/') . '/views/resetPassword.php?token=' . urlencode($tokenPlain);

// $subject = "Recuperar contraseña - La Comanda";
// $name = trim(($usuario['nombre'] ?? 'usuario'));

// $message = "Hola {$name},\n\n";
// $message .= "Recibimos una solicitud para restablecer tu contraseña.\n";
// $message .= "Usa este enlace (expira en 60 minutos):\n{$resetLink}\n\n";
// $message .= "Si no solicitaste este cambio, puedes ignorar este mensaje.\n";

// try {
// MailtrapMailer::send(
// $email,
// (string)($usuario['nombre'] ?? ''),
// $subject,
// $message,
// '<p>Hola ' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . ',</p>' .
// '<p>Recibimos una solicitud para restablecer tu contraseña.</p>' .
// '<p>Usa este enlace (expira en 60 minutos):<br><a href="' . htmlspecialchars($resetLink, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($resetLink, ENT_QUOTES, 'UTF-8') . '</a></p>' .
// '<p>Si no solicitaste este cambio, puedes ignorar este mensaje.</p>'
// );
// } catch (Throwable $mailError) {
// error_log("Error Mailtrap reset {$email}: " . $mailError->getMessage());
// }

// resetResponse();
// } catch (Throwable $e) {
// error_log("Forgot password error: " . $e->getMessage());

// http_response_code(500);
// echo json_encode([
// "status" => "ERROR",
// "message" => $e->getMessage()
// ]);
// exit;
// }
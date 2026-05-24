<?php
header("Content-Type: application/json; charset=utf-8");
require_once __DIR__ . "/../config/env.php";
app_configure_errors();

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

    $baseUrl = 'https://lacomanda-cafeteriatoscana.up.railway.app';
    
    $appUrl = rtrim((string)app_env('APP_URL', ''), '/');
    if ($appUrl !== '') {
        $resetLink = $appUrl . '/views/resetPassword.php?token=' . urlencode($tokenPlain);
    } else {
        $resetLink = $baseUrl . '/views/resetPassword.php?token=' . urlencode($tokenPlain);
    }

    $subject = "Recuperar contraseña - La Comanda";
    $name = trim(($usuario['nombre'] ?? 'usuario'));

    $message = "Hola {$name},\n\n";
    $message .= "Recibimos una solicitud para restablecer tu contraseña.\n";
    $message .= "Usa este enlace (expira en 60 minutos):\n{$resetLink}\n\n";
    $message .= "Si no solicitaste este cambio, puedes ignorar este mensaje.\n";

    $logoUrl = $baseUrl . '/public/img/logotipo1.PNG';

    $safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
    $safeResetLink = htmlspecialchars($resetLink, ENT_QUOTES, 'UTF-8');
    $safeLogoUrl = htmlspecialchars($logoUrl, ENT_QUOTES, 'UTF-8');

    $htmlMessage = '
        <div style="margin:0;padding:32px 16px;background:#f7f1e8;font-family:Montserrat,Arial,sans-serif;color:#4b3a2f;">
            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:640px;margin:0 auto;border-collapse:collapse;">
                <tr>
                    <td style="padding:0;">
                        <div style="background:#ffffff;border:1px solid #ebe3d7;border-radius:28px;box-shadow:0 18px 45px rgba(75,58,47,.12);overflow:hidden;">
                            <div style="padding:32px 32px 18px;text-align:center;background:linear-gradient(180deg,#ffffff 0%,#fbf7f2 100%);">
                                <img src="' . $safeLogoUrl . '" alt="Cafetería Toscana" style="width:120px;height:120px;border-radius:999px;object-fit:cover;display:block;margin:0 auto 18px;box-shadow:0 10px 24px rgba(0,0,0,.12);">
                                <div style="font-size:28px;font-weight:700;line-height:1.2;color:#4a2c17;margin-bottom:8px;">Recuperar contraseña</div>
                                <div style="font-size:14px;line-height:1.6;color:#7a6a5c;">Sistema La Comanda · Cafetería Toscana</div>
                            </div>

                            <div style="padding:8px 32px 32px;">
                                <p style="margin:0 0 16px;font-size:15px;line-height:1.7;color:#4b3a2f;">Hola <strong>' . $safeName . '</strong>,</p>
                                <p style="margin:0 0 16px;font-size:15px;line-height:1.7;color:#4b3a2f;">Recibimos una solicitud para restablecer tu contraseña. Si fuiste tú, usa el siguiente botón. El enlace expira en <strong>60 minutos</strong>.</p>

                                <div style="text-align:center;margin:28px 0;">
                                    <a href="' . $safeResetLink . '" style="display:inline-block;background:#70A38F;color:#ffffff;text-decoration:none;font-weight:700;font-size:15px;padding:14px 24px;border-radius:14px;box-shadow:0 10px 24px rgba(112,163,143,.28);">Restablecer contraseña</a>
                                </div>

                                <div style="margin:0 0 18px;padding:16px 18px;background:#f8faf9;border:1px solid #d7e7df;border-radius:16px;">
                                    <div style="font-size:12px;font-weight:700;letter-spacing:.04em;text-transform:uppercase;color:#6b7d74;margin-bottom:8px;">Enlace directo</div>
                                    <div style="font-size:13px;line-height:1.7;word-break:break-word;">
                                        <a href="' . $safeResetLink . '" style="color:#70A38F;text-decoration:underline;">' . $safeResetLink . '</a>
                                    </div>
                                </div>

                                <p style="margin:0 0 8px;font-size:14px;line-height:1.7;color:#7a6a5c;">Si no solicitaste este cambio, puedes ignorar este mensaje. Tu contraseña actual seguirá funcionando.</p>
                            </div>

                            <div style="padding:18px 32px;background:#4a2c17;color:#f7f1e8;text-align:center;font-size:12px;line-height:1.6;">
                                La Comanda · Cafetería Toscana<br>
                                Este es un correo automático de recuperación de acceso.
                            </div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>';

    try {
        MailtrapMailer::send(
            $email,
            (string)($usuario['nombre'] ?? ''),
            $subject,
            $message,
            $htmlMessage
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
        "message" => app_is_production() ? 'Error interno del servidor' : $e->getMessage()
    ]);
    exit;
}

<?php
ini_set('display_errors', 0);
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . "/../../middleware/auth.php";
require_once __DIR__ . "/../../middleware/roles.php";
require_once __DIR__ . "/../../config/rutas.php";
require_once __DIR__ . "/../../model/Usuarios.php";
require_once __DIR__ . "/../../model/Conexion.php";
require_once __DIR__ . "/../../model/MailtrapMailer.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    verificarRol([1]); // Solo admins

    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        throw new Exception("Method not allowed");
    }

    $adminId = $_SESSION["usuario_id"] ?? 0;
    if ($adminId <= 0) {
        throw new Exception("Sesión inválida");
    }

    $admin = Usuarios::obtenerPorId($adminId);
    if (!$admin) {
        throw new Exception("Admin no encontrado");
    }

    $email = $admin["email"];
    $name = $admin["nombre"];

    $conexion = Conexion::conectar();

    // Invalidate previous OTPs for this user
    $stmtInvalidate = $conexion->prepare("UPDATE password_resets SET usado = 1 WHERE usuario_id = ? AND usado = 0");
    $stmtInvalidate->bind_param("i", $adminId);
    $stmtInvalidate->execute();

    // Generate 6 digit OTP
    $otpCode = sprintf("%06d", random_int(100000, 999999));
    
    // Hash the OTP using SHA-256 (same as forgot password)
    $tokenStored = hash('sha256', $otpCode);

    // Insert new OTP with 30 min expiration
    $stmtInsert = $conexion->prepare("INSERT INTO password_resets (usuario_id, token, expira_en, usado) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 30 MINUTE), 0)");
    $stmtInsert->bind_param("is", $adminId, $tokenStored);
    $stmtInsert->execute();

    // Prepare Email
    $subject = "Código de Seguridad - La Comanda";
    
    $message = "Hola {$name},\n\n";
    $message .= "Tu código de seguridad para confirmar cambios es: {$otpCode}\n";
    $message .= "Este código expirará en 30 minutos.\n\n";
    $message .= "Si no solicitaste este código, ignora este mensaje.\n";

    // Obtener la URL base desde la ruta o el env
    $appUrl = getenv('APP_URL');
    if (empty($appUrl) || strpos($appUrl, 'localhost') !== false || strpos($appUrl, '127.0.0.1') !== false) {
        // En localhost Mailtrap no puede descargar la imagen local, usamos un logo genérico
        $logoUrl = 'https://ui-avatars.com/api/?name=La+Comanda&background=70A38F&color=fff&size=120&font-size=0.33';
    } else {
        $appUrl = rtrim($appUrl, '/');
        $logoUrl = $appUrl . '/public/img/logotipo1.PNG';
    }

    $safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
    $safeLogoUrl = htmlspecialchars($logoUrl, ENT_QUOTES, 'UTF-8');
    
    $htmlMessage = '
        <div style="margin:0;padding:32px 16px;background:#f7f1e8;font-family:Montserrat,Arial,sans-serif;color:#4b3a2f;">
            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:640px;margin:0 auto;border-collapse:collapse;">
                <tr>
                    <td style="padding:0;">
                        <div style="background:#ffffff;border:1px solid #ebe3d7;border-radius:28px;box-shadow:0 18px 45px rgba(75,58,47,.12);overflow:hidden;">
                            <div style="padding:32px 32px 18px;text-align:center;background:linear-gradient(180deg,#ffffff 0%,#fbf7f2 100%);">
                                <img src="' . $safeLogoUrl . '" alt="Cafetería Toscana" style="width:120px;height:120px;border-radius:999px;object-fit:cover;display:block;margin:0 auto 18px;box-shadow:0 10px 24px rgba(0,0,0,.12);">
                                <div style="font-size:28px;font-weight:700;line-height:1.2;color:#4a2c17;margin-bottom:8px;">Código de Seguridad</div>
                                <div style="font-size:14px;line-height:1.6;color:#7a6a5c;">Sistema La Comanda · Cafetería Toscana</div>
                            </div>
                            <div style="padding:8px 32px 32px;">
                                <p style="margin:0 0 16px;font-size:15px;line-height:1.7;color:#4b3a2f;">Hola <strong>' . $safeName . '</strong>,</p>
                                <p style="margin:0 0 16px;font-size:15px;line-height:1.7;color:#4b3a2f;">Has solicitado realizar un cambio sensible en el sistema. Utiliza el siguiente código para autorizar la acción:</p>
                                
                                <div style="text-align:center;margin:28px 0;padding:24px;background:#f8faf9;border:2px dashed #70A38F;border-radius:16px;">
                                    <div style="font-size:36px;font-weight:800;letter-spacing:6px;color:#70A38F;font-family:monospace;">' . $otpCode . '</div>
                                </div>

                                <p style="margin:0 0 8px;font-size:14px;line-height:1.7;color:#7a6a5c;">Este código expirará en <strong>30 minutos</strong>.</p>
                            </div>
                            <div style="padding:18px 32px;background:#4a2c17;color:#f7f1e8;text-align:center;font-size:12px;line-height:1.6;">
                                La Comanda · Cafetería Toscana
                            </div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>';

    // Send Email
    try {
        MailtrapMailer::send(
            $email,
            $name,
            $subject,
            $message,
            $htmlMessage
        );
        echo json_encode(["success" => true, "message" => "Código enviado"]);
    } catch (Throwable $mailError) {
        error_log("Error enviando OTP a {$email}: " . $mailError->getMessage());
        echo json_encode(["success" => false, "message" => "Error al enviar el correo. Verifique la configuración."]);
    }

} catch (Throwable $e) {
    error_log("OTP Request Error: " . $e->getMessage());
    echo json_encode(["success" => false, "message" => "Error interno: " . $e->getMessage()]);
}

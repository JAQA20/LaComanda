<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

require_once __DIR__ . "/../model/Conexion.php";

function validatePasswordStrength($password)
{
    $errors = [];
    if (strlen($password) < 8) $errors[] = "mínimo 8 caracteres";
    if (!preg_match('/[a-z]/', $password)) $errors[] = "una letra minúscula";
    if (!preg_match('/[A-Z]/', $password)) $errors[] = "una letra mayúscula";
    if (!preg_match('/[0-9]/', $password)) $errors[] = "un número";
    if (!preg_match('/[!@#$%^&*()_+\-=\[\]{};:"<>,.?\/]/', $password)) $errors[] = "un carácter especial";
    return $errors;
}

$token = trim($_GET['token'] ?? $_POST['token'] ?? '');
$tokenHash = $token !== '' ? hash('sha256', $token) : '';
$error = null;
$success = false;
$tokenValid = false;
$usuarioId = null;

if ($tokenHash !== '') {
    $stmt = $conexion->prepare("SELECT usuario_id FROM password_resets WHERE token = ? AND usado = 0 AND expira_en > NOW() LIMIT 1");
    if ($stmt) {
        $stmt->bind_param("s", $tokenHash);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res ? $res->fetch_assoc() : null;
        if ($row) {
            $tokenValid = true;
            $usuarioId = (int)$row['usuario_id'];
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newPassword = (string)($_POST['new_password'] ?? '');
    $confirmPassword = (string)($_POST['confirm_password'] ?? '');

    if (!$tokenValid || $usuarioId === null) {
        $error = "El enlace de recuperación es inválido o expiró.";
    } elseif ($newPassword !== $confirmPassword) {
        $error = "Las contraseñas no coinciden.";
    } else {
        $passwordErrors = validatePasswordStrength($newPassword);
        if (!empty($passwordErrors)) {
            $error = "La contraseña debe contener: " . implode(', ', $passwordErrors) . ".";
        } else {
            try {
                $conexion->begin_transaction();

                $stmtToken = $conexion->prepare("SELECT id, usuario_id FROM password_resets WHERE token = ? AND usado = 0 AND expira_en > NOW() LIMIT 1 FOR UPDATE");
                if (!$stmtToken) {
                    throw new Exception("No se pudo validar token");
                }
                $stmtToken->bind_param("s", $tokenHash);
                $stmtToken->execute();
                $resToken = $stmtToken->get_result();
                $tokenRow = $resToken ? $resToken->fetch_assoc() : null;

                if (!$tokenRow) {
                    throw new Exception("El enlace de recuperación es inválido o expiró.");
                }

                $uid = (int)$tokenRow['usuario_id'];
                $passwordHash = password_hash($newPassword, PASSWORD_BCRYPT);

                $stmtUser = $conexion->prepare("UPDATE usuarios SET password = ? WHERE id = ? LIMIT 1");
                if (!$stmtUser) {
                    throw new Exception("No se pudo actualizar la contraseña");
                }
                $stmtUser->bind_param("si", $passwordHash, $uid);
                $stmtUser->execute();

                if ($stmtUser->affected_rows < 0) {
                    throw new Exception("No se pudo actualizar la contraseña");
                }

                $stmtUse = $conexion->prepare("UPDATE password_resets SET usado = 1 WHERE usuario_id = ? AND usado = 0");
                if ($stmtUse) {
                    $stmtUse->bind_param("i", $uid);
                    $stmtUse->execute();
                }

                $conexion->commit();
                $success = true;
                $tokenValid = false;
            } catch (Throwable $e) {
                $conexion->rollback();
                $error = $e->getMessage() === "El enlace de recuperación es inválido o expiró."
                    ? $e->getMessage()
                    : "No fue posible actualizar tu contraseña. Intenta nuevamente.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña - La Comanda</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
        }

        .brand-green {
            background-color: #70A38F;
        }

        .brand-brown {
            color: #362018;
        }

        .brand-beige {
            background-color: #F5EDE1;
        }
    </style>
</head>

<body class="brand-beige min-h-screen flex items-center justify-center px-4">
    <div class="w-full max-w-md bg-white rounded-3xl shadow-2xl p-8 border border-gray-100">
        <div class="text-center mb-8">
            <h2 class="text-2xl font-bold brand-brown mb-2"><?php echo $success ? 'Contraseña actualizada' : 'Restablecer contraseña'; ?></h2>
            <p class="text-gray-600 text-sm"><?php echo $success ? 'Ya puedes iniciar sesión con tu nueva contraseña.' : 'Ingresa una contraseña segura.'; ?></p>
        </div>

        <?php if ($success): ?>
            <a href="./login.php" class="w-full flex justify-center py-3 px-4 rounded-xl text-sm font-medium text-white brand-green">Ir al login</a>
        <?php elseif (!$tokenValid): ?>
            <div class="mb-6 p-4 bg-red-100 border border-red-300 rounded-lg text-red-700 text-sm">El enlace de recuperación es inválido o expiró.</div>
            <a href="./forgotPassword.php" class="w-full flex justify-center py-3 px-4 rounded-xl text-sm font-medium text-white brand-green">Solicitar nuevo enlace</a>
        <?php else: ?>
            <form method="POST" class="space-y-6" id="reset-form">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token, ENT_QUOTES, 'UTF-8'); ?>">

                <?php if ($error): ?>
                    <div class="p-4 bg-red-100 border border-red-300 rounded-lg text-red-700 text-sm"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endif; ?>

                <div>
                    <label for="new_password" class="block text-sm font-medium brand-brown mb-2">Nueva contraseña</label>
                    <input type="password" id="new_password" name="new_password" required class="block w-full px-3 py-3 border border-gray-300 rounded-xl" placeholder="Mínimo 8 caracteres">
                    <div class="mt-3 p-3 bg-gray-50 border border-gray-200 rounded-lg text-xs text-gray-700">
                        <p class="font-semibold mb-2">Requisitos de seguridad:</p>
                        <ul class="space-y-1">
                            <li id="req-length">✗ Mínimo 8 caracteres</li>
                            <li id="req-lower">✗ Al menos una minúscula (a-z)</li>
                            <li id="req-upper">✗ Al menos una mayúscula (A-Z)</li>
                            <li id="req-number">✗ Al menos un número (0-9)</li>
                            <li id="req-special">✗ Al menos un símbolo (!@#$%^&*)</li>
                        </ul>
                    </div>
                </div>

                <div>
                    <label for="confirm_password" class="block text-sm font-medium brand-brown mb-2">Confirmar contraseña</label>
                    <input type="password" id="confirm_password" name="confirm_password" required class="block w-full px-3 py-3 border border-gray-300 rounded-xl" placeholder="Repite tu contraseña">
                    <p id="match-feedback" class="mt-2 text-xs text-gray-600">Escribe ambas contraseñas para validar coincidencia.</p>
                </div>

                <button id="submit-btn" type="submit" disabled class="w-full flex justify-center py-3 px-4 rounded-xl text-sm font-medium text-white brand-green disabled:opacity-50 disabled:cursor-not-allowed">Actualizar contraseña</button>
                <div class="text-center text-sm">
                    <a href="./login.php" class="text-green-600 hover:underline font-medium">Volver al login</a>
                </div>
            </form>
        <?php endif; ?>
    </div>

    <script>
        const form = document.getElementById('reset-form');
        const passInput = document.getElementById('new_password');
        const confirmInput = document.getElementById('confirm_password');
        const matchFeedback = document.getElementById('match-feedback');
        const submitBtn = document.getElementById('submit-btn');

        function setReqState(id, valid, text) {
            const el = document.getElementById(id);
            if (!el) return;
            el.textContent = (valid ? '✓ ' : '✗ ') + text;
            el.style.color = valid ? '#16a34a' : '#dc2626';
            el.style.fontWeight = valid ? '600' : '400';
        }

        function validatePassword(pass) {
            return {
                length: pass.length >= 8,
                lower: /[a-z]/.test(pass),
                upper: /[A-Z]/.test(pass),
                number: /[0-9]/.test(pass),
                special: /[!@#$%^&*()_+\-=\[\]{};:"<>,.?\/]/.test(pass)
            };
        }

        function refreshValidation() {
            if (!passInput || !confirmInput) return;

            const pass = passInput.value;
            const pass2 = confirmInput.value;
            const rules = validatePassword(pass);

            setReqState('req-length', rules.length, 'Mínimo 8 caracteres');
            setReqState('req-lower', rules.lower, 'Al menos una minúscula (a-z)');
            setReqState('req-upper', rules.upper, 'Al menos una mayúscula (A-Z)');
            setReqState('req-number', rules.number, 'Al menos un número (0-9)');
            setReqState('req-special', rules.special, 'Al menos un símbolo (!@#$%^&*)');

            const strong = rules.length && rules.lower && rules.upper && rules.number && rules.special;
            const matches = pass !== '' && pass === pass2;

            if (pass2 === '') {
                matchFeedback.textContent = 'Escribe ambas contraseñas para validar coincidencia.';
                matchFeedback.style.color = '#4b5563';
            } else if (matches) {
                matchFeedback.textContent = '✓ Las contraseñas coinciden.';
                matchFeedback.style.color = '#16a34a';
                matchFeedback.style.fontWeight = '600';
            } else {
                matchFeedback.textContent = '✗ Las contraseñas no coinciden.';
                matchFeedback.style.color = '#dc2626';
                matchFeedback.style.fontWeight = '600';
            }

            if (submitBtn) {
                submitBtn.disabled = !(strong && matches);
            }
        }

        passInput?.addEventListener('input', refreshValidation);
        confirmInput?.addEventListener('input', refreshValidation);
        refreshValidation();

        form?.addEventListener('submit', function(e) {
            const pass = passInput.value;
            const pass2 = confirmInput.value;
            const rules = validatePassword(pass);
            const strong = rules.length && rules.lower && rules.upper && rules.number && rules.special;

            if (!strong || pass !== pass2) {
                e.preventDefault();
                refreshValidation();
            }
        });
    </script>
</body>

</html>
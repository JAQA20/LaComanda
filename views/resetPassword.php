<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

require_once __DIR__ . "/../model/Conexion.php";

$token = isset($_GET['token']) ? trim($_GET['token']) : (isset($_POST['token']) ? trim($_POST['token']) : null);
$email = isset($_GET['email']) ? trim($_GET['email']) : (isset($_POST['email']) ? trim($_POST['email']) : null);
$error = null;
$success = false;

// Función para validar fortaleza de contraseña
function validatePasswordStrength($password)
{
    $errors = [];
    if (strlen($password) < 8) $errors[] = "mínimo 8 caracteres";
    if (!preg_match('/[a-z]/', $password)) $errors[] = "una letra minúscula";
    if (!preg_match('/[A-Z]/', $password)) $errors[] = "una letra mayúscula";
    if (!preg_match('/[0-9]/', $password)) $errors[] = "un número";
    if (!preg_match('/[!@#$%^&*()_+\-=\[\]{};:"<>,.?\/]/', $password)) $errors[] = "un carácter especial (!@#$%^&* etc)";
    return $errors;
}

// Validar token si viene con POST (submit del formulario)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_password'])) {
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (!$token || !$email) {
        $error = "Token o email no válidos";
    } elseif ($new_password !== $confirm_password) {
        $error = "Las contraseñas no coinciden";
    } else {
        // Validar fortaleza de contraseña
        $passErrors = validatePasswordStrength($new_password);
        if (!empty($passErrors)) {
            $error = "La contraseña debe contener: " . implode(", ", $passErrors);
        } else {
            try {
                // Validar que el token existe y no ha expirado
                $stmt = $conexion->prepare("
                    SELECT usuario_id FROM password_resets 
                    WHERE token = ? AND expira_en > NOW() AND usado = 0 LIMIT 1
                ");
                if (!$stmt) {
                    throw new Exception("Error prepare: " . $conexion->error);
                }

                $stmt->bind_param("s", $token);
                if (!$stmt->execute()) {
                    throw new Exception("Error execute: " . $stmt->error);
                }

                $result = $stmt->get_result();
                $reset = $result->fetch_assoc();

                if (!$reset) {
                    $error = "El enlace de recuperación ha expirado o no es válido";
                } else {
                    // Verificar que el usuario existe y está activo
                    $user_stmt = $conexion->prepare("
                        SELECT id, email, activo FROM usuarios 
                        WHERE id = ? AND activo = 1 LIMIT 1
                    ");
                    if (!$user_stmt) {
                        throw new Exception("Error prepare user: " . $conexion->error);
                    }

                    $user_stmt->bind_param("i", $reset['usuario_id']);
                    if (!$user_stmt->execute()) {
                        throw new Exception("Error execute user: " . $user_stmt->error);
                    }

                    $user_result = $user_stmt->get_result();
                    $user = $user_result->fetch_assoc();

                    if (!$user) {
                        $error = "El usuario no existe o ha sido desactivado";
                    } elseif (strtolower(trim($user['email'])) !== strtolower(trim($email))) {
                        // Validar que el email coincida con el usuario
                        $error = "El email no coincide con el usuario registrado";
                    } else {
                        // Actualizar contraseña y marcar token como usado
                        $update_success = false;
                        $token_marked = false;

                        // Actualizar contraseña con hash
                        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

                        $upd = $conexion->prepare("
                            UPDATE usuarios SET password = ? WHERE id = ?
                        ");
                        if (!$upd) {
                            throw new Exception("Error prepare update: " . $conexion->error);
                        }

                        $upd->bind_param("si", $hashed_password, $reset['usuario_id']);
                        if (!$upd->execute()) {
                            throw new Exception("Error execute update: " . $upd->error);
                        }

                        // Marcar token como usado
                        $token_update = $conexion->prepare("UPDATE password_resets SET usado = 1 WHERE token = ?");
                        if ($token_update) {
                            $token_update->bind_param("s", $token);
                            $token_update->execute();
                        }

                        $update_success = true;
                        $token_marked = true;

                        if ($update_success) {
                            $success = true;
                        }
                    }
                }
            } catch (Exception $e) {
                $error = "Error al actualizar la contraseña: " . $e->getMessage();
            }
        }
    }
}

// Validar token GET (vista inicial)
$token_valid = false;
if ($token && $email && !$success) {
    try {
        $stmt = $conexion->prepare("
            SELECT usuario_id FROM password_resets 
            WHERE token = ? AND expira_en > NOW() AND usado = 0 LIMIT 1
        ");
        if ($stmt) {
            $stmt->bind_param("s", $token);
            $stmt->execute();
            $result = $stmt->get_result();
            $reset = $result->fetch_assoc();
            $token_valid = ($reset !== null);
        }
    } catch (Exception $e) {
        $token_valid = false;
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - La Comanda</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
        }

        .coffee-pattern {
            background-image: repeating-linear-gradient(45deg, transparent, transparent 10px, rgba(180, 140, 100, 0.05) 10px, rgba(180, 140, 100, 0.05) 20px);
        }

        .brand-brown {
            color: #362018;
        }

        .brand-green {
            background-color: #70A38F;
        }

        .brand-beige {
            background-color: #F5EDE1;
        }
    </style>
</head>

<body class="bg-brand-beige min-h-screen">

    <main id="reset-main" class="flex items-center justify-center min-h-[calc(100vh-80px)] px-4 coffee-pattern">
        <div id="reset-container" class="w-full max-w-md">

            <!-- Reset Card -->
            <div id="reset-card" class="bg-white rounded-3xl shadow-2xl p-8 border border-gray-100">

                <!-- Card Header -->
                <div id="card-header" class="text-center mb-8">
                    <div class="w-40 h-40 rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg">
                        <img class="h-40 w-40 rounded-full flex items-center" src="/LaComanda-main/public/img/logotipo1.PNG" alt="Logo" />
                    </div>
                    <h2 class="text-2xl font-bold brand-brown mb-2">
                        <?php echo $success ? "Contraseña actualizada" : "Recuperar Contraseña"; ?>
                    </h2>
                    <p class="text-gray-600 text-sm">
                        <?php echo $success ? "Tu contraseña ha sido cambiada exitosamente" : "Ingresa tu nueva contraseña"; ?>
                    </p>
                </div>

                <?php if ($success): ?>

                    <!-- Success Message -->
                    <div class="mb-6 p-4 bg-green-100 border border-green-400 rounded-lg">
                        <p class="text-green-700 text-sm font-medium">✓ Contraseña actualizada correctamente</p>
                    </div>

                    <a href="./login.php"
                        class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-sm text-sm font-medium text-white brand-green focus:outline-none transition-all duration-200">
                        Ir al login
                    </a>

                <?php elseif (!$token_valid && $token): ?>

                    <!-- Token Invalid -->
                    <div class="mb-6 p-4 bg-red-100 border border-red-400 rounded-lg">
                        <p class="text-red-700 text-sm font-medium">✗ El enlace ha expirado. Solicita uno nuevo.</p>
                    </div>

                    <a href="/LaComanda-main/views/login.php"
                        class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-sm text-sm font-medium text-white brand-green focus:outline-none transition-all duration-200">
                        Volver al login
                    </a>

                <?php else: ?>

                    <!-- Reset Form -->
                    <form id="reset-form" class="space-y-6" method="POST">

                        <!-- Hidden fields para token y email -->
                        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                        <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">

                        <?php if ($error): ?>
                            <div class="p-4 bg-red-100 border border-red-400 rounded-lg">
                                <p class="text-red-700 text-sm font-medium"><?php echo htmlspecialchars($error); ?></p>
                            </div>
                        <?php endif; ?>

                        <!-- New Password Field -->
                        <div id="password-field" class="space-y-2">
                            <label for="new_password" class="block text-sm font-medium brand-brown">
                                Nueva Contraseña
                            </label>
                            <div class="relative">
                                <input
                                    type="password"
                                    id="new_password"
                                    name="new_password"
                                    class="block w-full px-3 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors duration-200 bg-gray-50 focus:bg-white"
                                    placeholder="Mínimo 6 caracteres"
                                    required>
                            </div>
                            <div class="text-xs text-gray-600 mt-2 p-2 bg-gray-50 rounded border border-gray-200">
                                <p class="font-bold mb-2 text-gray-700">Requisitos:</p>
                                <ul class="space-y-1">
                                    <li id="req-length">✗ Mínimo 8 caracteres</li>
                                    <li id="req-lower">✗ Una letra minúscula (a-z)</li>
                                    <li id="req-upper">✗ Una letra mayúscula (A-Z)</li>
                                    <li id="req-number">✗ Un número (0-9)</li>
                                    <li id="req-special">✗ Un símbolo especial (!@#$%^&*)</li>
                                </ul>
                            </div>
                        </div>

                        <!-- Confirm Password Field -->
                        <div id="confirm-password-field" class="space-y-2">
                            <label for="confirm_password" class="block text-sm font-medium brand-brown">
                                Confirmar Contraseña
                            </label>
                            <div class="relative">
                                <input
                                    type="password"
                                    id="confirm_password"
                                    name="confirm_password"
                                    class="block w-full px-3 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors duration-200 bg-gray-50 focus:bg-white"
                                    placeholder="Repite tu contraseña"
                                    required>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div id="submit-button-section">
                            <button
                                type="submit"
                                class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-sm text-sm font-medium text-white brand-green focus:outline-none focus:ring-2 focus:ring-offset-2 transition-all duration-200 transform hover:scale-105">
                                Actualizar Contraseña
                            </button>
                        </div>

                        <div class="text-center text-sm">
                            <a href="/LaComanda-main/views/login.php" class="text-green-600 hover:underline font-medium">
                                Volver al login
                            </a>
                        </div>
                    </form>

                <?php endif; ?>

            </div>
        </div>
    </main>

    <script>
        const passwordInput = document.getElementById('new_password');
        const confirmInput = document.getElementById('confirm_password');

        if (passwordInput) {
            passwordInput.addEventListener('input', function() {
                const pass = this.value;

                // Validaciones en tiempo real
                const reqLength = pass.length >= 8;
                const reqLower = /[a-z]/.test(pass);
                const reqUpper = /[A-Z]/.test(pass);
                const reqNumber = /[0-9]/.test(pass);
                const reqSpecial = /[!@#$%^&*()_+\-=\[\]{};:"<>,.?\/]/.test(pass);

                document.getElementById('req-length').textContent =
                    (reqLength ? '✓' : '✗') + ' Mínimo 8 caracteres';
                document.getElementById('req-lower').textContent =
                    (reqLower ? '✓' : '✗') + ' Una letra minúscula (a-z)';
                document.getElementById('req-upper').textContent =
                    (reqUpper ? '✓' : '✗') + ' Una letra mayúscula (A-Z)';
                document.getElementById('req-number').textContent =
                    (reqNumber ? '✓' : '✗') + ' Un número (0-9)';
                document.getElementById('req-special').textContent =
                    (reqSpecial ? '✓' : '✗') + ' Un símbolo especial (!@#$%^&*)';

                // Colorear los requisitos
                document.querySelectorAll('[id^="req-"]').forEach(el => {
                    el.style.color = el.textContent.startsWith('✓') ? '#10b981' : '#ef4444';
                    el.style.fontWeight = el.textContent.startsWith('✓') ? '600' : '400';
                });
            });
        }

        document.getElementById('reset-form')?.addEventListener('submit', function(e) {
            const newPass = document.getElementById('new_password').value;
            const confirmPass = document.getElementById('confirm_password').value;

            if (newPass.length < 8) {
                e.preventDefault();
                alert('La contraseña debe tener mínimo 8 caracteres');
                return;
            }

            if (!/[a-z]/.test(newPass) || !/[A-Z]/.test(newPass) || !/[0-9]/.test(newPass) || !/[!@#$%^&*()_+\-=\[\]{};:"<>,.?\/]/.test(newPass)) {
                e.preventDefault();
                alert('La contraseña no cumple con todos los requisitos');
                return;
            }

            if (newPass !== confirmPass) {
                e.preventDefault();
                alert('Las contraseñas no coinciden');
                return;
            }
        });
    </script>

</body>

</html>
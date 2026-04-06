<?php
require_once __DIR__ . "/../model/ResetPassword.php";
require_once __DIR__ . "/../config/rutas.php";

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
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/resetPassword.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/style.css">

</head>

<body class="bg-brand-beige min-h-screen">

    <main id="reset-main" class="flex items-center justify-center min-h-[calc(100vh-80px)] px-4 coffee-pattern">
        <div id="reset-container" class="w-full max-w-md">

            <!-- Reset Card -->
            <div id="reset-card" class="bg-white rounded-3xl shadow-2xl p-8 border border-gray-100">

                <!-- Card Header -->
                <div id="card-header" class="text-center mb-8">
                    <div class="w-40 h-40 rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg">
                        <img class="h-40 w-40 rounded-full flex items-center" src="<?= BASE_URL ?>public/img/logotipo1.PNG" alt="Logo" />
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

                    <a href="<?= BASE_URL ?>views/login.php"
                        class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-sm text-sm font-medium text-white brand-green focus:outline-none transition-all duration-200">
                        Ir al login
                    </a>

                <?php elseif (!$tokenValid && $token): ?>

                    <!-- Token Invalid -->
                    <div class="mb-6 p-4 bg-red-100 border border-red-400 rounded-lg">
                        <p class="text-red-700 text-sm font-medium">✗ El enlace ha expirado. Solicita uno nuevo.</p>
                    </div>

                    <a href="<?= BASE_URL ?>views/login.php"
                        class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-sm text-sm font-medium text-white brand-green focus:outline-none transition-all duration-200">
                        Volver al login
                    </a>

                <?php else: ?>

                    <!-- Reset Form -->
                    <form id="reset-form" class="space-y-6" method="POST">

                        <!-- Hidden field para token -->
                        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

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

                        <!-- Submit Button -->
                        <div id="submit-button-section">
                            <button
                                type="submit"
                                class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-sm text-sm font-medium text-white brand-green focus:outline-none focus:ring-2 focus:ring-offset-2 transition-all duration-200 transform hover:scale-105">
                                Actualizar Contraseña
                            </button>
                        </div>

                        <div class="text-center text-sm">
                            <a href="<?= BASE_URL ?>views/login.php" class="text-green-600 hover:underline font-medium">
                                Volver al login
                            </a>
                        </div>
                    </form>

                <?php endif; ?>

            </div>
        </div>
    </main>

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
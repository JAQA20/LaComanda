<?php
require_once __DIR__ . "/../../config/rutas.php";
require_once __DIR__ . "/../../middleware/auth.php";
require_once __DIR__ . "/../../middleware/roles.php";
require_once __DIR__ . "/../../model/Usuarios.php";

verificarRol([1]); // solo Admin
// if (session_status() === PHP_SESSION_NONE) {
//     session_start();
// }

$errors = [];
$roles = [];
$usuario = null;

$id = isset($_GET["id"]) ? (int)$_GET["id"] : 0;
if ($id <= 0) {
    header("Location: " . BASE_URL . "/views/admin/usuarios.php");
    exit;
}

// Cargar roles y usuario
try {
    $roles = Usuarios::listarRoles();
    $usuario = Usuarios::obtenerPorId($id);
    if (!$usuario) {
        header("Location: " . BASE_URL . "/views/admin/usuarios.php");
        exit;
    }
} catch (Throwable $e) {
    $errors[] = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Editar usuario | La Comanda</title>

    <!-- Tailwind (navbar) -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- FontAwesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <!-- Fuente -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Estilos La Comanda -->
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/admin-editar-usuario.css">
</head>

<body class="custom-beige min-h-screen">

    <?php require_once __DIR__ . "/adminNavbar.php"; ?>

    <main class="container-fluid pt-5 mt-4">
        <div class="container-xxl py-4">

            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
                <div>
                    <h1 class="h3 mb-1 text-brown fw-bold">Editar usuario</h1>
                    <div class="text-muted small">Actualiza los datos del usuario</div>
                </div>

                <a href="<?= BASE_URL ?>views/admin/usuarios.php" class="btn btn-outline-brown px-3 py-2">
                    <i class="fa-solid fa-arrow-left me-2"></i> Volver
                </a>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <div class="fw-semibold mb-1">Revisa lo siguiente:</div>
                    <ul class="mb-0">
                        <?php foreach ($errors as $err): ?>
                            <li><?= htmlspecialchars($err) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="card card-comanda shadow-sm">
                <div class="card-body p-4 p-md-5">

                    <form method="POST" action="<?= BASE_URL ?>public/api/editarUsuario.php" class="row g-3" novalidate>
                        <input type="hidden" name="id" value="<?= (int)($usuario["id"] ?? 0) ?>">

                        <div class="col-md-6">
                            <label class="form-label label-comanda">Nombre</label>
                            <input type="text" name="nombre" class="form-control form-comanda"
                                value="<?= htmlspecialchars($usuario["nombre"] ?? "") ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label label-comanda">Apellido</label>
                            <input type="text" name="apellido" class="form-control form-comanda"
                                value="<?= htmlspecialchars($usuario["apellido"] ?? "") ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label label-comanda">Email</label>
                            <input type="email" name="email" class="form-control form-comanda"
                                value="<?= htmlspecialchars($usuario["email"] ?? "") ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label label-comanda">Rol</label>
                            <select name="rol_id" class="form-select form-comanda" required>
                                <option value="">Seleccione un rol</option>
                                <?php foreach ($roles as $r): ?>
                                    <option value="<?= (int)$r["id"] ?>"
                                        <?= ((int)($usuario["rol_id"] ?? 0) === (int)$r["id"]) ? "selected" : "" ?>>
                                        <?= htmlspecialchars($r["nombre"]) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-12">
                            <div class="alert alert-light border">
                                <div class="fw-semibold mb-1">Cambiar contraseña (opcional)</div>
                                <div class="text-muted small">Si no deseas cambiarla, deja ambos campos vacíos.</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label label-comanda">Nueva contraseña</label>
                            <div class="input-group">
                                <input type="password" name="password" id="password" class="form-control form-comanda">
                                <button class="btn btn-eye" type="button" id="togglePassword">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </div>
                            <div class="form-text">Mínimo 8 caracteres.</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label label-comanda">Confirmar nueva contraseña</label>
                            <input type="password" name="password2" class="form-control form-comanda">
                            <!-- <button class="btn btn-eye" type="button" id="togglePassword">
                                <i class="fa-solid fa-eye"></i>
                            </button> -->
                        </div>

                        <div class="col-12 d-flex justify-content-end gap-2 mt-2">
                            <a href="<?= BASE_URL ?>views/admin/usuarios.php" class="btn btn-outline-brown px-4 py-2">
                                Cancelar
                            </a>
                            <button type="submit" class="btn btn-mint px-4 py-2">
                                <i class="fa-solid fa-floppy-disk me-2"></i> Guardar cambios
                            </button>
                        </div>

                    </form>

                </div>
            </div>

        </div>
    </main>

    <?php require_once __DIR__ . "/../layout/footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= BASE_URL ?>public/js/togglePassword.js"></script>

</body>

</html>
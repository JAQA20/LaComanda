<?php
// Solo Admin
require_once __DIR__ . "/../../middleware/auth.php";
require_once __DIR__ . "/../../middleware/roles.php";
verificarRol([1]);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Nuevo usuario | La Comanda</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <!-- Font -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600;700&display=swap" rel="stylesheet">

    <!-- ✅ SIEMPRE ABSOLUTO -->
    <link rel="stylesheet" href="/LaComanda-main/public/css/admin-nuevo-usuario.css">
</head>

<body class="bg-comanda">

    <?php include __DIR__ . "/adminNavbar.php"; ?>

    <main class="container-fluid pt-5 mt-4">
        <div class="container-xxl py-4">

            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
                <div>
                    <h1 class="h3 mb-1 text-brown fw-bold">Agregar nuevo usuario</h1>
                    <div class="text-muted small">Solo administradores pueden registrar usuarios</div>
                </div>

                <a href="/LaComanda-main/views/admin/usuarios.php" class="btn btn-outline-brown px-3 py-2">
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

                    <!-- ✅ POST al controller -->
                    <form method="POST" action="/LaComanda-main/controller/nuevoUsuarioController.php" class="row g-3" novalidate>

                        <div class="col-md-6">
                            <label class="form-label label-comanda">Nombre</label>
                            <input type="text" name="nombre" class="form-control form-comanda"
                                value="<?= htmlspecialchars($old["nombre"] ?? "") ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label label-comanda">Apellido</label>
                            <input type="text" name="apellido" class="form-control form-comanda"
                                value="<?= htmlspecialchars($old["apellido"] ?? "") ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label label-comanda">Email</label>
                            <input type="email" name="email" class="form-control form-comanda"
                                value="<?= htmlspecialchars($old["email"] ?? "") ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label label-comanda">Rol</label>
                            <select name="rol_id" class="form-select form-comanda" required>
                                <option value="">Seleccione un rol</option>

                                <?php if (!empty($roles)): ?>
                                    <?php foreach ($roles as $r): ?>
                                        <option value="<?= (int)$r["id"] ?>"
                                            <?= ((string)($old["rol_id"] ?? "") === (string)$r["id"]) ? "selected" : "" ?>>
                                            <?= htmlspecialchars($r["nombre"]) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>

                            <?php if (empty($roles)): ?>
                                <div class="form-text text-danger">No se cargaron roles.</div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label label-comanda">Contraseña</label>
                            <div class="input-group">
                                <input type="password" name="password" id="password" class="form-control form-comanda" required>
                                <button class="btn btn-eye" type="button" id="togglePassword">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </div>
                            <div class="form-text">Mínimo 6 caracteres.</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label label-comanda">Confirmar contraseña</label>
                            <input type="password" name="password2" class="form-control form-comanda" required>
                        </div>

                        <div class="col-12 d-flex justify-content-end gap-2 mt-2">
                            <a href="/LaComanda-main/views/admin/usuarios.php" class="btn btn-outline-brown px-4 py-2">
                                Cancelar
                            </a>
                            <button type="submit" class="btn btn-mint px-4 py-2">
                                <i class="fa-solid fa-floppy-disk me-2"></i> Guardar usuario
                            </button>
                        </div>

                    </form>

                </div>
            </div>

        </div>
    </main>

    <?php include __DIR__ . "/../layout/footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>


    <script src="/LaComanda-main/public/js/togglePassword.js"></script>
</body>

</html>
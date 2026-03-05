<?php
require_once __DIR__ . "/../../middleware/auth.php";
require_once __DIR__ . "/../../middleware/roles.php";
verificarRol([1]);

require_once __DIR__ . "/../../model/Usuarios.php";

$usuarios = Usuarios::listar();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuarios | La Comanda</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- DataTables (Bootstrap 5) -->
    <link href="https://cdn.datatables.net/2.1.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">

    <!-- FontAwesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <!-- Estilos propios -->
    <link rel="stylesheet" href="/LaComanda-main/public/css/admin-usuarios.css">
</head>

<body class="bg-comanda">

    <?php include __DIR__ . "/adminNavbar.php"; ?>

    <main class="container-fluid pt-5 mt-4">
        <div class="container-xxl py-4">

            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
                <?php if (isset($_GET["deleted"])): ?>
                    <div class="alert alert-success mb-3">
                        <i class="fa-solid fa-circle-check me-2"></i>
                        Usuario eliminado correctamente.
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET["error"]) && $_GET["error"] === "self_delete"): ?>
                    <div class="alert alert-danger mb-3">
                        <i class="fa-solid fa-triangle-exclamation me-2"></i>
                        No puedes eliminar tu propio usuario.
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET["error"]) && $_GET["error"] === "delete_failed"): ?>
                    <div class="alert alert-danger mb-3">
                        <i class="fa-solid fa-triangle-exclamation me-2"></i>
                        No se pudo eliminar el usuario.
                    </div>
                <?php endif; ?>
                <?php if (isset($_GET["error"]) && $_GET["error"] === "root_delete"): ?>
                    <div class="alert alert-danger mb-3">
                        <i class="fa-solid fa-shield-halved me-2"></i>
                        El usuario principal del sistema no puede ser eliminado.
                    </div>
                <?php endif; ?>



                <div>
                    <h1 class="h3 mb-1 text-brown fw-bold">Usuarios</h1>
                    <div class="text-muted small">Gestión de usuarios del sistema</div>
                </div>

                <a href="../../controller/nuevoUsuarioController.php" class="btn btn-mint px-3 py-2">
                    <i class="fa-solid fa-user-plus me-2"></i>
                    Agregar nuevo usuario
                </a>
            </div>

            <div class="card card-comanda shadow-sm">
                <div class="card-body">

                    <div class="table-responsive">
                        <table id="tablaUsuarios" class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th style="min-width:260px;">Nombre</th>
                                    <th style="min-width:260px;">Email</th>
                                    <th style="min-width:170px;">Rol</th>
                                    <th class="text-end" style="width:120px;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($usuarios as $u): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">
                                                <?= htmlspecialchars($u["nombre"] . " " . $u["apellido"]) ?>
                                            </div>
                                            <div class="text-muted small">ID: <?= (int)$u["id"] ?></div>
                                        </td>

                                        <td><?= htmlspecialchars($u["email"]) ?></td>

                                        <td>
                                            <span class="badge badge-role ">
                                                <?= htmlspecialchars($u["rol_nombre"] ?? ("Rol " . (int)$u["rol_id"])) ?>
                                            </span>
                                        </td>

                                        <td class="text-end">
                                            <!-- Editar -->
                                            <a class="btn btn-sm btn-light"
                                                href="/LaComanda-main/controller/editarUsuarioController.php?id=<?= (int)$u['id'] ?>"
                                                title="Editar">
                                                <i class="fa-solid fa-pen"></i>
                                            </a>

                                            <!-- Eliminar -->
                                            <?php if ((int)$u["id"] !== 1 && (int)$u["id"] !== 3): ?>
                                                -<form class="d-inline form-eliminar-usuario"
                                                    method="POST"
                                                    action="/LaComanda-main/controller/eliminarUsuarioController.php">
                                                    <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                                                    <button type="submit"
                                                        class="btn btn-sm btn-light text-danger"
                                                        title="Eliminar">
                                                        <i class="fa-solid fa-trash"></i>
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                -<button class="btn btn-sm btn-light text-secondary"
                                                    title="Usuario protegido"
                                                    disabled>
                                                    <i class="fa-solid fa-lock"></i>
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>

        </div>
    </main>

    <?php include __DIR__ . "/../layout/footer.php"; ?>

    <!-- JS -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- DataTables -->
    <script src="https://cdn.datatables.net/2.1.8/js/dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/2.1.8/js/dataTables.bootstrap5.min.js"></script>

    <!-- SweetAlert2 para confirmación de eliminación -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Script propio -->
    <script src="/LaComanda-main/public/js/admin-usuarios.js"></script>


</body>

</html>
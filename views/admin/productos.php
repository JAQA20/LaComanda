<?php
require_once __DIR__ . "/../../middleware/auth.php";
require_once __DIR__ . "/../../middleware/roles.php";
verificarRol([1]);

require_once __DIR__ . "/../../model/Productos.php";

$productos = Productos::listar();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos | La Comanda</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- DataTables (Bootstrap 5) -->
    <link href="https://cdn.datatables.net/2.1.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">

    <!-- FontAwesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <!-- Estilos propios -->
    <link rel="stylesheet" href="../../public/css/admin-productos.css">
</head>



<body class="bg-comanda">

    <?php include __DIR__ . "/adminNavbar.php"; ?>

    <main class="container-fluid pt-5 mt-4">
        <div class="container-xxl py-4">

            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">

                <?php if (isset($_GET["created"])): ?>
                    <div class="alert alert-success mb-3">
                        <i class="fa-solid fa-circle-check me-2"></i>
                        Producto creado correctamente.
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET["updated"])): ?>
                    <div class="alert alert-success mb-3">
                        <i class="fa-solid fa-circle-check me-2"></i>
                        Producto actualizado correctamente.
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET["deleted"])): ?>
                    <div class="alert alert-success mb-3">
                        <i class="fa-solid fa-circle-check me-2"></i>
                        Producto eliminado correctamente.
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET["error"])): ?>
                    <div class="alert alert-danger mb-3">
                        <i class="fa-solid fa-triangle-exclamation me-2"></i>
                        <?= htmlspecialchars($_GET["error"]) ?>
                    </div>
                <?php endif; ?>

                <div>
                    <h1 class="h3 mb-1 text-brown fw-bold">Productos</h1>
                    <div class="text-muted small">Gestión de productos del menú</div>
                </div>

                <a href="/LaComanda-main/controller/nuevoProductoController.php" class="btn btn-mint px-3 py-2">
                    <i class="fa-solid fa-plus me-2"></i>
                    Agregar nuevo producto
                </a>
            </div>

            <div class="card card-comanda shadow-sm">
                <div class="card-body">

                    <div class="table-responsive">
                        <table id="tablaProductos" class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th style="min-width:260px;">Producto</th>
                                    <th style="min-width:220px;">Categoría</th>
                                    <th style="min-width:140px;">Precio</th>
                                    <th style="min-width:120px;">Activo</th>
                                    <th class="text-end" style="width:140px;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($productos as $p): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-semibold d-flex align-items-center gap-2">
                                                <span class="icon-circle">
                                                    <i class="fa-solid <?= htmlspecialchars($p["icono"] ?? "fa-mug-hot") ?>"></i>
                                                </span>
                                                <?= htmlspecialchars($p["nombre"]) ?>
                                            </div>
                                            <div class="text-muted small">ID: <?= (int)$p["id"] ?></div>
                                        </td>

                                        <td>
                                            <span class="badge badge-category ">
                                                <?= htmlspecialchars($p["categoria_nombre"] ?? "Sin categoría") ?>
                                            </span>
                                        </td>

                                        <td>₡<?= number_format((int)$p["precio"]) ?></td>

                                        <td>
                                            <?php if ((int)$p["activo"] === 1): ?>
                                                <span class="badge bg-success-subtle text-success border border-success-subtle">
                                                    <i class="fa-solid fa-check me-1"></i> Sí
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                                                    <i class="fa-solid fa-xmark me-1"></i> No
                                                </span>
                                            <?php endif; ?>
                                        </td>

                                        <td class="text-end">
                                            <a class="btn btn-sm btn-light"
                                                href="/LaComanda-main/controller/editarProductoController.php?id=<?= (int)$p['id'] ?>"
                                                title="Editar">
                                                <i class="fa-solid fa-pen"></i>
                                            </a>

                                            <form class="d-inline form-eliminar-producto"
                                                method="POST"
                                                action="/LaComanda-main/controller/eliminarProductosController.php">
                                                <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                                                <button type="submit"
                                                    class="btn btn-sm btn-light text-danger"
                                                    title="Eliminar">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </form>
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

    <!-- SweetAlert2 confirm -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Script propio -->
    <script src="/LaComanda-main/public/js/admin-productos.js"></script>

</body>

</html>
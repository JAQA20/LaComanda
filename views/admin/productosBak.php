<?php
require_once __DIR__ . "/../../middleware/auth.php";
require_once __DIR__ . "/../../middleware/roles.php";
require_once __DIR__ . "/../../config/rutas.php";
require_once __DIR__ . "/../../model/Productos.php";
require_once __DIR__ . "/../../model/Categorias.php";
verificarRol([1]);

$productos = Productos::listar();
$categorias = Categorias::listarTodas();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos | La Comanda</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/2.1.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/admin-productos.css">
</head>

<body class="bg-comanda">

    <?php require_once __DIR__ . "/adminNavbar.php"; ?>

    <main class="container-fluid pt-5 mt-4">
        <div class="container-xxl py-4">

            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">

                <div class="alerts-stack w-100">
                    <?php if (isset($_GET["created"])): ?>
                        <div class="alert alert-success mb-3"><i class="fa-solid fa-circle-check me-2"></i>Producto creado correctamente.</div>
                    <?php endif; ?>
                    <?php if (isset($_GET["updated"])): ?>
                        <div class="alert alert-success mb-3"><i class="fa-solid fa-circle-check me-2"></i>Producto actualizado correctamente.</div>
                    <?php endif; ?>
                    <?php if (isset($_GET["deleted"])): ?>
                        <div class="alert alert-success mb-3"><i class="fa-solid fa-circle-check me-2"></i>Producto eliminado correctamente.</div>
                    <?php endif; ?>
                    <?php if (isset($_GET["categoryCreated"])): ?>
                        <div class="alert alert-success mb-3"><i class="fa-solid fa-circle-check me-2"></i>Categoría creada correctamente.</div>
                    <?php endif; ?>
                    <?php if (isset($_GET["categoryUpdated"])): ?>
                        <div class="alert alert-success mb-3"><i class="fa-solid fa-circle-check me-2"></i>Categoría actualizada correctamente.</div>
                    <?php endif; ?>
                    <?php if (isset($_GET["categoryDeleted"])): ?>
                        <div class="alert alert-success mb-3"><i class="fa-solid fa-circle-check me-2"></i>Categoría eliminada correctamente.</div>
                    <?php endif; ?>
                    <?php if (isset($_GET["error"])): ?>
                        <div class="alert alert-danger mb-3"><i class="fa-solid fa-triangle-exclamation me-2"></i><?= htmlspecialchars($_GET["error"]) ?></div>
                    <?php endif; ?>
                </div>

                <div>
                    <h1 class="h3 mb-1 text-brown fw-bold">Productos y categorías</h1>
                    <div class="text-muted small">Gestión del menú respetando el flujo actual del panel admin</div>
                </div>

                <div class="d-flex gap-2 flex-wrap">
                    <button type="button" class="btn btn-brown px-3 py-2 text-white" style="background-color: #4b2e2a;" data-bs-toggle="modal" data-bs-target="#modalNuevaCategoria">
                        <i class="fa-solid fa-tags me-2"></i>
                        Nueva categoría
                    </button>
                    <a href="<?= BASE_URL ?>views/admin/nuevoProducto.php" class="btn btn-mint px-3 py-2">
                        <i class="fa-solid fa-plus me-2"></i>
                        Agregar nuevo producto
                    </a>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-12">
                    <div class="card card-comanda shadow-sm">
                        <div class="card-body">
                            <div class="section-header mb-3">
                                <h2 class="h5 mb-1 text-brown fw-bold">Productos</h2>
                                <div class="text-muted small">Listado principal del menú</div>
                            </div>
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
                                                        <?php if (!empty($p["imagen"])): ?>
                                                            <img src="<?= htmlspecialchars((strpos($p["imagen"], "http") === 0 ? "" : BASE_URL) . ltrim($p["imagen"], "/")) ?>" alt="Img" class="rounded object-fit-cover" style="width: 40px; height: 40px; border: 1px solid #ddd;">
                                                        <?php else: ?>
                                                            <span class="icon-circle bg-light border text-muted">
                                                                <i class="fa-solid fa-image"></i>
                                                            </span>
                                                        <?php endif; ?>
                                                        <?= htmlspecialchars($p["nombre"], ENT_QUOTES, 'UTF-8') ?>
                                                    </div>
                                                    <div class="text-muted small">ID: <?= (int)$p["id"] ?></div>
                                                </td>
                                                <td>
                                                    <span class="badge badge-category">
                                                        <?= htmlspecialchars($p["categoria_nombre"] ?? "Sin categoría", ENT_QUOTES, 'UTF-8') ?>
                                                    </span>
                                                </td>
                                                <td>₡<?= number_format((int)$p["precio"]) ?></td>
                                                <td>
                                                    <?php if ((int)$p["activo"] === 1): ?>
                                                        <span class="badge bg-success-subtle text-success border border-success-subtle"><i class="fa-solid fa-check me-1"></i>Sí</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle"><i class="fa-solid fa-xmark me-1"></i>No</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-end">
                                                    <a class="btn btn-sm btn-light" href="<?= BASE_URL ?>views/admin/editarProducto.php?id=<?= (int)$p['id'] ?>" title="Editar">
                                                        <i class="fa-solid fa-pen"></i>
                                                    </a>
                                                    <form class="d-inline form-eliminar-producto" method="POST" action="<?= BASE_URL ?>public/api/eliminarProducto.php">
                                                        <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                                                        <button type="submit" class="btn btn-sm btn-light text-danger" title="Eliminar">
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

                <div class="col-12">
                    <div class="card card-comanda shadow-sm">
                        <div class="card-body">
                            <div class="section-header mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <div>
                                    <h2 class="h5 mb-1 text-brown fw-bold">Categorías</h2>

                                </div>
                                <button type="button" class="btn btn-mint btn-sm px-3" data-bs-toggle="modal" data-bs-target="#modalNuevaCategoria">
                                    <i class="fa-solid fa-plus me-2"></i>Agregar categoría
                                </button>
                            </div>
                            <div class="table-responsive">
                                <table id="tablaCategorias" class="table table-hover align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Nombre</th>
                                            <th>Icono</th>
                                            <th>Orden</th>
                                            <th>Activa</th>
                                            <th>Productos</th>
                                            <th class="text-end">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($categorias as $c): ?>
                                            <tr>
                                                <td>
                                                    <div class="fw-semibold d-flex align-items-center gap-2">
                                                        <span class="icon-circle"><i class="fa-solid <?= htmlspecialchars($c['icono'] ?: 'fa-tags') ?>"></i></span>
                                                        <?= htmlspecialchars($c['nombre']) ?>
                                                    </div>
                                                </td>
                                                <td><span class="text-muted"><?= htmlspecialchars($c['icono'] ?? 'fa-tags') ?></span></td>
                                                <td><?= (int)($c['orden'] ?? 1) ?></td>
                                                <td>
                                                    <?php if ((int)$c['activo'] === 1): ?>
                                                        <span class="badge bg-success-subtle text-success border border-success-subtle">Sí</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">No</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><span class="badge text-bg-light border"><?= (int)($c['total_productos'] ?? 0) ?></span></td>
                                                <td class="text-end">
                                                    <button
                                                        type="button"
                                                        class="btn btn-sm btn-light btn-editar-categoria"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#modalEditarCategoria"
                                                        data-id="<?= (int)$c['id'] ?>"
                                                        data-nombre="<?= htmlspecialchars($c['nombre'], ENT_QUOTES, 'UTF-8') ?>"
                                                        data-slug="<?= htmlspecialchars($c['slug'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                                        data-icono="<?= htmlspecialchars($c['icono'] ?? 'fa-tags', ENT_QUOTES, 'UTF-8') ?>"
                                                        data-orden="<?= (int)($c['orden'] ?? 1) ?>"
                                                        data-activo="<?= (int)$c['activo'] ?>">
                                                        <i class="fa-solid fa-pen"></i>
                                                    </button>
                                                    <form class="d-inline form-eliminar-categoria" method="POST" action="<?= BASE_URL ?>public/api/eliminarCategoria.php">
                                                        <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                                                        <button type="submit" class="btn btn-sm btn-light text-danger" title="Eliminar categoría">
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
            </div>

        </div>
    </main>

    <div class="modal fade" id="modalNuevaCategoria" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content modal-comanda">
                <form method="POST" action="<?= BASE_URL ?>public/api/nuevaCategoria.php">
                    <div class="modal-header border-0 pb-0">
                        <div>
                            <h3 class="modal-title h5 text-brown fw-bold mb-1">Nueva categoría</h3>
                            <div class="text-muted small">Crea una categoría sin salir de la gestión de productos</div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body pt-3">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label label-comanda">Nombre</label>
                                <input type="text" name="nombre" class="form-control form-comanda" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label label-comanda">Slug</label>
                                <input type="text" name="slug" class="form-control form-comanda" placeholder="Opcional, se genera automáticamente">
                                <div class="form-text">Si no ingresas uno, se creará uno basado en el nombre.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label label-comanda">Icono (FontAwesome)</label>
                                <input type="text" name="icono" class="form-control form-comanda" value="fa-tags">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label label-comanda">Orden</label>
                                <input type="number" min="1" name="orden" class="form-control form-comanda" value="1">
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="activo" id="categoriaActivaNueva" checked>
                                    <label class="form-check-label label-comanda" for="categoriaActivaNueva">Categoría activa</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-outline-brown" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-mint"><i class="fa-solid fa-floppy-disk me-2"></i>Guardar categoría</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalEditarCategoria" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content modal-comanda">
                <form method="POST" action="<?= BASE_URL ?>public/api/editarCategoria.php">
                    <input type="hidden" name="id" id="editCategoriaId">
                    <div class="modal-header border-0 pb-0">
                        <div>
                            <h3 class="modal-title h5 text-brown fw-bold mb-1">Editar categoría</h3>
                            <div class="text-muted small">Ajusta nombre, slug, orden, icono y estado</div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body pt-3">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label label-comanda">Nombre</label>
                                <input type="text" name="nombre" id="editCategoriaNombre" class="form-control form-comanda" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label label-comanda">Slug</label>
                                <input type="text" name="slug" id="editCategoriaSlug" class="form-control form-comanda">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label label-comanda">Icono (FontAwesome)</label>
                                <input type="text" name="icono" id="editCategoriaIcono" class="form-control form-comanda">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label label-comanda">Orden</label>
                                <input type="number" min="1" name="orden" id="editCategoriaOrden" class="form-control form-comanda">
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="activo" id="editCategoriaActiva">
                                    <label class="form-check-label label-comanda" for="editCategoriaActiva">Categoría activa</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-outline-brown" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-mint"><i class="fa-solid fa-floppy-disk me-2"></i>Guardar cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php require_once __DIR__ . "/../layout/footer.php"; ?>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/2.1.8/js/dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/2.1.8/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="<?= BASE_URL ?>public/js/admin-productos.js"></script>

</body>

</html>
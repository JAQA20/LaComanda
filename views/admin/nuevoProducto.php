<?php
require_once __DIR__ . "/../../middleware/auth.php";
require_once __DIR__ . "/../../middleware/roles.php";
require_once __DIR__ . "/../../config/rutas.php";
require_once __DIR__ . "/../../model/Productos.php";
verificarRol([1]);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$errors = $_SESSION["nuevo_producto_errors"] ?? [];
$old = $_SESSION["nuevo_producto_old"] ?? [
    "categoria_id" => "",
    "nombre" => "",
    "precio" => "",
    "icono" => "fa-mug-hot",
    "activo" => "1"
];
unset($_SESSION["nuevo_producto_errors"], $_SESSION["nuevo_producto_old"]);

try {
    $categorias = Productos::listarCategoriasActivas();
} catch (Throwable $e) {
    $categorias = [];
    $errors[] = "Error cargando categorías: " . $e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Nuevo producto | La Comanda</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <!-- Font -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600;700&display=swap" rel="stylesheet">

    <!-- Estilos propios -->
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/admin-productos.css">
</head>

<body class="bg-comanda">

    <?php require_once __DIR__ . "/adminNavbar.php"; ?>

    <main class="container-fluid pt-5 mt-4">
        <div class="container-xxl py-4">

            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
                <div>
                    <h1 class="h3 mb-1 text-brown fw-bold">Agregar nuevo producto</h1>
                    <div class="text-muted small">Configura el producto para el menú</div>
                </div>

                <a href="<?= BASE_URL ?>views/admin/productos.php" class="btn btn-outline-brown px-3 py-2">
                    <i class="fa-solid fa-arrow-left me-2"></i> Volver
                </a>
            </div>



            <div class="card card-comanda shadow-sm">
                <div class="card-body p-4 p-md-5">

                    <form method="POST" action="<?= BASE_URL ?>public/api/nuevoProducto.php" class="row g-3" novalidate>

                        <div class="col-md-6">
                            <label class="form-label label-comanda">Nombre</label>
                            <input type="text" name="nombre" class="form-control form-comanda"
                                value="<?= htmlspecialchars($old["nombre"] ?? "") ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label label-comanda">Categoría</label>
                            <select name="categoria_id" class="form-select form-comanda" required>
                                <option value="">Seleccione una categoría</option>
                                <?php if (!empty($categorias)): ?>
                                    <?php foreach ($categorias as $c): ?>
                                        <option value="<?= (int)$c["id"] ?>"
                                            <?= ((string)($old["categoria_id"] ?? "") === (string)$c["id"]) ? "selected" : "" ?>>
                                            <?= htmlspecialchars($c["nombre"]) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <?php if (empty($categorias)): ?>
                                <div class="form-text text-danger">No se cargaron categorías.</div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label label-comanda">Precio (CRC)</label>
                            <input type="number" name="precio" min="1" step="1" class="form-control form-comanda"
                                value="<?= htmlspecialchars($old["precio"] ?? "") ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label label-comanda">Icono (FontAwesome)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white">
                                    <i class="fa-solid <?= htmlspecialchars($old["icono"] ?? "fa-mug-hot") ?>"></i>
                                </span>
                                <input type="text" name="icono" class="form-control form-comanda"
                                    value="<?= htmlspecialchars($old["icono"] ?? "fa-mug-hot") ?>"
                                    placeholder="Ej: fa-coffee">
                            </div>
                            <div class="form-text">Ej: fa-coffee, fa-hamburger, fa-ice-cream</div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-check mt-1">
                                <input class="form-check-input" type="checkbox" name="activo" id="activo"
                                    <?= ((string)($old["activo"] ?? "1") === "1") ? "checked" : "" ?>>
                                <label class="form-check-label label-comanda" for="activo">
                                    Producto activo
                                </label>
                            </div>
                        </div>

                        <div class="col-12 d-flex justify-content-end gap-2 mt-2">
                            <a href="<?= BASE_URL ?>views/admin/productos.php" class="btn btn-outline-brown px-4 py-2">
                                Cancelar
                            </a>
                            <button type="submit" class="btn btn-mint px-4 py-2">
                                <i class="fa-solid fa-floppy-disk me-2"></i> Guardar producto
                            </button>
                        </div>

                    </form>

                </div>
            </div>

        </div>
    </main>

    <?php require_once __DIR__ . "/../layout/footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <?php if (!empty($errors)): ?>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'No se pudo crear el producto',
                html: <?= json_encode('<div style="text-align:left;"><ul style="margin:0;padding-left:1.25rem;">' . implode('', array_map(static fn($err) => '<li>' . htmlspecialchars($err, ENT_QUOTES, 'UTF-8') . '</li>', $errors)) . '</ul></div>') ?>,
                confirmButtonText: 'Entendido'
            });
        </script>
    <?php endif; ?>
</body>

</html>
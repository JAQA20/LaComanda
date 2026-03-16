<?php
require_once __DIR__ . "/../../config/rutas.php";
require_once __DIR__ . "/../../middleware/auth.php";
require_once __DIR__ . "/../../middleware/roles.php";
require_once __DIR__ . "/../../model/Productos.php";
verificarRol([1]); // solo Admin

$errors = [];
$id = (int)($_GET["id"] ?? 0);

try {
    $categorias = Productos::listarCategoriasActivas();
} catch (Throwable $e) {
    $categorias = [];
    $errors[] = "Error cargando categorías: " . $e->getMessage();
}

$producto = null;
try {
    $producto = Productos::obtenerPorId($id);
    if (!$producto) throw new Exception("Producto no encontrado.");
} catch (Throwable $e) {
    $errors[] = $e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Editar producto | La Comanda</title>

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
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/admin-productos.css">
</head>

<body class="custom-beige min-h-screen">

    <?php require_once __DIR__ . "/adminNavbar.php"; ?>

    <main class="container-fluid pt-5 mt-4">
        <div class="container-xxl py-4">

            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
                <div>
                    <h1 class="h3 mb-1 text-brown fw-bold">Editar producto</h1>
                    <div class="text-muted small">Actualiza los datos del producto</div>
                </div>

                <a href="<?= BASE_URL ?>views/admin/productos.php" class="btn btn-outline-brown px-3 py-2">
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

                    <!-- action="" para que postee al mismo controller -->
                    <form method="POST" action="<?= BASE_URL ?>public/api/editarProducto.php" class="row g-3" novalidate>

                        <input type="hidden" name="id" value="<?= (int)($producto["id"] ?? 0) ?>">

                        <div class="col-md-6">
                            <label class="form-label label-comanda">Nombre</label>
                            <input type="text" name="nombre" class="form-control form-comanda"
                                value="<?= htmlspecialchars($producto["nombre"] ?? "") ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label label-comanda">Categoría</label>
                            <select name="categoria_id" class="form-select form-comanda" required>
                                <option value="">Seleccione una categoría</option>
                                <?php foreach ($categorias as $c): ?>
                                    <option value="<?= (int)$c["id"] ?>"
                                        <?= ((int)($producto["categoria_id"] ?? 0) === (int)$c["id"]) ? "selected" : "" ?>>
                                        <?= htmlspecialchars($c["nombre"]) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label label-comanda">Precio (CRC)</label>
                            <input type="number" name="precio" min="1" step="1" class="form-control form-comanda"
                                value="<?= htmlspecialchars($producto["precio"] ?? "") ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label label-comanda">Icono (FontAwesome)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white">
                                    <i class="fa-solid <?= htmlspecialchars($producto["icono"] ?? "fa-mug-hot") ?>"></i>
                                </span>
                                <input type="text" name="icono" class="form-control form-comanda"
                                    value="<?= htmlspecialchars($producto["icono"] ?? "fa-mug-hot") ?>"
                                    placeholder="Ej: fa-coffee">
                            </div>
                            <div class="form-text">Ej: fa-coffee, fa-hamburger, fa-ice-cream</div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-check mt-1">
                                <input class="form-check-input" type="checkbox" name="activo" id="activo"
                                    <?= ((int)($producto["activo"] ?? 1) === 1) ? "checked" : "" ?>>
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
</body>

</html>
<?php
require_once __DIR__ . "/../../config/env.php";
app_configure_errors();
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

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        window.FontAwesomeConfig = { autoReplaceSvg: 'nest' };
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        cream: '#F8F5F0',
                        beigeSoft: '#EDE3D6',
                        brownDark: '#4E342E',
                        brownSoft: '#8D6E63',
                        mintGreen: '#7FB69E',
                        goldSoft: '#D6B98C'
                    },
                    boxShadow: {
                        card: '0 10px 25px rgba(0,0,0,0.08)'
                    }
                }
            }
        }
    </script>
    
    <!-- Bootstrap para Modals y DataTables -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.datatables.net/2.1.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/style.css">

    <style>
        /* Ajustes para que DataTables se vea más limpio con Tailwind */
        .dataTables_wrapper .row { margin-bottom: 1rem; align-items: center; }
        .dataTables_filter input { border-radius: 0.75rem !important; border: 1px solid #efe7db !important; padding: 0.4rem 1rem !important; }
        .dataTables_length select { border-radius: 0.5rem !important; border: 1px solid #efe7db !important; }
        .page-item.active .page-link { background-color: #7FB69E !important; border-color: #7FB69E !important; }
        .page-link { color: #8D6E63; }
        
        /* Ocultar header de tabla original si DataTable inyecta el suyo, o ajustar padding */
        table.dataTable>thead>tr>th { border-bottom: 1px solid #efe7db; color: #8D6E63; font-weight: 600; }
        table.dataTable>tbody>tr>td { border-color: #efe7db; }
        
        /* Fix modal z-index with tailwind flex */
        .modal-backdrop { z-index: 40 !important; }
        .modal { z-index: 50 !important; }
    </style>
</head>

<body class="custom-beige min-h-screen font-sans">
    
    <?php require_once __DIR__ . "/adminNavbar.php"; ?>

    <div class="flex pt-16 min-h-screen">
        <main class="flex-1 p-6 w-full max-w-7xl mx-auto">
            
            <div class="bg-gradient-to-r from-mintGreen to-[#6a9e7a] rounded-3xl shadow-card p-8 mb-8 text-white flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                <div>
                    <p class="uppercase tracking-[0.2em] text-sm text-beigeSoft mb-2">Administración</p>
                    <h1 class="text-3xl md:text-4xl font-extrabold mb-2">Productos y Categorías</h1>
                    <p class="text-sm md:text-base text-white/85 max-w-2xl">
                        Gestiona el menú de tu restaurante, organiza los productos y ajusta precios e imágenes.
                    </p>
                </div>
                <div class="flex flex-col sm:flex-row gap-3">
                    <button type="button" class="bg-white/10 hover:bg-white/20 text-white border border-white/20 backdrop-blur-sm px-5 py-3 rounded-xl transition-colors font-semibold flex items-center justify-center gap-2" data-bs-toggle="modal" data-bs-target="#modalNuevaCategoria">
                        <i class="fa-solid fa-tags"></i> Nueva categoría
                    </button>
                    <button type="button" class="bg-white text-mintGreen hover:bg-gray-50 px-5 py-3 rounded-xl shadow-sm transition-colors font-semibold flex items-center justify-center gap-2" data-bs-toggle="modal" data-bs-target="#modalNuevoProducto">
                        <i class="fa-solid fa-plus"></i> Agregar producto
                    </button>
                </div>
            </div>

            <!-- Alertas -->
            <div class="w-full mb-6 space-y-3">
                <?php if (isset($_GET["created"])): ?>
                    <div class="bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-xl p-4 flex items-center gap-3 font-semibold"><i class="fa-solid fa-circle-check text-xl"></i> Producto creado correctamente.</div>
                <?php endif; ?>
                <?php if (isset($_GET["updated"])): ?>
                    <div class="bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-xl p-4 flex items-center gap-3 font-semibold"><i class="fa-solid fa-circle-check text-xl"></i> Producto actualizado correctamente.</div>
                <?php endif; ?>
                <?php if (isset($_GET["deleted"])): ?>
                    <div class="bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-xl p-4 flex items-center gap-3 font-semibold"><i class="fa-solid fa-circle-check text-xl"></i> Producto eliminado correctamente.</div>
                <?php endif; ?>
                <?php if (isset($_GET["categoryCreated"])): ?>
                    <div class="bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-xl p-4 flex items-center gap-3 font-semibold"><i class="fa-solid fa-circle-check text-xl"></i> Categoría creada correctamente.</div>
                <?php endif; ?>
                <?php if (isset($_GET["categoryUpdated"])): ?>
                    <div class="bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-xl p-4 flex items-center gap-3 font-semibold"><i class="fa-solid fa-circle-check text-xl"></i> Categoría actualizada correctamente.</div>
                <?php endif; ?>
                <?php if (isset($_GET["categoryDeleted"])): ?>
                    <div class="bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-xl p-4 flex items-center gap-3 font-semibold"><i class="fa-solid fa-circle-check text-xl"></i> Categoría eliminada correctamente.</div>
                <?php endif; ?>
                <?php if (isset($_GET["error"])): ?>
                    <div class="bg-red-50 text-red-700 border border-red-200 rounded-xl p-4 flex items-center gap-3 font-semibold"><i class="fa-solid fa-triangle-exclamation text-xl"></i> <?= htmlspecialchars($_GET["error"]) ?></div>
                <?php endif; ?>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
                <!-- Columna Categorías -->
                <div class="xl:col-span-1">
                    <div class="bg-white rounded-2xl shadow-card p-6 border border-[#efe7db] h-full">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-xl font-extrabold text-brownDark">Categorías</h2>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="text-brownSoft text-left border-b border-[#efe7db]">
                                        <th class="pb-3 font-semibold">Nombre</th>
                                        <th class="pb-3 font-semibold text-center">Prod.</th>
                                        <th class="pb-3 font-semibold text-right">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[#efe7db]">
                                    <?php foreach ($categorias as $c): ?>
                                    <tr class="hover:bg-[#FCFAF7] transition-colors group">
                                        <td class="py-4 pr-3">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 rounded-xl bg-[#F5EEE5] flex items-center justify-center text-brownSoft group-hover:bg-white group-hover:shadow-sm transition-all border border-transparent group-hover:border-[#efe7db]">
                                                    <i class="fa-solid <?= htmlspecialchars($c['icono'] ?: 'fa-tags') ?>"></i>
                                                </div>
                                                <div>
                                                    <p class="font-bold text-brownDark text-base"><?= htmlspecialchars($c['nombre']) ?></p>
                                                    <span class="text-[10px] <?= ((int)$c['activo'] === 1) ? 'text-emerald-500' : 'text-gray-400' ?> uppercase font-bold tracking-wider">
                                                        <?= ((int)$c['activo'] === 1) ? 'Activa' : 'Inactiva' ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-4 px-2 text-center align-middle">
                                            <span class="px-2.5 py-1 bg-white border border-[#efe7db] text-brownDark rounded-lg text-xs font-bold shadow-sm"><?= (int)($c['total_productos'] ?? 0) ?></span>
                                        </td>
                                        <td class="py-4 pl-3 text-right align-middle">
                                            <div class="flex items-center justify-end gap-1">
                                                <button type="button" class="w-8 h-8 rounded-lg flex items-center justify-center text-brownSoft hover:text-brownDark hover:bg-[#F5EEE5] transition-colors btn-editar-categoria" 
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#modalEditarCategoria"
                                                    data-id="<?= (int)$c['id'] ?>"
                                                    data-nombre="<?= htmlspecialchars($c['nombre'], ENT_QUOTES, 'UTF-8') ?>"
                                                    data-slug="<?= htmlspecialchars($c['slug'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                                    data-icono="<?= htmlspecialchars($c['icono'] ?? 'fa-tags', ENT_QUOTES, 'UTF-8') ?>"
                                                    data-orden="<?= (int)($c['orden'] ?? 1) ?>"
                                                    data-activo="<?= (int)$c['activo'] ?>">
                                                    <i class="fa-solid fa-pen text-xs"></i>
                                                </button>
                                                <form class="inline-block form-eliminar-categoria" method="POST" action="<?= BASE_URL ?>public/api/eliminarCategoria.php">
                                                    <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                                                    <button type="submit" class="w-8 h-8 rounded-lg flex items-center justify-center text-red-400 hover:text-red-600 hover:bg-red-50 transition-colors">
                                                        <i class="fa-solid fa-trash text-xs"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Columna Productos -->
                <div class="xl:col-span-2">
                    <div class="bg-white rounded-2xl shadow-card p-6 border border-[#efe7db] h-full">
                        <div class="mb-6">
                            <h2 class="text-xl font-extrabold text-brownDark">Catálogo de Productos</h2>
                        </div>
                        
                        <div class="overflow-x-auto pb-4">
                            <table id="tablaProductos" class="w-full text-sm align-middle">
                                <thead>
                                    <tr class="text-brownSoft text-left border-b border-[#efe7db]">
                                        <th class="pb-3 font-semibold min-w-[200px]">Producto</th>
                                        <th class="pb-3 font-semibold min-w-[120px]">Categoría</th>
                                        <th class="pb-3 font-semibold min-w-[100px]">Precio</th>
                                        <th class="pb-3 font-semibold min-w-[100px]">Estado</th>
                                        <th class="pb-3 font-semibold text-right min-w-[100px]">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[#efe7db]">
                                    <?php foreach ($productos as $p): ?>
                                    <tr class="hover:bg-[#FCFAF7] transition-colors">
                                        <td class="py-3 pr-3">
                                            <div class="flex items-center gap-3">
                                                <?php if (!empty($p["imagen"])): ?>
                                                    <img src="<?= htmlspecialchars((strpos($p["imagen"], "http") === 0 ? "" : BASE_URL) . ltrim($p["imagen"], "/")) ?>" alt="Img" class="w-12 h-12 rounded-xl object-cover border border-[#efe7db] shadow-sm">
                                                <?php else: ?>
                                                    <div class="w-12 h-12 rounded-xl bg-[#F5EEE5] flex items-center justify-center text-brownSoft shadow-sm">
                                                        <i class="fa-solid fa-image text-lg"></i>
                                                    </div>
                                                <?php endif; ?>
                                                <div>
                                                    <p class="font-bold text-brownDark text-base"><?= htmlspecialchars($p["nombre"], ENT_QUOTES, 'UTF-8') ?></p>
                                                    <p class="text-[11px] text-brownSoft font-semibold tracking-wide">ID: <?= (int)$p["id"] ?></p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-3 px-2">
                                            <span class="px-3 py-1 bg-white border border-[#efe7db] text-brownDark rounded-lg text-xs font-bold shadow-sm inline-block">
                                                <?= htmlspecialchars($p["categoria_nombre"] ?? "Sin categoría", ENT_QUOTES, 'UTF-8') ?>
                                            </span>
                                        </td>
                                        <td class="py-3 px-2 font-extrabold text-brownDark text-base">
                                            ₡<?= number_format((int)$p["precio"]) ?>
                                        </td>
                                        <td class="py-3 px-2">
                                            <?php if ((int)$p["activo"] === 1): ?>
                                                <span class="px-2.5 py-1.5 bg-emerald-50 text-emerald-600 rounded-lg text-xs font-bold border border-emerald-100 inline-flex items-center gap-1.5"><i class="fa-solid fa-check"></i> Activo</span>
                                            <?php else: ?>
                                                <span class="px-2.5 py-1.5 bg-gray-50 text-gray-500 rounded-lg text-xs font-bold border border-gray-200 inline-flex items-center gap-1.5"><i class="fa-solid fa-xmark"></i> Inactivo</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-3 pl-3 text-right">
                                            <div class="flex items-center justify-end gap-1.5">
                                                <button type="button" class="w-9 h-9 inline-flex items-center justify-center rounded-xl bg-white border border-[#efe7db] text-brownDark hover:bg-[#F5EEE5] hover:border-[#e8dccb] shadow-sm transition-all btn-editar-producto"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#modalEditarProducto"
                                                    data-id="<?= (int)$p['id'] ?>"
                                                    data-nombre="<?= htmlspecialchars($p['nombre'], ENT_QUOTES, 'UTF-8') ?>"
                                                    data-categoria_id="<?= (int)$p['categoria_id'] ?>"
                                                    data-precio="<?= (int)$p['precio'] ?>"
                                                    data-imagen_url="<?= htmlspecialchars($p['imagen'], ENT_QUOTES, 'UTF-8') ?>"
                                                    data-activo="<?= (int)$p['activo'] ?>">
                                                    <i class="fa-solid fa-pen text-sm"></i>
                                                </button>
                                                <form class="inline-block form-eliminar-producto" method="POST" action="<?= BASE_URL ?>public/api/eliminarProducto.php">
                                                    <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                                                    <button type="submit" class="w-9 h-9 inline-flex items-center justify-center rounded-xl bg-white border border-red-100 text-red-500 hover:bg-red-50 hover:border-red-200 shadow-sm transition-all">
                                                        <i class="fa-solid fa-trash text-sm"></i>
                                                    </button>
                                                </form>
                                            </div>
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
    </div>

    <!-- Modal Nueva Categoría -->
    <div class="modal fade" id="modalNuevaCategoria" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-2xl shadow-card overflow-hidden">
                <form method="POST" action="<?= BASE_URL ?>public/api/nuevaCategoria.php">
                    <div class="bg-[#F8F5F0] px-6 py-4 border-b border-[#efe7db] flex justify-between items-center">
                        <div>
                            <h3 class="text-xl font-extrabold text-brownDark">Nueva categoría</h3>
                        </div>
                        <button type="button" class="text-brownSoft hover:text-brownDark transition-colors" data-bs-dismiss="modal"><i class="fa-solid fa-xmark text-xl"></i></button>
                    </div>
                    
                    <div class="p-6 space-y-4 bg-white">
                        <div>
                            <label class="block text-sm font-bold text-brownDark mb-1">Nombre</label>
                            <input type="text" name="nombre" class="w-full px-4 py-2.5 rounded-xl border border-[#efe7db] bg-[#FCFAF7] focus:outline-none focus:ring-2 focus:ring-mintGreen focus:border-transparent transition-all" required>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-brownDark mb-1">Slug (Opcional)</label>
                            <input type="text" name="slug" class="w-full px-4 py-2.5 rounded-xl border border-[#efe7db] bg-[#FCFAF7] focus:outline-none focus:ring-2 focus:ring-mintGreen focus:border-transparent transition-all" placeholder="Se genera automáticamente">
                        </div>
                        <div class="flex gap-4">
                            <div class="flex-1">
                                <label class="block text-sm font-bold text-brownDark mb-1">Icono (FontAwesome)</label>
                                <input type="text" name="icono" class="w-full px-4 py-2.5 rounded-xl border border-[#efe7db] bg-[#FCFAF7] focus:outline-none focus:ring-2 focus:ring-mintGreen focus:border-transparent transition-all" value="fa-tags">
                            </div>
                            <div class="w-24">
                                <label class="block text-sm font-bold text-brownDark mb-1">Orden</label>
                                <input type="number" min="1" name="orden" class="w-full px-4 py-2.5 rounded-xl border border-[#efe7db] bg-[#FCFAF7] focus:outline-none focus:ring-2 focus:ring-mintGreen focus:border-transparent transition-all" value="1">
                            </div>
                        </div>
                        <div class="pt-2">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" name="activo" id="categoriaActivaNueva" class="w-5 h-5 rounded border-[#efe7db] text-mintGreen focus:ring-mintGreen" checked>
                                <span class="text-sm font-bold text-brownDark">Categoría visible en el POS</span>
                            </label>
                        </div>
                    </div>
                    <div class="px-6 py-4 bg-[#FCFAF7] border-t border-[#efe7db] flex justify-end gap-3">
                        <button type="button" class="px-5 py-2.5 rounded-xl text-brownDark font-bold hover:bg-[#F5EEE5] transition-colors" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-brownDark text-white font-bold hover:bg-[#362018] transition-colors shadow-sm"><i class="fa-solid fa-floppy-disk me-2"></i>Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Nuevo Producto -->
    <div class="modal fade" id="modalNuevoProducto" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 rounded-2xl shadow-card overflow-hidden">
                <form method="POST" action="<?= BASE_URL ?>public/api/nuevoProducto.php" enctype="multipart/form-data">
                    <div class="bg-[#F8F5F0] px-6 py-4 border-b border-[#efe7db] flex justify-between items-center">
                        <div>
                            <h3 class="text-xl font-extrabold text-brownDark">Nuevo producto</h3>
                        </div>
                        <button type="button" class="text-brownSoft hover:text-brownDark transition-colors" data-bs-dismiss="modal"><i class="fa-solid fa-xmark text-xl"></i></button>
                    </div>
                    
                    <div class="p-6 bg-white">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-brownDark mb-1">Nombre</label>
                                <input type="text" name="nombre" class="w-full px-4 py-2.5 rounded-xl border border-[#efe7db] bg-[#FCFAF7] focus:outline-none focus:ring-2 focus:ring-mintGreen focus:border-transparent transition-all" required>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-brownDark mb-1">Categoría</label>
                                <select name="categoria_id" class="w-full px-4 py-2.5 rounded-xl border border-[#efe7db] bg-[#FCFAF7] focus:outline-none focus:ring-2 focus:ring-mintGreen focus:border-transparent transition-all" required>
                                    <option value="">Seleccione una categoría</option>
                                    <?php foreach ($categorias as $c): ?>
                                        <option value="<?= (int)$c['id'] ?>"><?= htmlspecialchars($c['nombre']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-brownDark mb-1">Precio (CRC)</label>
                                <input type="number" name="precio" min="1" step="1" class="w-full px-4 py-2.5 rounded-xl border border-[#efe7db] bg-[#FCFAF7] focus:outline-none focus:ring-2 focus:ring-mintGreen focus:border-transparent transition-all" required>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-bold text-brownDark mb-1">Imagen (Archivo)</label>
                                <input type="file" name="imagen_file" class="w-full px-4 py-2 rounded-xl border border-[#efe7db] bg-[#FCFAF7] text-sm file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-[#F5EEE5] file:text-brownDark hover:file:bg-[#eadbc6] transition-all" accept="image/png, image/jpeg, image/webp">
                                <p class="text-[11px] text-brownSoft mt-1">Sube un archivo JPG, PNG o WebP, o proporciona una URL abajo.</p>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-bold text-brownDark mb-1">Imagen (URL)</label>
                                <input type="url" name="imagen_url" class="w-full px-4 py-2.5 rounded-xl border border-[#efe7db] bg-[#FCFAF7] focus:outline-none focus:ring-2 focus:ring-mintGreen focus:border-transparent transition-all" placeholder="https://...">
                            </div>
                            <div class="md:col-span-2 pt-2">
                                <label class="flex items-center gap-3 cursor-pointer w-max">
                                    <input type="checkbox" name="activo" class="w-5 h-5 rounded border-[#efe7db] text-mintGreen focus:ring-mintGreen" checked>
                                    <span class="text-sm font-bold text-brownDark">Producto activo</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="px-6 py-4 bg-[#FCFAF7] border-t border-[#efe7db] flex justify-end gap-3">
                        <button type="button" class="px-5 py-2.5 rounded-xl text-brownDark font-bold hover:bg-[#F5EEE5] transition-colors" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-mintGreen text-white font-bold hover:bg-[#6a9e7a] transition-colors shadow-sm"><i class="fa-solid fa-floppy-disk me-2"></i>Crear producto</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Editar Producto -->
    <div class="modal fade" id="modalEditarProducto" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 rounded-2xl shadow-card overflow-hidden">
                <form method="POST" action="<?= BASE_URL ?>public/api/editarProducto.php" enctype="multipart/form-data">
                    <input type="hidden" name="id" id="editProductoId">
                    <div class="bg-[#F8F5F0] px-6 py-4 border-b border-[#efe7db] flex justify-between items-center">
                        <div>
                            <h3 class="text-xl font-extrabold text-brownDark">Editar producto</h3>
                        </div>
                        <button type="button" class="text-brownSoft hover:text-brownDark transition-colors" data-bs-dismiss="modal"><i class="fa-solid fa-xmark text-xl"></i></button>
                    </div>
                    
                    <div class="p-6 bg-white">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-brownDark mb-1">Nombre</label>
                                <input type="text" name="nombre" id="editProductoNombre" class="w-full px-4 py-2.5 rounded-xl border border-[#efe7db] bg-[#FCFAF7] focus:outline-none focus:ring-2 focus:ring-mintGreen focus:border-transparent transition-all" required>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-brownDark mb-1">Categoría</label>
                                <select name="categoria_id" id="editProductoCategoria" class="w-full px-4 py-2.5 rounded-xl border border-[#efe7db] bg-[#FCFAF7] focus:outline-none focus:ring-2 focus:ring-mintGreen focus:border-transparent transition-all" required>
                                    <option value="">Seleccione una categoría</option>
                                    <?php foreach ($categorias as $c): ?>
                                        <option value="<?= (int)$c['id'] ?>"><?= htmlspecialchars($c['nombre']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-brownDark mb-1">Precio (CRC)</label>
                                <input type="number" name="precio" id="editProductoPrecio" min="1" step="1" class="w-full px-4 py-2.5 rounded-xl border border-[#efe7db] bg-[#FCFAF7] focus:outline-none focus:ring-2 focus:ring-mintGreen focus:border-transparent transition-all" required>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-bold text-brownDark mb-1">Actualizar Imagen (Archivo)</label>
                                <input type="file" name="imagen_file" class="w-full px-4 py-2 rounded-xl border border-[#efe7db] bg-[#FCFAF7] text-sm file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-[#F5EEE5] file:text-brownDark hover:file:bg-[#eadbc6] transition-all" accept="image/png, image/jpeg, image/webp">
                                <p class="text-[11px] text-brownSoft mt-1">Deja vacío para mantener la imagen actual.</p>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-bold text-brownDark mb-1">Actualizar Imagen (URL)</label>
                                <input type="url" name="imagen_url" id="editProductoImagenUrl" class="w-full px-4 py-2.5 rounded-xl border border-[#efe7db] bg-[#FCFAF7] focus:outline-none focus:ring-2 focus:ring-mintGreen focus:border-transparent transition-all" placeholder="https://...">
                            </div>
                            <div class="md:col-span-2 pt-2">
                                <label class="flex items-center gap-3 cursor-pointer w-max">
                                    <input type="checkbox" name="activo" id="editProductoActivo" class="w-5 h-5 rounded border-[#efe7db] text-mintGreen focus:ring-mintGreen">
                                    <span class="text-sm font-bold text-brownDark">Producto activo</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="px-6 py-4 bg-[#FCFAF7] border-t border-[#efe7db] flex justify-end gap-3">
                        <button type="button" class="px-5 py-2.5 rounded-xl text-brownDark font-bold hover:bg-[#F5EEE5] transition-colors" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-brownDark text-white font-bold hover:bg-[#362018] transition-colors shadow-sm"><i class="fa-solid fa-floppy-disk me-2"></i>Actualizar producto</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Editar Categoría -->
    <div class="modal fade" id="modalEditarCategoria" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-2xl shadow-card overflow-hidden">
                <form method="POST" action="<?= BASE_URL ?>public/api/editarCategoria.php">
                    <input type="hidden" name="id" id="editCategoriaId">
                    <div class="bg-[#F8F5F0] px-6 py-4 border-b border-[#efe7db] flex justify-between items-center">
                        <div>
                            <h3 class="text-xl font-extrabold text-brownDark">Editar categoría</h3>
                        </div>
                        <button type="button" class="text-brownSoft hover:text-brownDark transition-colors" data-bs-dismiss="modal"><i class="fa-solid fa-xmark text-xl"></i></button>
                    </div>
                    
                    <div class="p-6 space-y-4 bg-white">
                        <div>
                            <label class="block text-sm font-bold text-brownDark mb-1">Nombre</label>
                            <input type="text" name="nombre" id="editCategoriaNombre" class="w-full px-4 py-2.5 rounded-xl border border-[#efe7db] bg-[#FCFAF7] focus:outline-none focus:ring-2 focus:ring-mintGreen focus:border-transparent transition-all" required>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-brownDark mb-1">Slug</label>
                            <input type="text" name="slug" id="editCategoriaSlug" class="w-full px-4 py-2.5 rounded-xl border border-[#efe7db] bg-[#FCFAF7] focus:outline-none focus:ring-2 focus:ring-mintGreen focus:border-transparent transition-all">
                        </div>
                        <div class="flex gap-4">
                            <div class="flex-1">
                                <label class="block text-sm font-bold text-brownDark mb-1">Icono (FontAwesome)</label>
                                <input type="text" name="icono" id="editCategoriaIcono" class="w-full px-4 py-2.5 rounded-xl border border-[#efe7db] bg-[#FCFAF7] focus:outline-none focus:ring-2 focus:ring-mintGreen focus:border-transparent transition-all">
                            </div>
                            <div class="w-24">
                                <label class="block text-sm font-bold text-brownDark mb-1">Orden</label>
                                <input type="number" min="1" name="orden" id="editCategoriaOrden" class="w-full px-4 py-2.5 rounded-xl border border-[#efe7db] bg-[#FCFAF7] focus:outline-none focus:ring-2 focus:ring-mintGreen focus:border-transparent transition-all">
                            </div>
                        </div>
                        <div class="pt-2">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" name="activo" id="editCategoriaActiva" class="w-5 h-5 rounded border-[#efe7db] text-mintGreen focus:ring-mintGreen">
                                <span class="text-sm font-bold text-brownDark">Categoría visible en el POS</span>
                            </label>
                        </div>
                    </div>
                    <div class="px-6 py-4 bg-[#FCFAF7] border-t border-[#efe7db] flex justify-end gap-3">
                        <button type="button" class="px-5 py-2.5 rounded-xl text-brownDark font-bold hover:bg-[#F5EEE5] transition-colors" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-brownDark text-white font-bold hover:bg-[#362018] transition-colors shadow-sm"><i class="fa-solid fa-floppy-disk me-2"></i>Actualizar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/2.1.8/js/dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/2.1.8/js/dataTables.bootstrap5.min.js"></script>
    <script>
        const BASE_URL = '<?= BASE_URL ?>';
    </script>
    <script src="<?= BASE_URL ?>public/js/admin-productos.js?v=<?= time() ?>"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
</html>
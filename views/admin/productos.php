<?php
require_once __DIR__ . "/../../config/env.php";
app_configure_errors();
require_once __DIR__ . "/../../middleware/auth.php";
require_once __DIR__ . "/../../middleware/roles.php";
require_once __DIR__ . "/../../config/rutas.php";
require_once __DIR__ . "/../../model/Productos.php";
require_once __DIR__ . "/../../model/Categorias.php";
require_once __DIR__ . "/../../model/Modificadores.php";
verificarRol([1]);

$productos = Productos::listar();
$categorias = Categorias::listarTodas();
$grupos = Modificadores::listarGrupos();
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
                    <button type="button" class="bg-[#1A73E8] hover:bg-[#1557B0] text-white px-5 py-3 rounded-xl shadow-sm transition-colors font-semibold flex items-center justify-center gap-2" data-bs-toggle="modal" data-bs-target="#modalImportarMenu">
                        <i class="fa-solid fa-wand-magic-sparkles"></i> Importar PDF (IA)
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
                                                    <div class="flex items-center gap-2 mt-1">
                                                        <span class="text-[10px] <?= ((int)$c['activo'] === 1) ? 'text-emerald-500' : 'text-gray-400' ?> uppercase font-bold tracking-wider">
                                                            <?= ((int)$c['activo'] === 1) ? 'Activa' : 'Inactiva' ?>
                                                        </span>
                                                        <?php if ((int)$c['id'] !== 1): ?>
                                                        <span class="text-[10px] bg-[#F5EEE5] text-brownDark px-1.5 py-0.5 rounded uppercase font-bold tracking-wider">
                                                            <?= htmlspecialchars($c['area'] ?? 'cocina') ?>
                                                        </span>
                                                        <?php endif; ?>
                                                    </div>
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
                                                    data-area="<?= htmlspecialchars($c['area'] ?? 'cocina', ENT_QUOTES, 'UTF-8') ?>"
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
                                                    <?php $imgFull = (strpos($p["imagen"], "http") === 0 ? "" : BASE_URL) . ltrim($p["imagen"], "/"); ?>
                                                    <img src="<?= htmlspecialchars($imgFull) ?>" alt="Img" class="w-12 h-12 rounded-xl object-cover border border-[#efe7db] shadow-sm cursor-pointer hover:opacity-80 transition-opacity" onclick="openFullscreenPreviewFromTable(this)">
                                                <?php else: ?>
                                                    <?php $imgFull = ''; ?>
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
                                                    data-imagen_url="<?= htmlspecialchars($p['imagen'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                                    data-imagen_full_url="<?= htmlspecialchars($imgFull, ENT_QUOTES, 'UTF-8') ?>"
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

            <!-- Sección Modificadores -->
            <div class="mt-8 mb-4 flex justify-between items-end">
                <h2 class="text-2xl font-extrabold text-brownDark">Grupos de Modificadores (Extras)</h2>
                <button class="bg-mintGreen hover:bg-[#6CA38B] text-white px-5 py-2.5 rounded-xl font-bold transition-all shadow-sm flex items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalNuevoGrupo">
                    <i class="fa-solid fa-plus"></i> Nuevo Grupo
                </button>
            </div>
            
            <?php if (empty($grupos)): ?>
                <div class="bg-white rounded-2xl p-8 text-center shadow-card border border-[#efe7db] mb-8">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-cream text-goldSoft mb-4">
                        <i class="fa-solid fa-sliders text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-bold text-brownDark mb-2">Sin modificadores configurados</h3>
                    <p class="text-brownSoft text-sm mb-6 max-w-md mx-auto">Crea opciones extra (ej. Tamaños, Leche, Extras) y asígnalos a tus categorías para personalizar los pedidos.</p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 mb-8">
                    <?php foreach ($grupos as $g): ?>
                        <div class="bg-white rounded-2xl p-5 shadow-card border border-[#efe7db] flex flex-col h-full hover:shadow-md transition-shadow">
                            <div class="flex justify-between items-start mb-4">
                                <h3 class="text-lg font-bold text-brownDark"><?= htmlspecialchars($g['nombre']) ?></h3>
                                <div class="flex gap-2">
                                    <button class="text-mintGreen hover:text-[#6CA38B] transition-colors bg-mintGreen/10 hover:bg-mintGreen/20 w-8 h-8 rounded-lg flex items-center justify-center btn-editar-grupo" 
                                            data-id="<?= $g['id'] ?>" 
                                            data-grupo="<?= htmlspecialchars(json_encode($g), ENT_QUOTES, 'UTF-8') ?>"
                                            data-bs-toggle="modal" data-bs-target="#modalEditarGrupo">
                                        <i class="fa-solid fa-pen text-xs"></i>
                                    </button>
                                    <button class="text-red-400 hover:text-red-600 transition-colors bg-red-50 hover:bg-red-100 w-8 h-8 rounded-lg flex items-center justify-center" onclick="eliminarGrupo(<?= $g['id'] ?>)">
                                        <i class="fa-solid fa-trash text-xs"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="flex gap-2 mb-4">
                                <?php if ($g['requerido']): ?>
                                    <span class="px-2 py-1 bg-red-50 text-red-600 rounded-md text-[10px] uppercase font-bold border border-red-100">Requerido</span>
                                <?php else: ?>
                                    <span class="px-2 py-1 bg-gray-50 text-gray-500 rounded-md text-[10px] uppercase font-bold border border-gray-200">Opcional</span>
                                <?php endif; ?>
                                
                                <?php if ($g['seleccion_multiple']): ?>
                                    <span class="px-2 py-1 bg-blue-50 text-blue-600 rounded-md text-[10px] uppercase font-bold border border-blue-100">Múltiple</span>
                                <?php else: ?>
                                    <span class="px-2 py-1 bg-yellow-50 text-yellow-700 rounded-md text-[10px] uppercase font-bold border border-yellow-100">Único</span>
                                <?php endif; ?>
                            </div>

                            <div class="mb-4">
                                <h4 class="text-[11px] font-semibold text-brownSoft mb-2 uppercase tracking-wider">Opciones</h4>
                                <ul class="space-y-1">
                                    <?php foreach ($g['opciones'] as $opt): ?>
                                        <li class="flex justify-between text-sm text-gray-700">
                                            <span><?= htmlspecialchars($opt['nombre']) ?></span>
                                            <span class="font-bold text-mintGreen">+₡<?= number_format($opt['precio_adicional'], 2) ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>

                            <div class="mt-auto pt-4 border-t border-[#efe7db]">
                                <h4 class="text-[11px] font-semibold text-brownSoft mb-2 uppercase tracking-wider">Asignado a Categorías</h4>
                                <div class="flex flex-wrap gap-1.5 mb-3">
                                    <?php 
                                    $asignadas = 0;
                                    foreach ($categorias as $cat) {
                                        if (in_array($cat['id'], $g['categorias'])) {
                                            echo '<span class="px-2.5 py-1 bg-[#F5EEE5] text-brownDark text-[10px] font-bold rounded-lg border border-[#e8dccb]">'.htmlspecialchars($cat['nombre']).'</span>';
                                            $asignadas++;
                                        }
                                    }
                                    if ($asignadas === 0) echo '<span class="text-[11px] text-gray-400 italic font-medium">Ninguna</span>';
                                    ?>
                                </div>
                                <h4 class="text-[11px] font-semibold text-brownSoft mb-2 uppercase tracking-wider">Asignado a Productos</h4>
                                <div class="flex flex-wrap gap-1.5">
                                    <?php 
                                    $asignadosP = 0;
                                    foreach ($productos as $p) {
                                        if (in_array($p['id'], $g['productos'])) {
                                            echo '<span class="px-2.5 py-1 bg-mintGreen/10 text-mintGreen text-[10px] font-bold rounded-lg border border-mintGreen/20">'.htmlspecialchars($p['nombre']).'</span>';
                                            $asignadosP++;
                                        }
                                    }
                                    if ($asignadosP === 0) echo '<span class="text-[11px] text-gray-400 italic font-medium">Ninguno</span>';
                                    ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        </main>
    </div>

    <!-- Modal Nueva Categoría -->
    <div class="modal fade" id="modalNuevaCategoria" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-2xl shadow-card overflow-visible">
                <form method="POST" action="<?= BASE_URL ?>public/api/nuevaCategoria.php">
                    <div class="bg-[#F8F5F0] px-6 py-4 border-b border-[#efe7db] flex justify-between items-center rounded-t-2xl">
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
                        <div>
                            <label class="block text-sm font-bold text-brownDark mb-1">Área de Preparación</label>
                            <select name="area" class="w-full px-4 py-2.5 rounded-xl border border-[#efe7db] bg-[#FCFAF7] focus:outline-none focus:ring-2 focus:ring-mintGreen focus:border-transparent transition-all">
                                <option value="cocina">Cocina</option>
                                <option value="barista">Barista</option>
                            </select>
                        </div>
                        <div class="flex gap-4">
                            <div class="flex-1">
                                <label class="block text-sm font-bold text-brownDark mb-1">Icono (FontAwesome)</label>
                                <div class="flex gap-2 relative">
                                    <div class="relative flex-1">
                                        <input type="text" name="icono" id="newCategoriaIcono" class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-[#efe7db] bg-[#FCFAF7] focus:outline-none focus:ring-2 focus:ring-mintGreen focus:border-transparent transition-all" value="fa-tags">
                                        <i class="fa-solid fa-tags absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400" id="preview-newCategoriaIcono"></i>
                                    </div>
                                    <button type="button" class="px-3 bg-mintGreen/10 text-mintGreen rounded-xl hover:bg-mintGreen/20 transition-colors" onclick="toggleIconPicker('newCategoriaIcono')">
                                        <i class="fa-solid fa-grip"></i>
                                    </button>
                                    <div id="picker-newCategoriaIcono" class="hidden absolute z-[100] bg-white border border-gray-200 shadow-xl rounded-xl p-3 w-64 mt-12 grid grid-cols-5 gap-2 max-h-48 overflow-y-auto top-0 left-0"></div>
                                </div>
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
                    <div class="px-6 py-4 bg-[#FCFAF7] border-t border-[#efe7db] flex justify-end gap-3 rounded-b-2xl">
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
                            <div class="md:col-span-2 flex items-start gap-4">
                                <div class="w-24 h-24 shrink-0 rounded-xl bg-[#F5EEE5] border border-[#efe7db] flex items-center justify-center overflow-hidden cursor-pointer shadow-sm preview-container" onclick="openFullscreenPreview(this)">
                                    <i class="fa-solid fa-image text-2xl text-brownSoft preview-icon"></i>
                                    <img src="" class="w-full h-full object-cover hidden preview-img" alt="Preview">
                                </div>
                                <div class="flex-grow space-y-4">
                                    <div>
                                        <label class="block text-sm font-bold text-brownDark mb-1">Imagen (Archivo)</label>
                                        <input type="file" name="imagen_file" class="w-full px-4 py-2 rounded-xl border border-[#efe7db] bg-[#FCFAF7] text-sm file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-[#F5EEE5] file:text-brownDark hover:file:bg-[#eadbc6] transition-all input-file-img" accept="image/png, image/jpeg, image/webp">
                                        <p class="text-[11px] text-brownSoft mt-1">Sube un archivo JPG, PNG o WebP, o proporciona una URL abajo.</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-bold text-brownDark mb-1">Imagen (URL)</label>
                                        <input type="url" name="imagen_url" class="w-full px-4 py-2.5 rounded-xl border border-[#efe7db] bg-[#FCFAF7] focus:outline-none focus:ring-2 focus:ring-mintGreen focus:border-transparent transition-all input-url-img" placeholder="https://...">
                                    </div>
                                </div>
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
                            <div class="md:col-span-2 flex items-start gap-4">
                                <div class="w-24 h-24 shrink-0 rounded-xl bg-[#F5EEE5] border border-[#efe7db] flex items-center justify-center overflow-hidden cursor-pointer shadow-sm preview-container" onclick="openFullscreenPreview(this)">
                                    <i class="fa-solid fa-image text-2xl text-brownSoft preview-icon"></i>
                                    <img src="" class="w-full h-full object-cover hidden preview-img" alt="Preview">
                                </div>
                                <div class="flex-grow space-y-4">
                                    <div>
                                        <label class="block text-sm font-bold text-brownDark mb-1">Actualizar Imagen (Archivo)</label>
                                        <input type="file" name="imagen_file" class="w-full px-4 py-2 rounded-xl border border-[#efe7db] bg-[#FCFAF7] text-sm file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-[#F5EEE5] file:text-brownDark hover:file:bg-[#eadbc6] transition-all input-file-img" accept="image/png, image/jpeg, image/webp">
                                        <p class="text-[11px] text-brownSoft mt-1">Deja vacío para mantener la imagen actual.</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-bold text-brownDark mb-1">Actualizar Imagen (URL)</label>
                                        <input type="url" name="imagen_url" id="editProductoImagenUrl" class="w-full px-4 py-2.5 rounded-xl border border-[#efe7db] bg-[#FCFAF7] focus:outline-none focus:ring-2 focus:ring-mintGreen focus:border-transparent transition-all input-url-img" placeholder="https://...">
                                    </div>
                                </div>
                            </div>
                            <div class="md:col-span-2 pt-2 pb-2 border-b border-[#efe7db] mb-2 flex items-center justify-between">
                                <label class="block text-sm font-bold text-brownDark">Eliminar Imagen Actual</label>
                                <button type="button" id="btnEliminarImagenDirecto" class="px-4 py-2 bg-red-50 hover:bg-red-100 text-red-600 rounded-lg text-sm font-bold transition-colors border border-red-200">
                                    <i class="fa-solid fa-trash-can mr-1"></i> Eliminar
                                </button>
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
            <div class="modal-content border-0 rounded-2xl shadow-card overflow-visible">
                <form method="POST" action="<?= BASE_URL ?>public/api/editarCategoria.php">
                    <input type="hidden" name="id" id="editCategoriaId">
                    <div class="bg-[#F8F5F0] px-6 py-4 border-b border-[#efe7db] flex justify-between items-center rounded-t-2xl">
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
                        <div id="divEditCategoriaArea">
                            <label class="block text-sm font-bold text-brownDark mb-1">Área de Preparación</label>
                            <select name="area" id="editCategoriaArea" class="w-full px-4 py-2.5 rounded-xl border border-[#efe7db] bg-[#FCFAF7] focus:outline-none focus:ring-2 focus:ring-mintGreen focus:border-transparent transition-all">
                                <option value="cocina">Cocina</option>
                                <option value="barista">Barista</option>
                            </select>
                        </div>
                        <div class="flex gap-4">
                            <div class="flex-1">
                                <label class="block text-sm font-bold text-brownDark mb-1">Icono (FontAwesome)</label>
                                <div class="flex gap-2 relative">
                                    <div class="relative flex-1">
                                        <input type="text" name="icono" id="editCategoriaIcono" class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-[#efe7db] bg-[#FCFAF7] focus:outline-none focus:ring-2 focus:ring-mintGreen focus:border-transparent transition-all">
                                        <i class="fa-solid fa-tags absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400" id="preview-editCategoriaIcono"></i>
                                    </div>
                                    <button type="button" class="px-3 bg-mintGreen/10 text-mintGreen rounded-xl hover:bg-mintGreen/20 transition-colors" onclick="toggleIconPicker('editCategoriaIcono')">
                                        <i class="fa-solid fa-grip"></i>
                                    </button>
                                    <div id="picker-editCategoriaIcono" class="hidden absolute z-[100] bg-white border border-gray-200 shadow-xl rounded-xl p-3 w-64 mt-12 grid grid-cols-5 gap-2 max-h-48 overflow-y-auto top-0 left-0"></div>
                                </div>
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
                    <div class="px-6 py-4 bg-[#FCFAF7] border-t border-[#efe7db] flex justify-end gap-3 rounded-b-2xl">
                        <button type="button" class="px-5 py-2.5 rounded-xl text-brownDark font-bold hover:bg-[#F5EEE5] transition-colors" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-brownDark text-white font-bold hover:bg-[#362018] transition-colors shadow-sm"><i class="fa-solid fa-floppy-disk me-2"></i>Actualizar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Importar Menú (IA) -->
<div class="modal fade" id="modalImportarMenu" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-2xl border-0 shadow-lg">
            <div class="modal-header border-b border-[#efe7db] bg-[#1A73E8] bg-opacity-10 rounded-t-2xl px-6 py-4">
                <h5 class="modal-title font-bold text-[#1A73E8] text-xl flex items-center gap-2">
                    <i class="fa-solid fa-wand-magic-sparkles"></i> Importar Menú con IA
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formImportarMenu" action="<?= BASE_URL ?>public/api/importarMenuController.php" method="POST" enctype="multipart/form-data">
                <div class="modal-body p-6">
                    <div class="bg-blue-50 text-blue-800 p-4 rounded-xl text-sm mb-4 border border-blue-100">
                        <i class="fa-solid fa-circle-info mr-2"></i>
                        Sube el menú de tu restaurante en formato PDF. La Inteligencia Artificial de Google extraerá automáticamente las categorías, productos, precios y modificadores.
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-bold text-brownDark mb-2">Archivo PDF del Menú</label>
                        <div class="border-2 border-dashed border-gray-300 rounded-xl p-8 text-center bg-gray-50 hover:bg-gray-100 transition-colors cursor-pointer" onclick="document.getElementById('menuPdfFile').click()">
                            <i class="fa-solid fa-file-pdf text-4xl text-red-500 mb-3"></i>
                            <p class="text-brownDark font-medium m-0">Haz clic para seleccionar el PDF</p>
                            <p class="text-xs text-gray-500 mt-1" id="menuPdfFileName">Máximo 5MB</p>
                            <input type="file" name="menu_pdf" id="menuPdfFile" accept="application/pdf" class="hidden" required onchange="document.getElementById('menuPdfFileName').textContent = this.files[0].name">
                        </div>
                    </div>
                    
                    <div id="aiLoadingIndicator" class="hidden text-center py-4">
                        <div class="spinner-border text-[#1A73E8] mb-2" role="status"></div>
                        <p class="text-sm text-brownSoft font-medium m-0">La IA está leyendo y procesando tu menú...</p>
                        <p class="text-xs text-gray-400">Esto puede tardar unos 10-20 segundos.</p>
                    </div>
                </div>
                <div class="modal-footer border-t border-[#efe7db] p-4 bg-gray-50 rounded-b-2xl">
                    <button type="button" class="btn text-brownSoft hover:bg-gray-200 rounded-xl px-4 py-2 font-medium" data-bs-dismiss="modal" id="btnCancelImport">Cancelar</button>
                    <button type="submit" class="bg-[#1A73E8] hover:bg-[#1557B0] text-white rounded-xl px-6 py-2 font-bold transition-all shadow-sm" id="btnSubmitImport">
                        Procesar Menú
                    </button>
                    <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white rounded-xl px-6 py-2 font-bold transition-all shadow-sm hidden" id="btnRetryImport">
                        <i class="fa-solid fa-rotate-right mr-2"></i>Reintentar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Nuevo Grupo -->
<div class="modal fade" id="modalNuevoGrupo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-2xl border-0 shadow-lg">
            <div class="modal-header border-b border-[#efe7db] bg-cream rounded-t-2xl px-6 py-4">
                <h5 class="modal-title font-bold text-brownDark text-xl"><i class="fa-solid fa-sliders text-mintGreen mr-2"></i> Crear Grupo de Modificadores</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formNuevoGrupo" action="<?= BASE_URL ?>public/api/nuevoGrupoModificador.php" method="POST">
                <div class="modal-body p-6">
                    <!-- Configuración del Grupo -->
                    <div class="bg-gray-50 rounded-xl p-4 mb-6 border border-gray-100">
                        <h4 class="text-sm font-bold text-brownDark mb-3">1. Configuración Básica</h4>
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="block text-sm font-bold text-brownDark mb-1">Nombre del Grupo</label>
                                <input type="text" name="nombre" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-mintGreen focus:border-transparent" placeholder="Ej. Tipo de Leche, Tamaños..." required>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" name="requerido" id="reqSwitch" value="1">
                                    <label class="form-check-label font-medium text-gray-700" for="reqSwitch">¿Es obligatorio seleccionar uno?</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" name="seleccion_multiple" id="mulSwitch" value="1">
                                    <label class="form-check-label font-medium text-gray-700" for="mulSwitch">¿Permitir selección múltiple?</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Opciones Dinámicas -->
                    <div class="mb-6">
                        <div class="flex justify-between items-end mb-3">
                            <h4 class="text-sm font-bold text-brownDark">2. Opciones y Precios Extras</h4>
                            <button type="button" id="btnAgregarOpcion" class="text-mintGreen hover:text-[#6CA38B] font-bold text-sm bg-mintGreen bg-opacity-10 px-3 py-1.5 rounded-lg transition-colors">
                                <i class="fa-solid fa-plus"></i> Agregar fila
                            </button>
                        </div>
                        <div id="opcionesContainer" class="space-y-3">
                            <!-- Fila base -->
                            <div class="flex gap-3 items-start">
                                <div class="flex-grow">
                                    <input type="text" name="opciones_nombre[]" class="w-full px-4 py-2 rounded-xl border border-gray-300 text-sm focus:ring-2 focus:ring-mintGreen" placeholder="Nombre (Ej. Almendra)" required>
                                </div>
                                <div class="w-1/3">
                                    <input type="number" step="0.01" name="opciones_precio[]" class="w-full px-4 py-2 rounded-xl border border-gray-300 text-sm focus:ring-2 focus:ring-mintGreen" placeholder="Precio Extra (₡)" value="0" required>
                                </div>
                                <div class="w-10 flex justify-center items-center pt-2 text-gray-300">
                                    <i class="fa-solid fa-trash"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Asignación Masiva a Categorías -->
                    <div class="bg-gray-50 rounded-xl p-4 border border-gray-100 mb-4">
                        <h4 class="text-sm font-bold text-brownDark mb-3">3. Asignación a Categorías (Aplica a todos sus productos)</h4>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-2 max-h-[150px] overflow-y-auto pr-2">
                            <?php foreach ($categorias as $cat): ?>
                                <label class="flex items-center gap-2 p-2 bg-white border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                                    <input type="checkbox" name="categorias[]" value="<?= $cat['id'] ?>" class="text-mintGreen rounded focus:ring-mintGreen focus:ring-2">
                                    <span class="text-sm text-gray-700 font-medium"><?= htmlspecialchars($cat['nombre']) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- Asignación Específica a Productos -->
                    <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                        <div class="flex justify-between items-center mb-3">
                            <div>
                                <h4 class="text-sm font-bold text-brownDark mb-1">4. Asignación Individual a Productos</h4>
                                <p class="text-xs text-gray-500">Usa esta opción si el modificador solo aplica a productos específicos (Ej. "Shot Extra" solo para Cafés Americanos).</p>
                            </div>
                            <div class="relative w-48">
                                <input type="text" id="search-new-products" class="w-full pl-8 pr-3 py-1.5 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-mintGreen focus:border-transparent" placeholder="Buscar producto...">
                                <i class="fas fa-search absolute left-2.5 top-1/2 transform -translate-y-1/2 text-gray-400 text-xs"></i>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-2 max-h-[150px] overflow-y-auto pr-2" id="newProductsContainer">
                            <?php foreach ($productos as $p): ?>
                                <label class="flex items-center gap-2 p-2 bg-white border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                                    <input type="checkbox" name="productos[]" value="<?= $p['id'] ?>" class="text-mintGreen rounded focus:ring-mintGreen focus:ring-2">
                                    <span class="text-sm text-gray-700 font-medium"><?= htmlspecialchars($p['nombre']) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                </div>
                <div class="modal-footer border-t border-[#efe7db] p-4 bg-gray-50 rounded-b-2xl">
                    <button type="button" class="btn text-brownSoft hover:bg-gray-200 rounded-xl px-4 py-2 font-medium" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="bg-mintGreen hover:bg-[#6CA38B] text-white rounded-xl px-6 py-2 font-bold transition-all shadow-sm">Guardar Grupo</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Editar Grupo -->
<div class="modal fade" id="modalEditarGrupo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-2xl border-0 shadow-lg">
            <div class="modal-header border-b border-[#efe7db] bg-cream rounded-t-2xl px-6 py-4">
                <h5 class="modal-title font-bold text-brownDark text-xl"><i class="fa-solid fa-pen text-mintGreen mr-2"></i> Editar Grupo de Modificadores</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formEditarGrupo" action="<?= BASE_URL ?>public/api/editarGrupoModificador.php" method="POST">
                <input type="hidden" name="id" id="editGrupoId">
                <div class="modal-body p-6">
                    <div class="bg-gray-50 rounded-xl p-4 mb-6 border border-gray-100">
                        <h4 class="text-sm font-bold text-brownDark mb-3">1. Configuración Básica</h4>
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="block text-sm font-bold text-brownDark mb-1">Nombre del Grupo</label>
                                <input type="text" name="nombre" id="editGrupoNombre" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-mintGreen focus:border-transparent" required>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" name="requerido" id="editReqSwitch" value="1">
                                    <label class="form-check-label font-medium text-gray-700" for="editReqSwitch">¿Es obligatorio seleccionar uno?</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" name="seleccion_multiple" id="editMulSwitch" value="1">
                                    <label class="form-check-label font-medium text-gray-700" for="editMulSwitch">¿Permitir selección múltiple?</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-6">
                        <div class="flex justify-between items-end mb-3">
                            <h4 class="text-sm font-bold text-brownDark">2. Opciones y Precios Extras</h4>
                            <button type="button" id="btnEditAgregarOpcion" class="text-mintGreen hover:text-[#6CA38B] font-bold text-sm bg-mintGreen bg-opacity-10 px-3 py-1.5 rounded-lg transition-colors">
                                <i class="fa-solid fa-plus"></i> Agregar fila
                            </button>
                        </div>
                        <div id="editOpcionesContainer" class="space-y-3">
                            <!-- JS insertará las opciones aquí -->
                        </div>
                    </div>

                    <div class="bg-gray-50 rounded-xl p-4 border border-gray-100 mb-4">
                        <h4 class="text-sm font-bold text-brownDark mb-3">3. Asignación a Categorías</h4>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-2 max-h-[150px] overflow-y-auto pr-2" id="editCategoriasContainer">
                            <?php foreach ($categorias as $cat): ?>
                                <label class="flex items-center gap-2 p-2 bg-white border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                                    <input type="checkbox" name="categorias[]" value="<?= $cat['id'] ?>" class="text-mintGreen rounded focus:ring-mintGreen focus:ring-2 edit-cat-cb">
                                    <span class="text-sm text-gray-700 font-medium"><?= htmlspecialchars($cat['nombre']) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                        <div class="flex justify-between items-center mb-3">
                            <h4 class="text-sm font-bold text-brownDark">4. Asignación Individual a Productos</h4>
                            <div class="relative w-48">
                                <input type="text" id="search-edit-products" class="w-full pl-8 pr-3 py-1.5 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-mintGreen focus:border-transparent" placeholder="Buscar producto...">
                                <i class="fas fa-search absolute left-2.5 top-1/2 transform -translate-y-1/2 text-gray-400 text-xs"></i>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-2 max-h-[150px] overflow-y-auto pr-2" id="editProductosContainer">
                            <?php foreach ($productos as $p): ?>
                                <label class="flex items-center gap-2 p-2 bg-white border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                                    <input type="checkbox" name="productos[]" value="<?= $p['id'] ?>" class="text-mintGreen rounded focus:ring-mintGreen focus:ring-2 edit-prod-cb">
                                    <span class="text-sm text-gray-700 font-medium"><?= htmlspecialchars($p['nombre']) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                </div>
                <div class="modal-footer border-t border-[#efe7db] p-4 bg-gray-50 rounded-b-2xl">
                    <button type="button" class="btn text-brownSoft hover:bg-gray-200 rounded-xl px-4 py-2 font-medium" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="bg-mintGreen hover:bg-[#6CA38B] text-white rounded-xl px-6 py-2 font-bold transition-all shadow-sm">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Lightbox Image Preview Modal -->
<div class="modal fade" id="modalImagePreview" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content bg-transparent border-0 shadow-none">
            <div class="modal-header border-0 flex justify-end">
                <button type="button" class="text-white text-3xl opacity-80 hover:opacity-100 transition-opacity drop-shadow-md" data-bs-dismiss="modal">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="modal-body p-0 flex justify-center items-center">
                <img id="lightboxImage" src="" class="max-w-full max-h-[80vh] rounded-xl object-contain shadow-2xl" alt="Preview">
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/2.1.8/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.1.8/js/dataTables.bootstrap5.min.js"></script>
<script src="<?= BASE_URL ?>public/js/admin-productos.js?v=<?= time() ?>"></script>

<script>
document.getElementById('formImportarMenu').addEventListener('submit', function(e) {
    document.getElementById('btnSubmitImport').classList.add('hidden');
    document.getElementById('btnCancelImport').classList.add('hidden');
    document.getElementById('aiLoadingIndicator').classList.remove('hidden');
});
</script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
</html>
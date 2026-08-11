<?php
require_once __DIR__ . "/../../middleware/auth.php";
require_once __DIR__ . "/../../middleware/roles.php";
require_once __DIR__ . "/../../config/rutas.php";
require_once __DIR__ . "/../../model/Usuarios.php";
verificarRol([1]);

$usuarios = Usuarios::listar();
$roles = Usuarios::listarRoles();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuarios | La Comanda</title>

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
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.datatables.net/2.1.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/style.css">

    <style>
        .dataTables_wrapper .row { margin-bottom: 1rem; align-items: center; }
        .dataTables_filter input { border-radius: 0.75rem !important; border: 1px solid #efe7db !important; padding: 0.4rem 1rem !important; }
        .dataTables_length select { border-radius: 0.5rem !important; border: 1px solid #efe7db !important; }
        table.dataTable>thead>tr>th { border-bottom: 1px solid #efe7db; color: #8D6E63; font-weight: 600; }
        table.dataTable>tbody>tr>td { border-color: #efe7db; }
        .modal-backdrop { z-index: 40 !important; }
        .modal { z-index: 50 !important; }
    </style>
</head>

<body class="custom-beige min-h-screen font-sans">
    
    <?php require_once ROOT_PATH . "/views/admin/adminNavbar.php"; ?>

    <div class="flex pt-16 min-h-screen">
        <main class="flex-1 p-6 w-full max-w-7xl mx-auto">
            
            <div class="bg-gradient-to-r from-[#8D6E63] to-[#4E342E] rounded-3xl shadow-card p-8 mb-8 text-white flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                <div>
                    <p class="uppercase tracking-[0.2em] text-sm text-beigeSoft mb-2">Administración</p>
                    <h1 class="text-3xl md:text-4xl font-extrabold mb-2">Gestión de Usuarios</h1>
                    <p class="text-sm md:text-base text-white/85 max-w-2xl">
                        Administra los accesos al sistema, roles y credenciales del personal de La Comanda.
                    </p>
                </div>
                <div class="flex flex-col sm:flex-row gap-3">
                    <button type="button" class="bg-white text-brownDark hover:bg-gray-50 px-5 py-3 rounded-xl shadow-sm transition-colors font-semibold flex items-center justify-center gap-2" data-bs-toggle="modal" data-bs-target="#modalNuevoUsuario">
                        <i class="fa-solid fa-user-plus"></i> Agregar usuario
                    </button>
                </div>
            </div>

            <!-- Alertas -->
            <div class="w-full mb-6 space-y-3">
                <?php if (isset($_GET["created"])): ?>
                    <div class="bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-xl p-4 flex items-center gap-3 font-semibold"><i class="fa-solid fa-circle-check text-xl"></i> Usuario creado correctamente.</div>
                <?php endif; ?>
                <?php if (isset($_GET["updated"])): ?>
                    <div class="bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-xl p-4 flex items-center gap-3 font-semibold"><i class="fa-solid fa-circle-check text-xl"></i> Usuario actualizado correctamente.</div>
                <?php endif; ?>
                <?php if (isset($_GET["deleted"])): ?>
                    <div class="bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-xl p-4 flex items-center gap-3 font-semibold"><i class="fa-solid fa-circle-check text-xl"></i> Usuario eliminado correctamente.</div>
                <?php endif; ?>
                <?php if (isset($_GET["error"])): ?>
                    <div class="bg-red-50 text-red-700 border border-red-200 rounded-xl p-4 flex items-center gap-3 font-semibold">
                        <i class="fa-solid fa-triangle-exclamation text-xl"></i> 
                        <?php 
                        $err = $_GET["error"];
                        if ($err === "self_delete") echo "No puedes eliminar tu propio usuario.";
                        elseif ($err === "delete_failed") echo "No se pudo eliminar el usuario.";
                        elseif ($err === "root_delete") echo "El usuario principal no puede ser eliminado.";
                        else echo htmlspecialchars($err);
                        ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="bg-white rounded-2xl shadow-card p-6 border border-[#efe7db]">
                <div class="overflow-x-auto pb-4">
                    <table id="tablaUsuarios" class="w-full text-sm align-middle">
                        <thead>
                            <tr class="text-brownSoft text-left border-b border-[#efe7db]">
                                <th class="pb-3 font-semibold min-w-[200px]">Nombre</th>
                                <th class="pb-3 font-semibold min-w-[200px]">Email</th>
                                <th class="pb-3 font-semibold min-w-[120px]">Rol</th>
                                <th class="pb-3 font-semibold text-right min-w-[100px]">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#efe7db]">
                            <?php foreach ($usuarios as $u): ?>
                            <tr class="hover:bg-[#FCFAF7] transition-colors">
                                <td class="py-3 pr-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-[#F5EEE5] flex items-center justify-center text-brownSoft shadow-sm">
                                            <i class="fa-solid fa-user"></i>
                                        </div>
                                        <div>
                                            <p class="font-bold text-brownDark text-base"><?= htmlspecialchars($u["nombre"] . " " . $u["apellido"]) ?></p>
                                            <p class="text-[11px] text-brownSoft font-semibold tracking-wide">ID: <?= (int)$u["id"] ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 px-2 font-medium text-brownDark">
                                    <?= htmlspecialchars($u["email"]) ?>
                                </td>
                                <td class="py-3 px-2">
                                    <span class="px-3 py-1 bg-mintGreen text-white rounded-lg text-xs font-bold shadow-sm inline-block">
                                        <?= htmlspecialchars($u["rol_nombre"] ?? ("Rol " . (int)$u["rol_id"])) ?>
                                    </span>
                                </td>
                                <td class="py-3 pl-3 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button type="button" class="w-9 h-9 inline-flex items-center justify-center rounded-xl bg-white border border-[#efe7db] text-brownDark hover:bg-[#F5EEE5] hover:border-[#e8dccb] shadow-sm transition-all btn-editar-usuario"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalEditarUsuario"
                                            data-id="<?= (int)$u['id'] ?>"
                                            data-nombre="<?= htmlspecialchars($u['nombre'], ENT_QUOTES, 'UTF-8') ?>"
                                            data-apellido="<?= htmlspecialchars($u['apellido'], ENT_QUOTES, 'UTF-8') ?>"
                                            data-email="<?= htmlspecialchars($u['email'], ENT_QUOTES, 'UTF-8') ?>"
                                            data-rol_id="<?= (int)$u['rol_id'] ?>">
                                            <i class="fa-solid fa-pen text-sm"></i>
                                        </button>
                                        <?php if (!in_array((int)$u["id"], [1, 2, 3, 4])): ?>
                                        <form class="inline-block form-eliminar-usuario" method="POST" action="<?= BASE_URL ?>public/api/eliminarUsuario.php">
                                            <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                                            <button type="submit" class="w-9 h-9 inline-flex items-center justify-center rounded-xl bg-white border border-red-100 text-red-500 hover:bg-red-50 hover:border-red-200 shadow-sm transition-all">
                                                <i class="fa-solid fa-trash text-sm"></i>
                                            </button>
                                        </form>
                                        <?php else: ?>
                                        <button class="w-9 h-9 inline-flex items-center justify-center rounded-xl bg-gray-50 border border-gray-200 text-gray-400 shadow-sm" disabled title="Usuario protegido">
                                            <i class="fa-solid fa-lock text-sm"></i>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </main>
    </div>

    <!-- Modal Nuevo Usuario -->
    <div class="modal fade" id="modalNuevoUsuario" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 rounded-2xl shadow-card overflow-hidden">
                <form id="formNuevoUsuario" method="POST" action="<?= BASE_URL ?>public/api/nuevoUsuario.php">
                    <div class="bg-[#F8F5F0] px-6 py-4 border-b border-[#efe7db] flex justify-between items-center">
                        <div>
                            <h3 class="text-xl font-extrabold text-brownDark">Nuevo usuario</h3>
                        </div>
                        <button type="button" class="text-brownSoft hover:text-brownDark transition-colors" data-bs-dismiss="modal"><i class="fa-solid fa-xmark text-xl"></i></button>
                    </div>
                    
                    <div class="p-6 bg-white">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-brownDark mb-1">Nombre</label>
                                <input type="text" name="nombre" class="w-full px-4 py-2.5 rounded-xl border border-[#efe7db] bg-[#FCFAF7] focus:outline-none focus:ring-2 focus:ring-[#8D6E63] transition-all" required>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-brownDark mb-1">Apellido</label>
                                <input type="text" name="apellido" class="w-full px-4 py-2.5 rounded-xl border border-[#efe7db] bg-[#FCFAF7] focus:outline-none focus:ring-2 focus:ring-[#8D6E63] transition-all" required>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-bold text-brownDark mb-1">Correo Electrónico</label>
                                <input type="email" name="email" class="w-full px-4 py-2.5 rounded-xl border border-[#efe7db] bg-[#FCFAF7] focus:outline-none focus:ring-2 focus:ring-[#8D6E63] transition-all" required>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-bold text-brownDark mb-1">Rol</label>
                                <select name="rol_id" class="w-full px-4 py-2.5 rounded-xl border border-[#efe7db] bg-[#FCFAF7] focus:outline-none focus:ring-2 focus:ring-[#8D6E63] transition-all" required>
                                    <option value="">Seleccione un rol</option>
                                    <?php foreach ($roles as $r): ?>
                                        <option value="<?= (int)$r['id'] ?>"><?= htmlspecialchars($r['nombre']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-brownDark mb-1">Contraseña</label>
                                <input type="password" name="password" id="nuevoUsuarioPassword" class="w-full px-4 py-2.5 rounded-xl border border-[#efe7db] bg-[#FCFAF7] focus:outline-none focus:ring-2 focus:ring-[#8D6E63] transition-all" required minlength="8">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-brownDark mb-1">Confirmar contraseña</label>
                                <input type="password" name="password_confirm" id="nuevoUsuarioPasswordConfirm" class="w-full px-4 py-2.5 rounded-xl border border-[#efe7db] bg-[#FCFAF7] focus:outline-none focus:ring-2 focus:ring-[#8D6E63] transition-all" required minlength="8">
                            </div>
                        </div>
                    </div>
                    <div class="px-6 py-4 bg-[#FCFAF7] border-t border-[#efe7db] flex justify-end gap-3">
                        <button type="button" class="px-5 py-2.5 rounded-xl text-brownDark font-bold hover:bg-[#F5EEE5] transition-colors" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-brownDark text-white font-bold hover:bg-[#362018] transition-colors shadow-sm"><i class="fa-solid fa-floppy-disk me-2"></i>Crear usuario</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Editar Usuario -->
    <div class="modal fade" id="modalEditarUsuario" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 rounded-2xl shadow-card overflow-hidden">
                <form id="formEditarUsuario" method="POST" action="<?= BASE_URL ?>public/api/editarUsuario.php">
                    <input type="hidden" name="id" id="editUsuarioId">
                    <div class="bg-[#F8F5F0] px-6 py-4 border-b border-[#efe7db] flex justify-between items-center">
                        <div>
                            <h3 class="text-xl font-extrabold text-brownDark">Editar usuario</h3>
                        </div>
                        <button type="button" class="text-brownSoft hover:text-brownDark transition-colors" data-bs-dismiss="modal"><i class="fa-solid fa-xmark text-xl"></i></button>
                    </div>
                    
                    <div class="p-6 bg-white">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-brownDark mb-1">Nombre</label>
                                <input type="text" name="nombre" id="editUsuarioNombre" class="w-full px-4 py-2.5 rounded-xl border border-[#efe7db] bg-[#FCFAF7] focus:outline-none focus:ring-2 focus:ring-[#8D6E63] transition-all" required>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-brownDark mb-1">Apellido</label>
                                <input type="text" name="apellido" id="editUsuarioApellido" class="w-full px-4 py-2.5 rounded-xl border border-[#efe7db] bg-[#FCFAF7] focus:outline-none focus:ring-2 focus:ring-[#8D6E63] transition-all" required>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-bold text-brownDark mb-1">Correo Electrónico</label>
                                <input type="email" name="email" id="editUsuarioEmail" class="w-full px-4 py-2.5 rounded-xl border border-[#efe7db] bg-[#FCFAF7] focus:outline-none focus:ring-2 focus:ring-[#8D6E63] transition-all" required>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-bold text-brownDark mb-1">Rol</label>
                                <select name="rol_id" id="editUsuarioRol" class="w-full px-4 py-2.5 rounded-xl border border-[#efe7db] bg-[#FCFAF7] focus:outline-none focus:ring-2 focus:ring-[#8D6E63] transition-all" required>
                                    <option value="">Seleccione un rol</option>
                                    <?php foreach ($roles as $r): ?>
                                        <option value="<?= (int)$r['id'] ?>"><?= htmlspecialchars($r['nombre']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="mt-6 pt-4 border-t border-[#efe7db]">
                            <h4 class="font-bold text-brownDark mb-3"><i class="fa-solid fa-key me-2"></i>Cambiar Contraseña (Opcional)</h4>
                            <p class="text-xs text-brownSoft mb-4">Si llenas estos campos, se solicitará tu contraseña actual de administrador por seguridad.</p>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-bold text-brownDark mb-1">Nueva Contraseña</label>
                                    <input type="password" name="password" id="editUsuarioPassword" class="w-full px-4 py-2.5 rounded-xl border border-[#efe7db] bg-[#FCFAF7] focus:outline-none focus:ring-2 focus:ring-[#8D6E63] transition-all" minlength="8">
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-brownDark mb-1">Confirmar Nueva Contraseña</label>
                                    <input type="password" name="password2" id="editUsuarioPassword2" class="w-full px-4 py-2.5 rounded-xl border border-[#efe7db] bg-[#FCFAF7] focus:outline-none focus:ring-2 focus:ring-[#8D6E63] transition-all" minlength="8">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="px-6 py-4 bg-[#FCFAF7] border-t border-[#efe7db] flex justify-end gap-3">
                        <button type="button" class="px-5 py-2.5 rounded-xl text-brownDark font-bold hover:bg-[#F5EEE5] transition-colors" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-brownDark text-white font-bold hover:bg-[#362018] transition-colors shadow-sm"><i class="fa-solid fa-floppy-disk me-2"></i>Actualizar usuario</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/2.1.8/js/dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/2.1.8/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        const BASE_URL = '<?= BASE_URL ?>';
    </script>
    <script src="<?= BASE_URL ?>public/js/admin-usuarios.js?v=<?= time() ?>"></script>
</body>
</html>

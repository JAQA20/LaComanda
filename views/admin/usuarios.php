<?php
require_once __DIR__ . "/../../middleware/auth.php";
require_once __DIR__ . "/../../middleware/roles.php";
require_once __DIR__ . "/../../config/rutas.php";
require_once __DIR__ . "/../../model/Usuarios.php";
require_once __DIR__ . "/../../model/SesionesActivas.php";
verificarRol([1]);

$usuarios = Usuarios::listar();
$sesionesActivas = SesionesActivas::listarActivas();
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
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/admin-usuarios.css">
</head>

<body class="bg-comanda">

    <?php require_once ROOT_PATH . "/views/admin/adminNavbar.php"; ?>

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

                <a href="<?= BASE_URL ?>views/admin/nuevoUsuario.php" class="btn btn-mint px-3 py-2">
                    <i class="fa-solid fa-user-plus me-2"></i>
                    Agregar nuevo usuario
                </a>
            </div>

            <div class="card card-comanda shadow-sm mb-4">
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
                                                href="<?= BASE_URL ?>views/admin/editarUsuario.php?id=<?= (int)$u['id'] ?>"
                                                title="Editar">
                                                <i class="fa-solid fa-pen"></i>
                                            </a>

                                            <!-- Eliminar -->
                                            <?php if ((int)$u["id"] !== 1 && (int)$u["id"] !== 2 && (int)$u["id"] !== 3 && (int)$u["id"] !== 4): ?>
                                                <form class="d-inline form-eliminar-usuario"
                                                    method="POST"
                                                    action="<?= BASE_URL ?>public/api/eliminarUsuario.php">
                                                    <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                                                    <button type="submit"
                                                        class="btn btn-sm btn-light text-danger"
                                                        title="Eliminar">
                                                        <i class="fa-solid fa-trash"></i>
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <button class="btn btn-sm btn-light text-secondary"
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

            <div class="card card-comanda shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                        <div>
                            <h2 class="h5 mb-1 text-brown fw-bold">Usuarios conectados</h2>
                            <div class="text-muted small">Monitoreo de sesiones activas en tiempo real</div>
                        </div>
                        <span id="contador-usuarios-activos" class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2">
                            <?= count($sesionesActivas) ?> activos
                        </span>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="tablaUsuariosConectados">
                            <thead>
                                <tr>
                                    <th style="min-width:220px;">Usuario</th>
                                    <th style="min-width:220px;">Email</th>
                                    <th style="min-width:140px;">Rol</th>
                                    <th style="min-width:180px;">Hora de login</th>
                                    <th style="min-width:180px;">Última actividad</th>
                                    <th style="min-width:140px;">Estado</th>
                                </tr>
                            </thead>
                            <tbody id="tabla-usuarios-conectados-body">
                                <?php if (empty($sesionesActivas)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            <i class="fa-solid fa-user-clock me-2"></i>
                                            No hay usuarios conectados en este momento.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($sesionesActivas as $sesion): ?>
                                        <?php $estadoActividad = SesionesActivas::obtenerEstadoActividad($sesion['ultima_actividad'] ?? 0); ?>
                                        <?php $detalleSesion = htmlspecialchars(json_encode([
                                            'usuario_id' => (int)($sesion['usuario_id'] ?? 0),
                                            'nombre' => (string)($sesion['nombre'] ?? 'Sin nombre'),
                                            'email' => (string)($sesion['email'] ?? 'Sin email'),
                                            'rol' => SesionesActivas::nombreRol($sesion['rol_id'] ?? 0),
                                            'login_at' => SesionesActivas::formatearHora($sesion['login_at'] ?? null),
                                            'ultima_actividad' => SesionesActivas::formatearHora($sesion['ultima_actividad'] ?? null),
                                            'dispositivo' => SesionesActivas::resumirUserAgent($sesion['user_agent'] ?? ''),
                                            'pagina_actual' => (string)($sesion['pagina_actual'] ?? 'N/A'),
                                            'ip' => (string)($sesion['ip'] ?? 'N/A'),
                                            'estado_label' => $estadoActividad['label']
                                        ], JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8'); ?>
                                        <tr class="fila-sesion-activa" data-sesion="<?= $detalleSesion ?>" style="cursor:pointer;">
                                            <td>
                                                <div class="fw-semibold"><?= htmlspecialchars($sesion['nombre'] ?? 'Sin nombre') ?></div>
                                                <div class="text-muted small">ID usuario: <?= (int)($sesion['usuario_id'] ?? 0) ?></div>
                                            </td>
                                            <td><?= htmlspecialchars($sesion['email'] ?? 'Sin email') ?></td>
                                            <td>
                                                <span class="badge badge-role">
                                                    <?= htmlspecialchars(SesionesActivas::nombreRol($sesion['rol_id'] ?? 0)) ?>
                                                </span>
                                            </td>
                                            <td><?= htmlspecialchars(SesionesActivas::formatearHora($sesion['login_at'] ?? null)) ?></td>
                                            <td><?= htmlspecialchars(SesionesActivas::formatearHora($sesion['ultima_actividad'] ?? null)) ?></td>
                                            <td>
                                                <span class="badge <?= htmlspecialchars($estadoActividad['class']) ?>"><?= htmlspecialchars($estadoActividad['label']) ?></span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <div class="modal fade" id="modalSesionActiva" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-light">
                    <h5 class="modal-title text-brown fw-bold">Detalle de sesión activa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6"><strong>Usuario:</strong><br><span id="modal-sesion-usuario">-</span></div>
                        <div class="col-md-6"><strong>Email:</strong><br><span id="modal-sesion-email">-</span></div>
                        <div class="col-md-6"><strong>Rol:</strong><br><span id="modal-sesion-rol">-</span></div>
                        <div class="col-md-6"><strong>Estado:</strong><br><span id="modal-sesion-estado">-</span></div>
                        <div class="col-md-6"><strong>Hora de login:</strong><br><span id="modal-sesion-login">-</span></div>
                        <div class="col-md-6"><strong>Última actividad:</strong><br><span id="modal-sesion-actividad">-</span></div>
                        <div class="col-md-6"><strong>Hora de logout:</strong><br><span id="modal-sesion-logout">-</span></div>
                        <div class="col-md-6"><strong>Dispositivo:</strong><br><span id="modal-sesion-dispositivo">-</span></div>
                        <div class="col-md-6"><strong>IP:</strong><br><span id="modal-sesion-ip">-</span></div>
                        <div class="col-12"><strong>Página actual:</strong><br><span id="modal-sesion-pagina">-</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php require_once __DIR__ . "/../layout/footer.php"; ?>

    <!-- JS -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- DataTables -->
    <script src="https://cdn.datatables.net/2.1.8/js/dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/2.1.8/js/dataTables.bootstrap5.min.js"></script>

    <!-- SweetAlert2 para confirmación de eliminación -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Script propio -->
    <script src="<?= BASE_URL ?>public/js/admin-usuarios.js"></script>
    <script>
        const API_USUARIOS_CONECTADOS = "<?= BASE_URL ?>public/api/usuariosConectados.php";
        const tablaUsuariosConectadosBody = document.getElementById("tabla-usuarios-conectados-body");
        const contadorUsuariosActivos = document.getElementById("contador-usuarios-activos");
        const modalSesionActiva = new bootstrap.Modal(document.getElementById('modalSesionActiva'));

        function escapeHtml(texto) {
            const div = document.createElement("div");
            div.textContent = texto ?? "";
            return div.innerHTML;
        }

        function renderUsuariosConectados(sesiones) {
            contadorUsuariosActivos.textContent = `${sesiones.length} activos`;

            if (!Array.isArray(sesiones) || sesiones.length === 0) {
                tablaUsuariosConectadosBody.innerHTML = `
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            <i class="fa-solid fa-user-clock me-2"></i>
                            No hay usuarios conectados en este momento.
                        </td>
                    </tr>
                `;
                return;
            }

            tablaUsuariosConectadosBody.innerHTML = sesiones.map(sesion => {
                const payload = encodeURIComponent(JSON.stringify(sesion));
                return `
                    <tr class="fila-sesion-activa" data-sesion="${payload}" style="cursor:pointer;">
                        <td>
                            <div class="fw-semibold">${escapeHtml(sesion.nombre || 'Sin nombre')}</div>
                            <div class="text-muted small">ID usuario: ${escapeHtml(String(sesion.usuario_id || 0))}</div>
                        </td>
                        <td>${escapeHtml(sesion.email || 'Sin email')}</td>
                        <td><span class="badge badge-role">${escapeHtml(sesion.rol || 'Rol')}</span></td>
                        <td>${escapeHtml(sesion.login_at || 'N/A')}</td>
                        <td>${escapeHtml(sesion.ultima_actividad || 'N/A')}</td>
                        <td><span class="badge ${escapeHtml(sesion.estado_class || 'bg-secondary-subtle text-secondary')}">${escapeHtml(sesion.estado_label || 'Inactivo')}</span></td>
                    </tr>
                `;
            }).join('');

            document.querySelectorAll('.fila-sesion-activa').forEach(fila => {
                fila.addEventListener('click', () => {
                    try {
                        const sesion = JSON.parse(decodeURIComponent(fila.dataset.sesion || ''));
                        document.getElementById('modal-sesion-usuario').textContent = sesion.nombre || '-';
                        document.getElementById('modal-sesion-email').textContent = sesion.email || '-';
                        document.getElementById('modal-sesion-rol').textContent = sesion.rol || '-';
                        document.getElementById('modal-sesion-estado').textContent = sesion.estado_label || '-';
                        document.getElementById('modal-sesion-login').textContent = sesion.login_at || '-';
                        document.getElementById('modal-sesion-actividad').textContent = sesion.ultima_actividad || '-';
                        document.getElementById('modal-sesion-logout').textContent = sesion.logout_at || 'Sin logout registrado';
                        document.getElementById('modal-sesion-dispositivo').textContent = sesion.dispositivo || '-';
                        document.getElementById('modal-sesion-ip').textContent = sesion.ip || '-';
                        document.getElementById('modal-sesion-pagina').textContent = sesion.pagina_actual || '-';
                        modalSesionActiva.show();
                    } catch (error) {
                        console.error('Error leyendo detalle de sesión:', error);
                    }
                });
            });
        }

        async function cargarUsuariosConectados() {
            try {
                const response = await fetch(API_USUARIOS_CONECTADOS, {
                    method: "GET",
                    headers: {
                        "Accept": "application/json"
                    },
                    cache: "no-store"
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }

                const data = await response.json();
                if (!data.ok) {
                    throw new Error("Respuesta inválida del backend");
                }

                renderUsuariosConectados(data.sesiones || []);
            } catch (error) {
                console.error("Error cargando usuarios conectados:", error);
            }
        }

        cargarUsuariosConectados();
        setInterval(cargarUsuariosConectados, 8000);
    </script>

</body>

</html>
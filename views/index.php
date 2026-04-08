<?php
require_once __DIR__ . "/../config/env.php";
app_configure_errors();
require_once __DIR__ . "/../middleware/auth.php";
require_once __DIR__ . "/../middleware/roles.php";
require_once __DIR__ . "/../config/rutas.php";

verificarRol([1, 2]); // Admin(1) y Mesero(2)

$isAdminLayout = isset($_SESSION['rol_id']) && (int)$_SESSION['rol_id'] === 1;
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>La Comanda</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        window.FontAwesomeConfig = {
            autoReplaceSvg: 'nest'
        };
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/style.css">
    <a href="../config/rutas.php"></a>

        <link rel="stylesheet" href="<?= BASE_URL ?>public/css/index.css">



</head>

<body class="custom-beige min-h-screen">

    <!-- Navbar -->
    <?php require_once ROOT_PATH . '/views/layout/navbar.php'; ?>

    <!-- Main Content -->
    <div class="pt-20 min-h-screen">
        <div class="max-w-[1760px] mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col xl:flex-row gap-6 items-start">

                <!-- Content Area -->
                <main id="content-area" class="w-full flex-1 min-w-0">

                    <!-- Mesas View -->
                    <div id="mesas-view" class="block">
                        <h1 class="text-brown text-3xl font-bold mb-6">Mesas disponibles</h1>

                        <section class="floor-shell">
                            <div class="floor-layout">
                                <div class="floor-legend-bar" aria-label="Leyenda de estados de mesa">
                                    <div class="floor-legend-item"><span class="sw libre"></span> Disponible</div>
                                    <div class="floor-legend-item"><span class="sw pendiente"></span> En cocina</div>
                                    <div class="floor-legend-item"><span class="sw lista"></span> Lista para entregar</div>
                                    <!-- <div class="floor-legend-item"><span class="sw kitchen"></span> Cocina movible</div> -->
                                </div>

                                <div class="floor-canvas">
                                    <div class="restaurant-plan">
                                        <div class="restaurant-floor layout-readonly" id="restaurant-floor">
                                            <div class="draggable kitchen" data-id="kitchen" style="left: 2%; top: 2%;">
                                                <?php if ($isAdminLayout): ?>
                                                    <div class="drag-handle" title="Arrastrar">⠿</div>
                                                <?php endif; ?>
                                                <div class="burners" aria-hidden="true">
                                                    <span></span><span></span>
                                                    <span></span><span></span>
                                                    <span></span><span></span>
                                                    <span></span><span></span>
                                                </div>
                                                <div class="prep" aria-hidden="true"></div>
                                                <div class="sinks" aria-hidden="true">
                                                    <div class="sink"></div>
                                                    <div class="sink"></div>
                                                </div>
                                            </div>

                                            <div class="draggable mesa-card table square" data-id="t1" data-mesa="1" data-shape="square" style="left: 34%; top: 9%;"></div>
                                            <div class="draggable mesa-card table square" data-id="t2" data-mesa="2" data-shape="square" style="left: 47%; top: 9%;"></div>
                                            <div class="draggable mesa-card table rect" data-id="t3" data-mesa="3" data-shape="rect" style="left: 63%; top: 8%;"></div>
                                            <div class="draggable mesa-card table square" data-id="t4" data-mesa="4" data-shape="square" style="left: 34%; top: 26%;"></div>
                                            <div class="draggable mesa-card table square" data-id="t5" data-mesa="5" data-shape="square" style="left: 47%; top: 26%;"></div>
                                            <div class="draggable mesa-card table rect" data-id="t6" data-mesa="6" data-shape="rect" style="left: 75%; top: 31%;"></div>
                                            <div class="draggable mesa-card table square" data-id="t7" data-mesa="7" data-shape="square" style="left: 34%; top: 47%;"></div>
                                            <div class="draggable mesa-card table square" data-id="t8" data-mesa="8" data-shape="square" style="left: 47%; top: 47%;"></div>
                                            <div class="draggable mesa-card table rect" data-id="t9" data-mesa="9" data-shape="rect" style="left: 19%; top: 72%;"></div>
                                            <div class="draggable mesa-card table square" data-id="t10" data-mesa="10" data-shape="square" style="left: 34%; top: 73%;"></div>
                                            <div class="draggable mesa-card table square" data-id="t11" data-mesa="11" data-shape="square" style="left: 47%; top: 73%;"></div>
                                            <div class="draggable mesa-card table rect" data-id="t12" data-mesa="12" data-shape="rect" style="left: 63%; top: 72%;"></div>
                                        </div>
                                    </div>
                                </div>

                                <?php if ($isAdminLayout): ?>
                                    <div class="floor-controls">
                                        <div class="floor-actions">
                                            <button class="floor-btn primary" id="saveLayoutBtn" type="button">Guardar posiciones</button>
                                            <button class="floor-btn ghost" id="resetLayoutBtn" type="button">Restablecer</button>
                                        </div>

                                        <div class="floor-edit-wrap">
                                            <!-- ========JARVIS UPDATE========
                                                 El modo edición ahora inicia apagado para evitar cambios accidentales. -->
                                            <button class="floor-chip" id="editLayoutChip" type="button">Modo edición: OFF</button>
                                            <span class="floor-hint">Desactívalo para evitar mover el plano por accidente.</span>
                                        </div>

                                        <div class="floor-controls-note">Selecciona mesas desde el croquis y usa el asa ⠿ para mover mesas y cocina. Las posiciones ahora se guardan de forma compartida para todo el equipo.</div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </section>
                    </div>

                    <!-- Menu Views -->
                    <div id="menu-view" class="hidden">
                        <h1 id="menu-title" class="text-brown text-3xl font-bold mb-8">Menú</h1>
                        <div id="productos-grid" class="grid grid-cols-1 md:grid-cols-2 2xl:grid-cols-3 gap-6">
                        </div>
                    </div>

                </main>

                <!-- Sidebar - Orden Actual -->
                <?php require_once ROOT_PATH . '/views/layout/ordenActual.php'; ?>

            </div>
        </div>
        <a href="../controller/listarProductosController.php"></a>
    </div>

    <!-- Footer -->
    <?php require_once ROOT_PATH . '/views/layout/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

    

    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;">
        <div id="toastMesaLista" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body" id="toastMesaListaBody">
                    La orden está lista para entregar.
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Cerrar"></button>
            </div>
        </div>
    </div>
    <audio id="audio-notificacion" preload="auto">
        <source src="<?= BASE_URL ?>public/sounds/notificacion.mp3" type="audio/mpeg">
    </audio>

    <script>
        window.INDEX_CONFIG = {
            BASE_URL: "<?= BASE_URL ?>",
            LAYOUT_API_URL: "<?= BASE_URL ?>public/api/layoutMesas.php",
            USER_IS_ADMIN: <?= $isAdminLayout ? 'true' : 'false' ?>
        };
    </script>
    <script src="<?= BASE_URL ?>public/js/index.js"></script>

</body>

</html>
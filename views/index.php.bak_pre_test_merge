<?php
require_once __DIR__ . "/../config/env.php";
app_configure_errors();
require_once __DIR__ . "/../middleware/auth.php";
require_once __DIR__ . "/../middleware/roles.php";
require_once __DIR__ . "/../config/rutas.php";

verificarRol([1, 2]); // Admin(1) y Mesero(2)
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

</head>

<body class="custom-beige min-h-screen">

    <!-- Navbar -->
    <?php require_once ROOT_PATH . '/views/layout/navbar.php'; ?>

    <!-- Main Content -->
    <div class="pt-20 min-h-screen">
        <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col xl:flex-row gap-6 items-start">

                <!-- Content Area -->
                <main id="content-area" class="w-full flex-1 min-w-0">

                    <!-- Mesas View -->
                    <div id="mesas-view" class="block">
                        <h1 class="text-brown text-3xl font-bold mb-8">Mesas disponibles</h1>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4 gap-6">
                            <div class="mesa-card bg-white rounded-xl p-6 shadow-sm border-2 border-transparent hover:border-mint transition-all duration-200 cursor-pointer" data-mesa="1">
                                <div class="text-center">
                                    <div class="w-16 h-16 custom-mint rounded-full flex items-center justify-center mx-auto mb-3">
                                        <i class="fas fa-utensils text-white text-xl"></i>
                                    </div>
                                    <h3 class="text-brown font-semibold text-lg">Mesa 1</h3>
                                    <p class="text-mint text-sm">Disponible</p>
                                </div>
                            </div>

                            <div class="mesa-card bg-white rounded-xl p-6 shadow-sm border-2 border-transparent hover:border-mint transition-all duration-200 cursor-pointer" data-mesa="2">
                                <div class="text-center">
                                    <div class="w-16 h-16 custom-mint rounded-full flex items-center justify-center mx-auto mb-3">
                                        <i class="fas fa-utensils text-white text-xl"></i>
                                    </div>
                                    <h3 class="text-brown font-semibold text-lg">Mesa 2</h3>
                                    <p class="text-mint text-sm">Disponible</p>
                                </div>
                            </div>

                            <div class="mesa-card bg-white rounded-xl p-6 shadow-sm border-2 border-transparent hover:border-mint transition-all duration-200 cursor-pointer" data-mesa="3">
                                <div class="text-center">
                                    <div class="w-16 h-16 custom-mint rounded-full flex items-center justify-center mx-auto mb-3">
                                        <i class="fas fa-utensils text-white text-xl"></i>
                                    </div>
                                    <h3 class="text-brown font-semibold text-lg">Mesa 3</h3>
                                    <p class="text-mint text-sm">Disponible</p>
                                </div>
                            </div>

                            <div class="mesa-card bg-white rounded-xl p-6 shadow-sm border-2 border-transparent hover:border-mint transition-all duration-200 cursor-pointer" data-mesa="4">
                                <div class="text-center">
                                    <div class="w-16 h-16 custom-mint rounded-full flex items-center justify-center mx-auto mb-3">
                                        <i class="fas fa-utensils text-white text-xl"></i>
                                    </div>
                                    <h3 class="text-brown font-semibold text-lg">Mesa 4</h3>
                                    <p class="text-mint text-sm">Disponible</p>
                                </div>
                            </div>

                            <div class="mesa-card bg-white rounded-xl p-6 shadow-sm border-2 border-transparent hover:border-mint transition-all duration-200 cursor-pointer" data-mesa="5">
                                <div class="text-center">
                                    <div class="w-16 h-16 custom-mint rounded-full flex items-center justify-center mx-auto mb-3">
                                        <i class="fas fa-utensils text-white text-xl"></i>
                                    </div>
                                    <h3 class="text-brown font-semibold text-lg">Mesa 5</h3>
                                    <p class="text-mint text-sm">Disponible</p>
                                </div>
                            </div>

                            <div class="mesa-card bg-white rounded-xl p-6 shadow-sm border-2 border-transparent hover:border-mint transition-all duration-200 cursor-pointer" data-mesa="6">
                                <div class="text-center">
                                    <div class="w-16 h-16 custom-mint rounded-full flex items-center justify-center mx-auto mb-3">
                                        <i class="fas fa-utensils text-white text-xl"></i>
                                    </div>
                                    <h3 class="text-brown font-semibold text-lg">Mesa 6</h3>
                                    <p class="text-mint text-sm">Disponible</p>
                                </div>
                            </div>

                            <div class="mesa-card bg-white rounded-xl p-6 shadow-sm border-2 border-transparent hover:border-mint transition-all duration-200 cursor-pointer" data-mesa="7">
                                <div class="text-center">
                                    <div class="w-16 h-16 custom-mint rounded-full flex items-center justify-center mx-auto mb-3">
                                        <i class="fas fa-utensils text-white text-xl"></i>
                                    </div>
                                    <h3 class="text-brown font-semibold text-lg">Mesa 7</h3>
                                    <p class="text-mint text-sm">Disponible</p>
                                </div>
                            </div>

                            <div class="mesa-card bg-white rounded-xl p-6 shadow-sm border-2 border-transparent hover:border-mint transition-all duration-200 cursor-pointer" data-mesa="8">
                                <div class="text-center">
                                    <div class="w-16 h-16 custom-mint rounded-full flex items-center justify-center mx-auto mb-3">
                                        <i class="fas fa-utensils text-white text-xl"></i>
                                    </div>
                                    <h3 class="text-brown font-semibold text-lg">Mesa 8</h3>
                                    <p class="text-mint text-sm">Disponible</p>
                                </div>
                            </div>

                            <div class="mesa-card bg-white rounded-xl p-6 shadow-sm border-2 border-transparent hover:border-mint transition-all duration-200 cursor-pointer" data-mesa="9">
                                <div class="text-center">
                                    <div class="w-16 h-16 custom-mint rounded-full flex items-center justify-center mx-auto mb-3">
                                        <i class="fas fa-utensils text-white text-xl"></i>
                                    </div>
                                    <h3 class="text-brown font-semibold text-lg">Mesa 9</h3>
                                    <p class="text-mint text-sm">Disponible</p>
                                </div>
                            </div>

                            <div class="mesa-card bg-white rounded-xl p-6 shadow-sm border-2 border-transparent hover:border-mint transition-all duration-200 cursor-pointer" data-mesa="10">
                                <div class="text-center">
                                    <div class="w-16 h-16 custom-mint rounded-full flex items-center justify-center mx-auto mb-3">
                                        <i class="fas fa-utensils text-white text-xl"></i>
                                    </div>
                                    <h3 class="text-brown font-semibold text-lg">Mesa 10</h3>
                                    <p class="text-mint text-sm">Disponible</p>
                                </div>
                            </div>

                            <div class="mesa-card bg-white rounded-xl p-6 shadow-sm border-2 border-transparent hover:border-mint transition-all duration-200 cursor-pointer" data-mesa="11">
                                <div class="text-center">
                                    <div class="w-16 h-16 custom-mint rounded-full flex items-center justify-center mx-auto mb-3">
                                        <i class="fas fa-utensils text-white text-xl"></i>
                                    </div>
                                    <h3 class="text-brown font-semibold text-lg">Mesa 11</h3>
                                    <p class="text-mint text-sm">Disponible</p>
                                </div>
                            </div>

                            <div class="mesa-card bg-white rounded-xl p-6 shadow-sm border-2 border-transparent hover:border-mint transition-all duration-200 cursor-pointer" data-mesa="12">
                                <div class="text-center">
                                    <div class="w-16 h-16 custom-mint rounded-full flex items-center justify-center mx-auto mb-3">
                                        <i class="fas fa-utensils text-white text-xl"></i>
                                    </div>
                                    <h3 class="text-brown font-semibold text-lg">Mesa 12</h3>
                                    <p class="text-mint text-sm">Disponible</p>
                                </div>
                            </div>
                        </div>
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

    <script>
        const BASE_URL = "<?= BASE_URL ?>";
        let mesaActual = null;
        let ordenActual = [];
        let totalOrden = 0;

        // Estado visual por mesa: libre | pendiente | lista
        let mesaEstados = JSON.parse(localStorage.getItem('mesaEstados') || '{}');

        function guardarEstadoMesas() {
            localStorage.setItem('mesaEstados', JSON.stringify(mesaEstados));
        }

        function aplicarSeleccionMesa(numeroMesa) {
            numeroMesa = String(numeroMesa);
            mesaActual = numeroMesa;

            const mesaActualSpan = document.getElementById('mesa-actual');
            if (mesaActualSpan) {
                mesaActualSpan.textContent = `Mesa ${numeroMesa}`;
            }

            document.querySelectorAll('.mesa-card').forEach(card => {
                card.style.border = "2px solid transparent";
            });

            const cardSeleccionada = document.querySelector(`.mesa-card[data-mesa="${numeroMesa}"]`);
            if (cardSeleccionada) {
                cardSeleccionada.style.border = "2px solid #70A38F";
            }

            // Limpiar campo de notas cuando se selecciona una mesa
            const notasField = document.getElementById('notas-orden');
            if (notasField) notasField.value = '';

            actualizarBotones();
        }

        function seleccionarMesa(numeroMesa) {
            numeroMesa = String(numeroMesa);

            // Si es la misma mesa, no hacer nada
            if (mesaActual === numeroMesa) return;

            // Si ya hay productos y quiere cambiar a otra mesa, pedir confirmación
            if (mesaActual && ordenActual.length > 0 && mesaActual !== numeroMesa) {
                Swal.fire({
                    title: '¿Cambiar de mesa?',
                    html: `
                La orden actual tiene productos seleccionados.<br><br>
                <strong>No se eliminarán</strong>, pero quedarán asignados a la nueva mesa.<br><br>
                Cambiar de <strong>Mesa ${mesaActual}</strong> a <strong>Mesa ${numeroMesa}</strong>.
            `,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, cambiar mesa',
                    cancelButtonText: 'Cancelar',
                    reverseButtons: true,
                    confirmButtonColor: '#70A38F',
                    cancelButtonColor: '#6c757d'
                }).then((result) => {
                    if (result.isConfirmed) {
                        aplicarSeleccionMesa(numeroMesa);
                    }
                });

                return;
            }

            aplicarSeleccionMesa(numeroMesa);
        }

        function actualizarEstadoMesa(numeroMesa) {
            numeroMesa = String(numeroMesa);

            const card = document.querySelector(`.mesa-card[data-mesa="${numeroMesa}"]`);
            if (!card) return;

            const estado = mesaEstados[numeroMesa] || "libre";

            if (estado === "pendiente") {
                card.innerHTML = `
            <div class="text-center">
                <div class="w-16 h-16 custom-brown rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-utensils text-beige text-xl"></i>
                </div>
                <h3 class="text-brown font-semibold text-lg">Mesa ${numeroMesa}</h3>
                <p class="text-brown text-sm">En cocina</p>
            </div>
        `;
            } else if (estado === "lista") {
                card.innerHTML = `
            <div class="text-center">
                <div class="w-16 h-16 bg-green-600 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-bell text-white text-xl"></i>
                </div>
                <h3 class="text-brown font-semibold text-lg">Mesa ${numeroMesa}</h3>
                <p class="text-green-600 text-sm font-medium">Lista para entregar</p>
                <button class="btn-entregar w-full py-2 custom-mint text-white rounded-lg hover-mint-bg font-medium mt-3">
                    Entregar orden
                </button>
            </div>
        `;
            } else {
                card.innerHTML = `
            <div class="text-center">
                <div class="w-16 h-16 custom-mint rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-utensils text-white text-xl"></i>
                </div>
                <h3 class="text-brown font-semibold text-lg">Mesa ${numeroMesa}</h3>
                <p class="text-mint text-sm">Disponible</p>
            </div>
        `;
            }

            // Mantener borde si está seleccionada
            if (mesaActual === numeroMesa) {
                card.style.border = "2px solid #70A38F";
            } else {
                card.style.border = "2px solid transparent";
            }
        }

        function actualizarTodasLasMesas() {
            document.querySelectorAll('.mesa-card').forEach(card => {
                const mesa = card.getAttribute('data-mesa');
                if (mesa) actualizarEstadoMesa(mesa);
            });
        }

        function mostrarToastMesaLista(numeroMesa) {
            const toastEl = document.getElementById('toastMesaLista');
            const toastBody = document.getElementById('toastMesaListaBody');

            if (!toastEl || !toastBody) return;

            toastBody.textContent = `La orden de la Mesa ${numeroMesa} está lista para entregar.`;

            const toast = new bootstrap.Toast(toastEl, {
                delay: 4000
            });

            toast.show();
        }

        function reproducirSonidoNotificacion() {
            const audio = document.getElementById('audio-notificacion');
            if (!audio) return;

            audio.currentTime = 0;
            audio.play().catch(error => {
                console.warn("No se pudo reproducir el sonido automáticamente:", error);
            });
        }

        async function sincronizarEstadosMesas(mostrarNotificacion = false) {
            try {
                const resp = await fetch(`${BASE_URL}public/api/estadoMesas.php?_=${Date.now()}`);

                if (!resp.ok) {
                    const errorText = await resp.text();
                    console.error("Respuesta 500 de estadoMesas.php:", errorText);
                    throw new Error(`HTTP ${resp.status}`);
                }

                const json = await resp.json();

                if (json.status !== "OK") throw new Error(json.message || "Error obteniendo estados");

                const nuevosEstados = json.data || {};
                const estadosAnteriores = {
                    ...mesaEstados
                };

                // Normalizar: si una mesa no viene en la respuesta, queda libre
                const estadosNormalizados = {};
                document.querySelectorAll('.mesa-card').forEach(card => {
                    const mesa = card.getAttribute('data-mesa');
                    if (!mesa) return;

                    const estado = nuevosEstados[mesa];
                    if (estado === "pendiente" || estado === "lista") {
                        estadosNormalizados[mesa] = estado;
                    } else {
                        estadosNormalizados[mesa] = "libre";
                    }
                });

                mesaEstados = estadosNormalizados;
                guardarEstadoMesas();
                actualizarTodasLasMesas();

                if (mostrarNotificacion) {
                    Object.keys(mesaEstados).forEach(mesa => {
                        if (mesaEstados[mesa] === "lista" && estadosAnteriores[mesa] !== "lista") {
                            mostrarToastMesaLista(mesa);
                            reproducirSonidoNotificacion();
                        }
                    });
                }

            } catch (error) {
                console.error("Error sincronizando estados de mesas:", error);
            }
        }

        async function mostrarProductos(slug, nombreCategoria = "Menú") {
            const menuView = document.getElementById('menu-view');
            const mesasView = document.getElementById('mesas-view');
            const productosGrid = document.getElementById('productos-grid');
            const menuTitle = document.getElementById('menu-title');

            if (!menuView || !mesasView || !productosGrid || !menuTitle) return;

            mesasView.classList.add('hidden');
            menuView.classList.remove('hidden');
            menuTitle.textContent = nombreCategoria;

            productosGrid.innerHTML = `
                <div class="col-span-full text-center text-gray-500 py-10">
                    <i class="fas fa-circle-notch fa-spin text-2xl mb-3"></i>
                    <p>Cargando productos...</p>
                </div>
            `;
            try {

                // const resp = await fetch(`${BASE_URL}controller/listarProductosController.php?categoria=${encodeURIComponent(slug)}`);
                const resp = await fetch(`${BASE_URL}public/api/listarProductos.php?categoria=${encodeURIComponent(slug)}`);
                if (!resp.ok) throw new Error("HTTP " + resp.status);

                const json = await resp.json();
                if (json.status !== "OK") throw new Error(json.message || "Error API");

                const items = json.data || [];

                if (!items.length) {
                    renderPlaceholderSinProductos(nombreCategoria);
                    return;
                }

                productosGrid.innerHTML = items.map(p => {
                    const nombre = p.nombre ?? "";
                    const precio = Number(p.precio ?? 0);
                    const icono = p.icono ?? "fa-box";
                    const nombreSafe = nombre.replace(/'/g, "\\'");

                    return `
                        <div class="bg-white rounded-xl p-6 shadow-sm hover:shadow-md transition-all duration-200">
                            <div class="text-center">
                                <div class="w-16 h-16 custom-mint rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i class="fas ${icono} text-white text-xl"></i>
                                </div>
                                <h3 class="text-brown font-semibold text-lg mb-2">${nombre}</h3>
                                <p class="text-mint font-bold text-xl mb-4">₡${precio.toLocaleString()}</p>
                                <button onclick="agregarProducto('${nombreSafe}', ${precio})"
                                        class="w-full custom-mint text-white py-2 rounded-lg hover-mint-bg transition-all duration-200 flex items-center justify-center">
                                    <i class="fas fa-plus mr-2"></i>
                                    Agregar
                                </button>
                            </div>
                        </div>
                    `;
                }).join("");
            } catch (e) {
                console.error(e);
                productosGrid.innerHTML = `
                    <div class="col-span-full bg-white rounded-xl p-8 shadow-sm border border-red-100">
                        <div class="text-center">
                            <div class="w-16 h-16 bg-red-500 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-triangle-exclamation text-white text-xl"></i>
                            </div>
                            <h3 class="text-brown font-semibold text-xl mb-2">No se pudieron cargar los productos</h3>
                            <p class="text-gray-500">Intenta nuevamente.</p>
                        </div>
                    </div>
                `;
            }
        }

        function renderPlaceholderSinProductos(nombreCategoria = "esta categoría") {
            const productosGrid = document.getElementById('productos-grid');
            if (!productosGrid) return;

            productosGrid.innerHTML = `
                <div class="col-span-full bg-white rounded-xl p-10 shadow-sm border border-gray-100">
                    <div class="text-center">
                        <div class="w-16 h-16 custom-mint rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-box-open text-white text-2xl"></i>
                        </div>
                        <h3 class="text-brown font-semibold text-2xl mb-2">No hay productos registrados</h3>
                        <p class="text-gray-500">
                            Aún no existen productos para <strong>${nombreCategoria}</strong>.
                        </p>
                    </div>
                </div>
            `;
        }

        function mostrarMesas() {
            const menuView = document.getElementById('menu-view');
            const mesasView = document.getElementById('mesas-view');

            if (menuView) menuView.classList.add('hidden');
            if (mesasView) mesasView.classList.remove('hidden');
        }

        function agregarProducto(nombre, precio) {
            if (!mesaActual) {
                Swal.fire({
                    icon: "error",
                    title: "Oops...",
                    text: "Seleccione una mesa primero"
                });
                return;
            }

            const itemExistente = ordenActual.find(item => item.nombre === nombre);
            if (itemExistente) {
                itemExistente.cantidad++;
            } else {
                ordenActual.push({
                    nombre,
                    precio,
                    cantidad: 1
                });
            }

            actualizarOrden();
        }

        function actualizarOrden() {
            const ordenItems = document.getElementById('orden-items');
            const totalElement = document.getElementById('total-orden');

            if (!ordenItems || !totalElement) return;

            if (ordenActual.length === 0) {
                ordenItems.innerHTML = `
                    <div class="text-gray-500 text-center py-8">
                        <i class="fas fa-coffee text-4xl mb-2"></i>
                        <p>Selecciona una mesa y agrega productos</p>
                    </div>
                `;
            } else {
                ordenItems.innerHTML = ordenActual.map((item, index) => `
                    <div class="flex items-center justify-between bg-gray-50 p-3 rounded-lg">
                        <div class="flex-1">
                            <p class="text-brown font-medium">${item.nombre}</p>
                            <p class="text-mint text-sm">₡${item.precio.toLocaleString()} c/u</p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <button onclick="cambiarCantidad(${index}, -1)" class="w-6 h-6 bg-gray-300 rounded-full flex items-center justify-center text-sm">-</button>
                            <span class="text-brown font-medium w-8 text-center">${item.cantidad}</span>
                            <button onclick="cambiarCantidad(${index}, 1)" class="w-6 h-6 custom-mint text-white rounded-full flex items-center justify-center text-sm">+</button>
                        </div>
                    </div>
                `).join('');
            }

            totalOrden = ordenActual.reduce((total, item) => total + (item.precio * item.cantidad), 0);
            totalElement.textContent = `₡${totalOrden.toLocaleString()}`;
            actualizarBotones();
        }

        async function entregarOrden(numeroMesa) {
            const {
                isConfirmed
            } = await Swal.fire({
                title: `¿Marcar la orden de la Mesa ${numeroMesa} como ENTREGADA?`,
                icon: "question",
                showCancelButton: true,
                confirmButtonText: "Sí, entregar",
                cancelButtonText: "Cancelar",
                reverseButtons: true
            });

            if (!isConfirmed) return;

            try {
                const respuesta = await fetch("<?= BASE_URL ?>public/api/entregarOrden.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    body: `mesa=${encodeURIComponent(numeroMesa)}`
                });

                if (!respuesta.ok) {
                    const errorText = await respuesta.text();
                    console.error("Respuesta entregarOrden:", errorText);
                    throw new Error(`HTTP ${respuesta.status}`);
                }

                const result = await respuesta.json();
                console.log("Resultado entregarOrden:", result);

                if (result.status === "OK") {
                    mesaEstados[numeroMesa] = "libre";
                    guardarEstadoMesas();
                    actualizarEstadoMesa(numeroMesa);

                    await Swal.fire({
                        position: "center",
                        icon: "success",
                        html: `<strong>Mesa ${numeroMesa}</strong><br>Orden marcada como ENTREGADA`,
                        showConfirmButton: false,
                        timer: 2000
                    });

                    sincronizarEstadosMesas(false);

                } else {
                    await Swal.fire({
                        icon: "error",
                        title: "Oops...",
                        text: result.message || "No se pudo entregar la orden"
                    });
                }

            } catch (error) {
                console.error("Error en entregarOrden:", error);
                await Swal.fire({
                    icon: "error",
                    title: "Oops...",
                    text: error.message || "Error al entregar la orden"
                });
            }
        }

        function cambiarCantidad(index, cambio) {
            ordenActual[index].cantidad += cambio;
            if (ordenActual[index].cantidad <= 0) {
                ordenActual.splice(index, 1);
            }
            actualizarOrden();
        }

        function actualizarBotones() {
            const eliminarBtn = document.getElementById('eliminar-orden');
            const enviarBtn = document.getElementById('enviar-cocina');

            const tieneOrden = ordenActual.length > 0 && mesaActual;
            if (eliminarBtn) eliminarBtn.disabled = !tieneOrden;
            if (enviarBtn) enviarBtn.disabled = !tieneOrden;
        }

        function eliminarOrden() {
            if (ordenActual.length === 0) return;

            Swal.fire({
                title: "¿Eliminar orden?",
                text: "¿Estás seguro de eliminar toda la orden?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#8B0000",
                cancelButtonColor: "#6c757d",
                confirmButtonText: "Sí, eliminar",
                cancelButtonText: "Cancelar"
            }).then((result) => {
                if (result.isConfirmed) {
                    ordenActual = [];
                    const notasField = document.getElementById('notas-orden');
                    if (notasField) notasField.value = '';
                    actualizarOrden();
                    actualizarBotones();

                    Swal.fire({
                        icon: "success",
                        title: "Orden eliminada",
                        text: "La orden fue eliminada correctamente",
                        timer: 2500,
                        showConfirmButton: false
                    });
                }
            });
        }

        async function enviarCocina() {
            if (!mesaActual) {
                await Swal.fire({
                    icon: "error",
                    title: "Oops...",
                    text: "Seleccione una mesa primero"
                });
                return;
            }

            if (ordenActual.length === 0) {
                await Swal.fire({
                    icon: "error",
                    title: "Oops...",
                    text: "Agrega productos antes de enviar la orden"
                });
                return;
            }

            const {
                isConfirmed
            } = await Swal.fire({
                title: `¿Enviar orden de la Mesa ${mesaActual} a cocina?`,
                text: "Esta acción enviará la orden a cocina.",
                icon: "question",
                showCancelButton: true,
                confirmButtonText: "Enviar a cocina",
                cancelButtonText: "Cancelar",
                reverseButtons: true
            });

            if (!isConfirmed) return;

            const listaProductos = ordenActual
                .map(item => `${item.nombre} x${item.cantidad}`)
                .join("\n");


            const notasField = document.getElementById('notas-orden');
            const notas = notasField ? notasField.value.trim() : '';

            const data = {
                mesa: mesaActual,
                items: listaProductos,
                notas: notas
            };

            try {
                Swal.fire({
                    title: "Enviando orden...",
                    html: `Mesa <strong>${mesaActual}</strong>`,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => Swal.showLoading()
                });

                const respuesta = await fetch("<?= BASE_URL ?>public/api/guardarOrden.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify(data)
                });

                if (!respuesta.ok) {
                    throw new Error(`HTTP ${respuesta.status}`);
                }

                const result = await respuesta.json();

                if (result.status === "OK") {
                    console.log("Orden guardada correctamente en backend");
                    console.log("mesaActual antes de actualizar estado:", mesaActual);
                    console.log("mesaEstados antes:", mesaEstados);

                    mesaEstados[mesaActual] = "pendiente";
                    console.log("mesaEstados después:", mesaEstados);

                    guardarEstadoMesas();
                    console.log("Estado guardado en localStorage");

                    actualizarEstadoMesa(mesaActual);
                    console.log("UI de mesa actualizada");

                    await Swal.fire({
                        position: "center",
                        icon: "success",
                        html: `Orden enviada a cocina para <strong>Mesa ${mesaActual}</strong> ✔`,
                        showConfirmButton: false,
                        timer: 2500,
                        timerProgressBar: false
                    });

                    ordenActual = [];
                    mesaActual = null;

                    const mesaActualSpan = document.getElementById("mesa-actual");
                    if (mesaActualSpan) mesaActualSpan.textContent = "No seleccionada";

                    const notasField = document.getElementById('notas-orden');
                    if (notasField) notasField.value = '';

                    actualizarOrden();
                    actualizarBotones();

                    window.location.href = "<?= BASE_URL ?>index.php";

                } else {
                    await Swal.fire({
                        icon: "error",
                        title: "Oops...",
                        text: result.message || "No se pudo enviar la orden a cocina"
                    });
                }

            } catch (error) {
                console.error("Error real en enviarCocina:", error);

                await Swal.fire({
                    icon: "error",
                    title: "Oops...",
                    text: error.message || "Error al enviar la orden"
                });
            }
        }

        function actualizarNavbar(activeBtn) {
            const navbar = document.getElementById('navbar');
            if (!navbar) return;

            navbar.querySelectorAll('button').forEach(btn => {
                btn.classList.remove('border-b-2', 'border-mint');
            });
            activeBtn.classList.add('border-b-2', 'border-mint');
        }

        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('#navbar button[data-slug]').forEach(btn => {
                btn.addEventListener('click', async () => {
                    const slug = btn.getAttribute('data-slug');
                    const nombre = btn.textContent.trim();

                    if (slug === "mesas") {
                        mostrarMesas();
                    } else {
                        await mostrarProductos(slug, nombre);
                    }

                    actualizarNavbar(btn);
                });
            });

            const mesasBtn = document.querySelector('#navbar button[data-slug="mesas"]');
            if (mesasBtn) actualizarNavbar(mesasBtn);

            const eliminarBtn = document.getElementById('eliminar-orden');
            const enviarBtn = document.getElementById('enviar-cocina');

            if (eliminarBtn) eliminarBtn.addEventListener('click', eliminarOrden);
            if (enviarBtn) enviarBtn.addEventListener('click', enviarCocina);

            actualizarTodasLasMesas();
            sincronizarEstadosMesas(false);

            setInterval(() => {
                sincronizarEstadosMesas(true);
            }, 5000);

            document.addEventListener('click', (e) => {
                const botonEntregar = e.target.closest('.btn-entregar');
                if (botonEntregar) return;

                const card = e.target.closest('.mesa-card');
                if (!card) return;

                const mesa = card.getAttribute('data-mesa');
                if (!mesa) return;

                const estadoMesa = mesaEstados[mesa] || "libre";

                if ((estadoMesa === "pendiente" || estadoMesa === "lista") && mesaActual !== mesa) {
                    Swal.fire({
                        icon: "info",
                        title: "Mesa ocupada",
                        text: estadoMesa === "lista" ?
                            `La Mesa ${mesa} tiene una orden lista para entregar.` : `La Mesa ${mesa} tiene una orden en cocina.`
                    });
                    return;
                }

                seleccionarMesa(mesa);
            });
            document.addEventListener('click', (e) => {
                const boton = e.target.closest('.btn-entregar');
                if (!boton) return;

                e.preventDefault();
                e.stopPropagation();

                const card = e.target.closest('.mesa-card');
                if (!card) return;

                const mesa = card.getAttribute('data-mesa');
                if (!mesa) return;

                entregarOrden(mesa);
            });
        });
    </script>
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

</body>

</html>
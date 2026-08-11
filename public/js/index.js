// Lógica extraída desde views/index.php para aligerar la vista.
// Requiere window.INDEX_CONFIG definido por la vista PHP.

const { BASE_URL, LAYOUT_API_URL, USER_IS_ADMIN } = window.INDEX_CONFIG;

let mesaActual = null;
let ordenActual = [];
let totalOrden = 0;
let dragState = null;
        // Admin puede editar el layout, pero el modo edición inicia en OFF.
        let editLayoutMode = false;

        // Estado visual por mesa ahora contempla sub-órdenes separadas:
        // cocina y barista. Se guarda un objeto por mesa con estado general y
        // estados por área para poder notificar y entregar cada parte por separado.
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
                card.classList.toggle('is-selected', card.getAttribute('data-mesa') === numeroMesa);
            });

            actualizarResumenNotas();
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

        function normalizarEstadoMesaPayload(payload) {
            if (!payload || typeof payload !== 'object' || Array.isArray(payload)) {
                const simple = String(payload || 'libre');
                return {
                    general: simple === 'lista' ? 'lista' : (simple === 'pendiente' ? 'pendiente' : 'libre'),
                    cocina: simple === 'libre' ? null : simple,
                    barista: null,
                    estado: simple
                };
            }
            return {
                general: payload.general || 'libre',
                cocina: payload.cocina ?? null,
                barista: payload.barista ?? null,
                estado: payload.estado || payload.general || 'libre'
            };
        }

        function actualizarEstadoMesa(numeroMesa) {
            numeroMesa = String(numeroMesa);

            const card = document.querySelector(`.mesa-card[data-mesa="${numeroMesa}"]`);
            if (!card) return;

            const estadoMesa = normalizarEstadoMesaPayload(mesaEstados[numeroMesa]);
            const estado = estadoMesa.general || 'libre';
            const shape = card.dataset.shape || 'square';
            const isRect = shape === 'rect';

            card.className = `draggable mesa-card table ${shape} state-${(estado === 'parcial_lista' ? 'lista' : estado)}`;
            card.dataset.mesa = numeroMesa;
            card.classList.toggle('is-selected', mesaActual === numeroMesa);

            const chairs = isRect ? `
                <div class="chairs" aria-hidden="true">
                    <span class="chair c-top2"></span><span class="chair c-top3"></span>
                    <span class="chair c-bottom2"></span><span class="chair c-bottom3"></span>
                    <span class="chair c-left"></span><span class="chair c-right"></span>
                </div>
            ` : `
                <div class="chairs" aria-hidden="true">
                    <span class="chair c-top"></span><span class="chair c-bottom"></span>
                    <span class="chair c-left"></span><span class="chair c-right"></span>
                </div>
            `;

            const badges = [];
            if (estadoMesa.cocina === 'lista') {
                badges.push('<span class="table-notification-badge badge-kitchen" title="Comida lista">🍽</span>');
            }
            if (estadoMesa.barista === 'lista') {
                badges.push('<span class="table-notification-badge badge-barista" title="Bebidas listas">☕</span>');
            }

            let statusLabel = 'Disponible';
            let statusHtml = '<span class="table-status">Disponible</span>';

            if (estado === 'pendiente') {
                statusLabel = 'En preparación';
                statusHtml = '<span class="table-status">En preparación</span>';
            } else if (estado === 'parcial_lista') {
                statusLabel = 'Parcialmente lista';
                if (estadoMesa.cocina === 'lista' && estadoMesa.barista !== 'lista') {
                    statusHtml = '<button class="btn-entregar btn-entregar-plan" type="button">Entregar comida</button>';
                } else if (estadoMesa.barista === 'lista' && estadoMesa.cocina !== 'lista') {
                    statusHtml = '<button class="btn-entregar btn-entregar-plan" type="button">Entregar bebida</button>';
                } else {
                    statusHtml = '<button class="btn-entregar btn-entregar-plan" type="button">Entregar sub-orden</button>';
                }
            } else if (estado === 'lista') {
                statusLabel = 'Lista';
                statusHtml = '<button class="btn-entregar btn-entregar-plan" type="button">Entregar Orden</button>';
            }

            card.setAttribute('aria-label', `Mesa ${numeroMesa} - ${statusLabel}`);
            card.innerHTML = `
                ${USER_IS_ADMIN ? '<div class="drag-handle" title="Arrastrar">⠿</div>' : ''}
                <div class="table-notification-stack">${badges.join('')}</div>
                <div class="table-content">
                    <div class="table-label">${numeroMesa}</div>
                    ${statusHtml}
                </div>
                ${chairs}
            `;
        }

        function actualizarTodasLasMesas() {
            document.querySelectorAll('.mesa-card').forEach(card => {
                const mesa = card.getAttribute('data-mesa');
                if (mesa) actualizarEstadoMesa(mesa);
            });
        }

        function mostrarToastMesaLista(numeroMesa, area) {
            const toastEl = document.getElementById('toastMesaLista');
            const toastBody = document.getElementById('toastMesaListaBody');

            if (!toastEl || !toastBody) return;

            toastEl.classList.remove('text-bg-success', 'text-bg-dark');

            if (area === 'barista') {
                // Barista ahora usa también notificación verde, igual que cocina.
                toastEl.classList.add('text-bg-success');
                toastBody.textContent = `Bebida(s) lista(s) en Mesa ${numeroMesa}.`;
            } else {
                toastEl.classList.add('text-bg-success');
                toastBody.textContent = `La comida de la Mesa ${numeroMesa} está lista para entregar.`;
            }

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

                const estadosNormalizados = {};
                document.querySelectorAll('.mesa-card').forEach(card => {
                    const mesa = card.getAttribute('data-mesa');
                    if (!mesa) return;
                    estadosNormalizados[mesa] = normalizarEstadoMesaPayload(nuevosEstados[mesa] || {
                        general: 'libre',
                        cocina: null,
                        barista: null,
                        estado: 'libre'
                    });
                });

                mesaEstados = estadosNormalizados;
                guardarEstadoMesas();
                actualizarTodasLasMesas();

                if (mostrarNotificacion) {
                    Object.keys(mesaEstados).forEach(mesa => {
                        const actual = normalizarEstadoMesaPayload(mesaEstados[mesa]);
                        const anterior = normalizarEstadoMesaPayload(estadosAnteriores[mesa]);

                        if (actual.cocina === 'lista' && anterior.cocina !== 'lista') {
                            mostrarToastMesaLista(mesa, 'cocina');
                            reproducirSonidoNotificacion();
                        }
                        if (actual.barista === 'lista' && anterior.barista !== 'lista') {
                            mostrarToastMesaLista(mesa, 'barista');
                            reproducirSonidoNotificacion();
                        }
                    });
                }

            } catch (error) {
                console.error("Error sincronizando estados de mesas:", error);
            }
        }

        function clampLayout(n, min, max) {
            return Math.max(min, Math.min(max, n));
        }

        function getRestaurantFloorRect() {
            const floor = document.getElementById('restaurant-floor');
            return floor ? floor.getBoundingClientRect() : null;
        }

        function obtenerItemsLayoutActual() {
            const floorRect = getRestaurantFloorRect();
            if (!floorRect) return {};

            const items = {};
            document.querySelectorAll('#restaurant-floor .draggable').forEach(el => {
                const id = el.dataset.id;
                const rect = el.getBoundingClientRect();
                const leftPx = rect.left - floorRect.left;
                const topPx = rect.top - floorRect.top;
                items[id] = {
                    left: clampLayout((leftPx / floorRect.width) * 100, -2, 98),
                    top: clampLayout((topPx / floorRect.height) * 100, -2, 98)
                };
            });

            return items;
        }

        function aplicarPosicionesLayout(items) {
            if (!items || typeof items !== 'object') return;

            Object.entries(items).forEach(([id, pos]) => {
                const el = document.querySelector(`#restaurant-floor .draggable[data-id="${id}"]`);
                if (!el || typeof pos !== 'object') return;

                if (typeof pos.left !== 'undefined') {
                    el.style.left = `${clampLayout(Number(pos.left), -2, 98)}%`;
                }
                if (typeof pos.top !== 'undefined') {
                    el.style.top = `${clampLayout(Number(pos.top), -2, 98)}%`;
                }
            });
        }

        async function guardarPosicionesLayout() {
            if (!USER_IS_ADMIN) return;

            const items = obtenerItemsLayoutActual();

            try {
                const resp = await fetch(LAYOUT_API_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        items
                    })
                });

                const json = await resp.json();
                if (!resp.ok || json.status !== 'OK') {
                    throw new Error(json.message || `HTTP ${resp.status}`);
                }

                Swal.fire({
                    toast: true,
                    position: 'bottom',
                    icon: 'success',
                    title: 'Posiciones guardadas',
                    showConfirmButton: false,
                    timer: 1800
                });
            } catch (error) {
                console.error('No se pudo guardar el layout compartido:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'No se pudo guardar',
                    text: error.message || 'Error guardando el layout compartido.'
                });
            }
        }

        async function cargarPosicionesLayout() {
            try {
                const resp = await fetch(`${LAYOUT_API_URL}?_=${Date.now()}`);
                const json = await resp.json();

                if (!resp.ok || json.status !== 'OK') {
                    throw new Error(json.message || `HTTP ${resp.status}`);
                }

                aplicarPosicionesLayout(json.data || {});
            } catch (error) {
                console.warn('No se pudo cargar el layout compartido:', error);
            }
        }

        async function restablecerPosicionesLayout() {
            if (!USER_IS_ADMIN) return;

            try {
                const resp = await fetch(LAYOUT_API_URL, {
                    method: 'DELETE'
                });
                const json = await resp.json();

                if (!resp.ok || json.status !== 'OK') {
                    throw new Error(json.message || `HTTP ${resp.status}`);
                }

                window.location.reload();
            } catch (error) {
                console.error('No se pudo restablecer el layout compartido:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'No se pudo restablecer',
                    text: error.message || 'Error restableciendo el layout compartido.'
                });
            }
        }

        function actualizarChipEdicion() {
            const chip = document.getElementById('editLayoutChip');
            const floor = document.getElementById('restaurant-floor');
            const saveBtn = document.getElementById('saveLayoutBtn');
            const resetBtn = document.getElementById('resetLayoutBtn');
            if (!chip) return;

            chip.classList.toggle('active', editLayoutMode);
            chip.textContent = `Modo edición: ${editLayoutMode ? 'ON' : 'OFF'}`;

            if (floor) {
                floor.classList.toggle('layout-readonly', !editLayoutMode);
            }

            [saveBtn, resetBtn].forEach(btn => {
                if (!btn) return;
                btn.disabled = !editLayoutMode;
                btn.classList.toggle('opacity-50', !editLayoutMode);
                btn.classList.toggle('cursor-not-allowed', !editLayoutMode);
            });
        }

        function onLayoutPointerDown(e) {
            if (!USER_IS_ADMIN || !editLayoutMode) return;

            const handle = e.target.closest('.drag-handle');
            if (!handle) return;

            const el = handle.closest('.draggable');
            const floor = document.getElementById('restaurant-floor');
            if (!el || !floor || !floor.contains(el)) return;

            e.preventDefault();

            const rect = el.getBoundingClientRect();
            dragState = {
                el,
                pointerId: e.pointerId,
                offsetX: e.clientX - rect.left,
                offsetY: e.clientY - rect.top
            };

            el.classList.add('dragging');
            el.setPointerCapture?.(e.pointerId);
        }

        function onLayoutPointerMove(e) {
            if (!dragState || e.pointerId !== dragState.pointerId) return;

            const floorRect = getRestaurantFloorRect();
            if (!floorRect) return;

            const x = e.clientX - floorRect.left - dragState.offsetX;
            const y = e.clientY - floorRect.top - dragState.offsetY;

            dragState.el.style.left = `${clampLayout((x / floorRect.width) * 100, -2, 98)}%`;
            dragState.el.style.top = `${clampLayout((y / floorRect.height) * 100, -2, 98)}%`;
        }

        function onLayoutPointerUp(e) {
            if (!dragState || e.pointerId !== dragState.pointerId) return;
            dragState.el.classList.remove('dragging');
            dragState = null;
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
                    const imagen = p.imagen;
                    const nombreSafe = nombre.replace(/'/g, "\\'");
                    
                    let imgSrc = "";
                    if (imagen) {
                        if (imagen.startsWith("http://") || imagen.startsWith("https://")) {
                            imgSrc = imagen;
                        } else {
                            imgSrc = BASE_URL + "public/img/productos/" + imagen;
                        }
                    } else {
                        imgSrc = BASE_URL + "public/img/productos/default.png"; // Fallback
                    }

                    return `
                        <div class="bg-white rounded-xl p-6 shadow-sm hover:shadow-md transition-all duration-200">
                            <div class="text-center">
                                <div class="w-20 h-20 mx-auto mb-4 overflow-hidden rounded-xl border border-gray-100 flex items-center justify-center bg-gray-50">
                                    <img src="${imgSrc}" alt="${escaparHtml(nombre)}" class="w-full h-full object-cover" onerror="this.src='${BASE_URL}public/img/productos/default.png'">
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
                    cantidad: 1,
                    notas: []
                });
            }

            actualizarOrden();
        }

        function obtenerTotalNotas() {
            return ordenActual.reduce((total, item) => total + ((item.notas || []).length), 0);
        }

        function escaparHtml(valor = '') {
            return String(valor)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function actualizarResumenNotas() {
            const resumen = document.getElementById('resumen-notas');
            if (!resumen) return;

            if (ordenActual.length === 0) {
                resumen.textContent = 'Agrega productos para poder añadir notas específicas.';
                return;
            }

            const totalNotas = obtenerTotalNotas();
            resumen.textContent = totalNotas > 0 ?
                `${totalNotas} nota${totalNotas === 1 ? '' : 's'} distribuida${totalNotas === 1 ? '' : 's'} en la orden.` :
                'Puedes agregar notas separadas a cada producto de la orden.';
        }

        function abrirModalNotas() {
            if (ordenActual.length === 0) {
                Swal.fire({
                    icon: 'info',
                    title: 'Primero agrega productos',
                    text: 'Necesitas al menos un producto en la orden para añadir notas.'
                });
                return;
            }

            const opciones = ordenActual.map((item, index) => {
                const notas = item.notas || [];
                return `
                    <button type="button"
                            class="w-full text-left border border-gray-200 rounded-xl p-3 hover:border-mint hover:bg-gray-50 transition-all duration-200"
                            onclick="seleccionarProductoParaNotas(${index})">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="font-semibold text-brown">${escaparHtml(item.nombre)}</p>
                                <p class="text-sm text-gray-500">Cantidad: ${item.cantidad}</p>
                            </div>
                            <span class="text-xs font-medium ${notas.length ? 'text-mint' : 'text-gray-400'}">
                                ${notas.length} nota${notas.length === 1 ? '' : 's'}
                            </span>
                        </div>
                    </button>
                `;
            }).join('');

            Swal.fire({
                title: 'Agregar notas por producto',
                html: `
                    <div class="space-y-3 text-left">
                        <p class="text-sm text-gray-500">Selecciona el producto al que quieres agregarle una nota.</p>
                        <div class="space-y-2 max-h-80 overflow-y-auto pr-1">${opciones}</div>
                    </div>
                `,
                showConfirmButton: false,
                showCloseButton: true,
                width: 520
            });
        }

        function seleccionarProductoParaNotas(index) {
            const item = ordenActual[index];
            if (!item) return;

            Swal.fire({
                title: `Nota para ${item.nombre}`,
                html: `
                    <div class="text-left space-y-3">
                        <p class="text-sm text-gray-500">Escribe una nota específica para este producto.</p>
                        <textarea id="nota-producto-input"
                                  class="swal2-textarea !m-0 !w-full !min-h-[120px]"
                                  placeholder="Ej: Sin cebolla, término medio, salsa aparte..."></textarea>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Agregar nota',
                cancelButtonText: 'Volver',
                focusConfirm: false,
                preConfirm: () => {
                    const nota = document.getElementById('nota-producto-input')?.value.trim() || '';
                    if (!nota) {
                        Swal.showValidationMessage('Escribe una nota antes de continuar.');
                        return false;
                    }
                    return nota;
                }
            }).then((result) => {
                if (result.isConfirmed && result.value) {
                    agregarNotaProducto(index, result.value);
                } else if (result.dismiss === Swal.DismissReason.cancel) {
                    abrirModalNotas();
                }
            });
        }

        function agregarNotaProducto(index, nota) {
            const item = ordenActual[index];
            if (!item) return;

            if (!Array.isArray(item.notas)) {
                item.notas = [];
            }

            item.notas.push(nota);
            actualizarOrden();

            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: 'Nota agregada',
                showConfirmButton: false,
                timer: 1800
            }).then(() => {
                if (ordenActual.length > 1) {
                    abrirModalNotas();
                }
            });
        }

        function eliminarNotaProducto(indexProducto, indexNota) {
            const item = ordenActual[indexProducto];
            if (!item || !Array.isArray(item.notas)) return;

            item.notas.splice(indexNota, 1);
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
                ordenItems.innerHTML = ordenActual.map((item, index) => {
                    const notas = Array.isArray(item.notas) ? item.notas : [];
                    const notasHtml = notas.length ? `
                        <div class="mt-3 space-y-2 border-t border-gray-200 pt-3">
                            ${notas.map((nota, notaIndex) => `
                                <div class="flex items-start justify-between gap-2 bg-white border border-gray-200 rounded-lg px-3 py-2">
                                    <div class="flex items-start gap-2 text-sm text-gray-700">
                                        <i class="fas fa-note-sticky text-mint mt-1"></i>
                                        <span>${escaparHtml(nota)}</span>
                                    </div>
                                    <button type="button"
                                            onclick="eliminarNotaProducto(${index}, ${notaIndex})"
                                            class="text-red-500 hover:text-red-700 text-xs font-medium whitespace-nowrap">
                                        Eliminar
                                    </button>
                                </div>
                            `).join('')}
                        </div>
                    ` : '<p class="mt-3 text-xs text-gray-400">Sin notas para este producto.</p>';

                    return `
                        <div class="bg-gray-50 p-3 rounded-lg">
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex-1">
                                    <p class="text-brown font-medium">${escaparHtml(item.nombre)}</p>
                                    <p class="text-mint text-sm">₡${item.precio.toLocaleString()} c/u</p>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <button onclick="cambiarCantidad(${index}, -1)" class="w-6 h-6 bg-gray-300 rounded-full flex items-center justify-center text-sm">-</button>
                                    <span class="text-brown font-medium w-8 text-center">${item.cantidad}</span>
                                    <button onclick="cambiarCantidad(${index}, 1)" class="w-6 h-6 custom-mint text-white rounded-full flex items-center justify-center text-sm">+</button>
                                </div>
                            </div>
                            ${notasHtml}
                        </div>
                    `;
                }).join('');
            }

            totalOrden = ordenActual.reduce((total, item) => total + (item.precio * item.cantidad), 0);
            totalElement.textContent = `₡${totalOrden.toLocaleString()}`;
            actualizarResumenNotas();
            actualizarBotones();
        }

        async function entregarOrden(numeroMesa) {
            const estadoMesa = normalizarEstadoMesaPayload(mesaEstados[String(numeroMesa)]);
            const opciones = [];

            if (estadoMesa.barista === 'lista') {
                opciones.push({
                    value: 'barista',
                    label: 'Entregar bebida(s)'
                });
            }
            if (estadoMesa.cocina === 'lista') {
                opciones.push({
                    value: 'cocina',
                    label: 'Entregar comida'
                });
            }

            if (!opciones.length) {
                await Swal.fire({
                    icon: 'info',
                    title: 'Nada listo aún',
                    text: 'Todavía no hay sub-órdenes listas para entregar en esta mesa.'
                });
                return;
            }

            const ambasListas = estadoMesa.cocina === 'lista' && estadoMesa.barista === 'lista';
            let area = opciones[0].value;

            if (!ambasListas && opciones.length > 1) {
                const {
                    value: seleccion
                } = await Swal.fire({
                    title: `Mesa ${numeroMesa}`,
                    input: 'radio',
                    inputOptions: Object.fromEntries(opciones.map(o => [o.value, o.label])),
                    inputValue: area,
                    confirmButtonText: 'Continuar',
                    showCancelButton: true,
                    cancelButtonText: 'Cancelar',
                    inputValidator: (value) => !value ? 'Selecciona qué parte vas a entregar.' : null
                });
                if (!seleccion) return;
                area = seleccion;
            }

            const {
                isConfirmed
            } = await Swal.fire({
                title: ambasListas ?
                    `¿Entregar la orden completa de la Mesa ${numeroMesa}?` :
                    `¿Entregar ${area === 'barista' ? 'las bebida(s)' : 'la comida'} de la Mesa ${numeroMesa}?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, entregar',
                cancelButtonText: 'Cancelar',
                reverseButtons: true
            });
            if (!isConfirmed) return;

            try {
                const body = ambasListas ?
                    `mesa=${encodeURIComponent(numeroMesa)}&area=${encodeURIComponent('barista')}` :
                    `mesa=${encodeURIComponent(numeroMesa)}&area=${encodeURIComponent(area)}`;

                const respuesta = await fetch(`${BASE_URL}public/api/entregarOrden.php`, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    body
                });

                if (!respuesta.ok) {
                    const errorText = await respuesta.text();
                    console.error("Respuesta entregarOrden:", errorText);
                    throw new Error(`HTTP ${respuesta.status}`);
                }

                const result = await respuesta.json();

                if (result.status === "OK") {
                    if (ambasListas) {
                        const respuestaCocina = await fetch(`${BASE_URL}public/api/entregarOrden.php`, {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/x-www-form-urlencoded"
                            },
                            body: `mesa=${encodeURIComponent(numeroMesa)}&area=${encodeURIComponent('cocina')}`
                        });

                        if (!respuestaCocina.ok) {
                            throw new Error(`HTTP ${respuestaCocina.status} al entregar cocina`);
                        }
                    }

                    await Swal.fire({
                        position: "center",
                        icon: "success",
                        html: ambasListas ?
                            `<strong>Mesa ${numeroMesa}</strong><br>Orden completa entregada` :
                            `<strong>Mesa ${numeroMesa}</strong><br>${area === 'barista' ? 'Bebida(s)' : 'Comida'} entregada(s)`,
                        showConfirmButton: false,
                        timer: 2000
                    });

                    sincronizarEstadosMesas(false);
                } else {
                    await Swal.fire({
                        icon: "error",
                        title: "Oops...",
                        text: result.message || "No se pudo entregar la sub-orden"
                    });
                }
            } catch (error) {
                console.error("Error en entregarOrden:", error);
                await Swal.fire({
                    icon: "error",
                    title: "Oops...",
                    text: error.message || "Error al entregar la sub-orden"
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
            const gestionarNotasBtn = document.getElementById('gestionar-notas');

            const tieneOrden = ordenActual.length > 0 && mesaActual;
            if (eliminarBtn) eliminarBtn.disabled = !tieneOrden;
            if (enviarBtn) enviarBtn.disabled = !tieneOrden;
            if (gestionarNotasBtn) gestionarNotasBtn.disabled = !tieneOrden;
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
                confirmButtonText: "Enviar orden",
                cancelButtonText: "Cancelar",
                reverseButtons: true
            });

            if (!isConfirmed) return;

            const listaProductos = ordenActual
                .map(item => {
                    const notas = Array.isArray(item.notas) ? item.notas.filter(Boolean) : [];
                    const bloqueNotas = notas.length ? `\n  - Notas: ${notas.join(' | ')}` : '';
                    return `${item.nombre} x${item.cantidad}${bloqueNotas}`;
                })
                .join("\n");

            const notas = ordenActual
                .flatMap(item => (Array.isArray(item.notas) ? item.notas.map(nota => `${item.nombre}: ${nota}`) : []))
                .join("\n");

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

                const respuesta = await fetch(`${BASE_URL}public/api/guardarOrden.php`, {
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



                    mesaEstados[mesaActual] = {
                        general: 'pendiente',
                        cocina: 'pendiente',
                        barista: 'pendiente',
                        estado: 'pendiente'
                    };

                    guardarEstadoMesas();

                    actualizarEstadoMesa(mesaActual);

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
                    document.querySelectorAll('.mesa-card').forEach(card => card.classList.remove('is-selected'));

                    const mesaActualSpan = document.getElementById("mesa-actual");
                    if (mesaActualSpan) mesaActualSpan.textContent = "No seleccionada";

                    actualizarOrden();
                    actualizarBotones();

                    window.location.href = `${BASE_URL}index.php`;

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

        document.addEventListener('DOMContentLoaded', async () => {
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
            const gestionarNotasBtn = document.getElementById('gestionar-notas');
            const saveLayoutBtn = document.getElementById('saveLayoutBtn');
            const resetLayoutBtn = document.getElementById('resetLayoutBtn');
            const editLayoutChip = document.getElementById('editLayoutChip');

            if (eliminarBtn) eliminarBtn.addEventListener('click', eliminarOrden);
            if (enviarBtn) enviarBtn.addEventListener('click', enviarCocina);
            if (gestionarNotasBtn) gestionarNotasBtn.addEventListener('click', abrirModalNotas);
            if (saveLayoutBtn) saveLayoutBtn.addEventListener('click', () => {
                if (!editLayoutMode) return;
                guardarPosicionesLayout();
            });
            if (resetLayoutBtn) resetLayoutBtn.addEventListener('click', () => {
                if (!editLayoutMode) return;
                restablecerPosicionesLayout();
            });
            if (editLayoutChip) {
                editLayoutChip.addEventListener('click', () => {
                    editLayoutMode = !editLayoutMode;
                    actualizarChipEdicion();
                });
            }

            actualizarChipEdicion();
            await cargarPosicionesLayout();
            actualizarTodasLasMesas();
            sincronizarEstadosMesas(false);

            document.addEventListener('pointerdown', onLayoutPointerDown);
            document.addEventListener('pointermove', onLayoutPointerMove);
            document.addEventListener('pointerup', onLayoutPointerUp);
            document.addEventListener('pointercancel', onLayoutPointerUp);

            setInterval(() => {
                sincronizarEstadosMesas(true);
            }, 5000);

            document.addEventListener('click', (e) => {
                const botonEntregar = e.target.closest('.btn-entregar');
                if (botonEntregar) return;
                if (e.target.closest('.drag-handle')) return;

                const card = e.target.closest('.mesa-card');
                if (!card) return;

                const mesa = card.getAttribute('data-mesa');
                if (!mesa) return;

                const estadoMesaObj = normalizarEstadoMesaPayload(mesaEstados[mesa]);
                const estadoMesa = estadoMesaObj.general || 'libre';

                if ((estadoMesa === "pendiente" || estadoMesa === "lista" || estadoMesa === 'parcial_lista') && mesaActual !== mesa) {
                    let texto = `La Mesa ${mesa} tiene una orden en preparación.`;
                    if (estadoMesaObj.cocina === 'lista' && estadoMesaObj.barista === 'lista') {
                        texto = `La Mesa ${mesa} tiene comida y bebida listas para entregar.`;
                    } else if (estadoMesaObj.cocina === 'lista') {
                        texto = `La Mesa ${mesa} tiene la comida lista para entregar.`;
                    } else if (estadoMesaObj.barista === 'lista') {
                        texto = `La Mesa ${mesa} tiene las bebida(s) listas para entregar.`;
                    }
                    Swal.fire({
                        icon: "info",
                        title: "Mesa ocupada",
                        text: texto
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
    

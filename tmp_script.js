        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        montserrat: ['Montserrat', 'sans-serif']
                    },
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
        const API_ORDENES = "<?= BASE_URL ?>public/api/ordenesAdminData.php";
        const tablaBody = document.getElementById("tabla-ordenes-body");
        const filtroTipo = document.getElementById("filtro-tipo");
        const buscarOrdenInput = document.getElementById("buscar-orden-input");
        const filtroEstado = document.getElementById("filtro-estado");

        const modalOrden = document.getElementById("modal-orden");
        const cerrarModalOrdenBtn = document.getElementById("cerrar-modal-orden");
        const modalOrdenId = document.getElementById("modal-orden-id");
        const modalMesa = document.getElementById("modal-mesa");
        const modalUsuario = document.getElementById("modal-usuario");
        const modalEstado = document.getElementById("modal-estado");
        const modalTotal = document.getElementById("modal-total");
        const modalItemsBody = document.getElementById("modal-items-body");
        const modalNotas = document.getElementById("modal-notas");
        const modalSelectCocina = document.getElementById("modal-select-cocina");
        const btnUpdateCocina = document.getElementById("btn-update-cocina");
        const modalSelectBarista = document.getElementById("modal-select-barista");
        const btnUpdateBarista = document.getElementById("btn-update-barista");

        let ordenesCache = [];
        let modalOrdenActualId = null;

        function formatearColones(valor) {
            return "₡" + Number(valor || 0).toLocaleString("es-CR", {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        function escapeHtml(texto) {
            const div = document.createElement("div");
            div.textContent = texto ?? "";
            return div.innerHTML;
        }

        function obtenerClaseEstado(estado) {
            estado = (estado || "").toLowerCase();

            if (estado.includes("entregada")) {
                return "bg-emerald-100 text-emerald-700 border border-emerald-200";
            }

            if (estado.includes("proceso") || estado.includes("lista")) {
                return "bg-amber-100 text-amber-700 border border-amber-200";
            }

            if (estado.includes("cancel")) {
                return "bg-rose-100 text-rose-700 border border-rose-200";
            }

            return "bg-stone-100 text-stone-700 border border-stone-200";
        }

        function capitalizarEstado(estado) {
            if (!estado) return "Pendiente";
            return estado.replaceAll("_", " ").replace(/\b\w/g, c => c.toUpperCase());
        }

        function abrirModalOrden(orden) {
            modalOrdenId.textContent = `#${escapeHtml(String(orden.id_mostrado ?? 'N/A'))}`;
            modalMesa.textContent = `Mesa ${String(orden.mesa ?? 'N/A')}`;
            modalUsuario.textContent = String(orden.usuario_nombre ?? 'Sin usuario');
            modalEstado.textContent = capitalizarEstado(String(orden.estado_normalizado ?? 'pendiente'));
            modalTotal.textContent = formatearColones(orden.total ?? 0);
            modalNotas.textContent = String(orden.notas ?? 'Sin notas');
            modalOrdenActualId = orden.id_mostrado;
            
            const estadoCocina = String(orden.estado_subordenes?.cocina ?? 'no_aplica');
            if (estadoCocina === 'no_aplica') {
                modalSelectCocina.value = 'no_aplica';
                modalSelectCocina.disabled = true;
                btnUpdateCocina.disabled = true;
            } else {
                modalSelectCocina.value = estadoCocina;
                modalSelectCocina.disabled = false;
                btnUpdateCocina.disabled = false;
            }

            const estadoBarista = String(orden.estado_subordenes?.barista ?? 'no_aplica');
            if (estadoBarista === 'no_aplica') {
                modalSelectBarista.value = 'no_aplica';
                modalSelectBarista.disabled = true;
                btnUpdateBarista.disabled = true;
            } else {
                modalSelectBarista.value = estadoBarista;
                modalSelectBarista.disabled = false;
                btnUpdateBarista.disabled = false;
            }

            const items = Array.isArray(orden.items_detalle) ? orden.items_detalle : [];
            if (items.length === 0) {
                modalItemsBody.innerHTML = `
                    <tr>
                        <td colspan="6" class="px-5 py-8 text-center text-brownSoft">No hay detalle de productos disponible.</td>
                    </tr>
                `;
            } else {
                modalItemsBody.innerHTML = items.map(item => `
                    <tr>
                        <td class="px-5 py-4 font-medium text-brownDark">${escapeHtml(String(item.nombre ?? 'N/A'))}</td>
                        <td class="px-5 py-4 text-brownSoft">${escapeHtml(String(item.area ?? 'N/A')).replace('cocina', 'Cocina').replace('barista', 'Barista')}</td>
                        <td class="px-5 py-4 text-brownSoft">${escapeHtml(capitalizarEstado(String(item.estado_item ?? 'pendiente')))}</td>
                        <td class="px-5 py-4 text-brownSoft">${escapeHtml(String(item.cantidad ?? 0))}</td>
                        <td class="px-5 py-4 text-brownSoft">${formatearColones(item.precio_unitario ?? 0)}</td>
                        <td class="px-5 py-4 font-semibold text-mintGreen">${formatearColones(item.subtotal ?? 0)}</td>
                    </tr>
                `).join('');
            }

            modalOrden.classList.remove('hidden');
            modalOrden.classList.add('flex');
        }

        function cerrarModalOrden() {
            modalOrden.classList.add('hidden');
            modalOrden.classList.remove('flex');
        }

        function renderTabla(ordenes) {
            if (!Array.isArray(ordenes) || ordenes.length === 0) {
                tablaBody.innerHTML = `
                    <tr>
                        <td colspan="6" class="px-6 py-16 text-center">
                            <div class="flex flex-col items-center justify-center text-brownSoft">
                                <div class="w-16 h-16 rounded-2xl bg-[#F5EEE5] flex items-center justify-center text-2xl text-brownDark mb-4">
                                    <i class="fa-solid fa-inbox"></i>
                                </div>
                                <h3 class="text-lg font-bold text-brownDark mb-1">No hay órdenes registradas</h3>
                                <p class="text-sm">Cuando se generen órdenes, aparecerán aquí.</p>
                            </div>
                        </td>
                    </tr>
                `;
                return;
            }

            tablaBody.innerHTML = ordenes.map(orden => {
                const id = escapeHtml(String(orden.id_mostrado ?? "N/A"));
                const mesa = escapeHtml(String(orden.mesa ?? "N/A"));
                const usuario = escapeHtml(String(orden.usuario_nombre ?? "Sin usuario"));
                const total = formatearColones(orden.total ?? 0);
                const estado = String(orden.estado_normalizado ?? "pendiente");
                const fecha = escapeHtml(String(orden.fecha_formateada ?? "N/A"));
                const claseEstado = obtenerClaseEstado(estado);
                const estadoTexto = escapeHtml(capitalizarEstado(estado));

                const ordenPayload = encodeURIComponent(JSON.stringify(orden));

                return `
                    <tr class="hover:bg-[#FCFAF7] transition-colors cursor-pointer" data-orden="${ordenPayload}">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-[#F5EEE5] text-brownDark flex items-center justify-center">
                                    <i class="fa-solid fa-bag-shopping"></i>
                                </div>
                                <div>
                                    <div class="font-bold text-brownDark">#${id}</div>
                                    <div class="text-xs text-brownSoft">Orden registrada</div>
                                </div>
                            </div>
                        </td>

                        <td class="px-6 py-4 text-sm font-semibold text-brownDark">
                            Mesa ${mesa}
                        </td>

                        <td class="px-6 py-4 text-sm text-brownSoft">
                            ${usuario}
                        </td>

                        <td class="px-6 py-4 font-bold text-mintGreen">
                            ${total}
                        </td>

                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-semibold ${claseEstado}">
                                ${estadoTexto}
                            </span>
                        </td>

                        <td class="px-6 py-4 text-sm text-brownSoft">
                            ${fecha}
                        </td>
                    </tr>
                `;
            }).join("");
        }

        function aplicarFiltroOrdenes() {
            const termino = (buscarOrdenInput?.value || '').trim().toLowerCase();
            const tipoFiltro = filtroTipo?.value || 'id_mostrado';
            const estadoFiltro = (filtroEstado?.value || '').toLowerCase();
            
            const filtradas = ordenesCache.filter(orden => {
                let matchTexto = true;
                if (termino) {
                    const valor = String(orden[tipoFiltro] ?? '').toLowerCase();
                    matchTexto = valor.includes(termino);
                }

                let matchEstado = true;
                if (estadoFiltro) {
                    const estadoOrden = String(orden.estado_normalizado ?? "pendiente").toLowerCase();
                    matchEstado = estadoOrden === estadoFiltro;
                }

                return matchTexto && matchEstado;
            });
            renderTabla(filtradas);

            document.querySelectorAll('#tabla-ordenes-body tr[data-orden]').forEach(fila => {
                fila.addEventListener('click', () => {
                    try {
                        const orden = JSON.parse(decodeURIComponent(fila.dataset.orden || ''));
                        abrirModalOrden(orden);
                    } catch (e) {
                        console.error('Error leyendo detalle de orden:', e);
                    }
                });
            });
        }

        let cargando = false;

        async function cargarOrdenes() {
            if (cargando) return;
            cargando = true;

            try {
                const response = await fetch(API_ORDENES, {
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

                ordenesCache = data.ordenes || [];
                aplicarFiltroOrdenes();
            } catch (error) {
                console.error("Error cargando órdenes:", error);

                tablaBody.innerHTML = `
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-rose-600 font-medium">
                            Error cargando las órdenes. Intenta de nuevo en unos segundos.
                        </td>
                    </tr>
                `;
            } finally {
                cargando = false;
            }
        }

        async function actualizarSubestado(area, nuevoEstado, botonActualizar) {
            if (!modalOrdenActualId) return;
            botonActualizar.disabled = true;
            botonActualizar.textContent = '...';

            try {
                const formData = new FormData();
                formData.append('numero', modalOrdenActualId);
                formData.append('estado', nuevoEstado);
                formData.append('area', area);

                const response = await fetch('<?= BASE_URL ?>public/api/adminCambiarEstadoOrden.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                if (result.status === 'OK') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Actualizado',
                        text: `La suborden de ${area} ha sido actualizada`,
                        timer: 1500,
                        showConfirmButton: false
                    });
                    cargarOrdenes();
                    setTimeout(cerrarModalOrden, 1000);
                } else {
                    throw new Error(result.message || 'Error desconocido');
                }
            } catch (e) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: e.message || 'No se pudo actualizar el estado'
                });
            } finally {
                botonActualizar.disabled = false;
                botonActualizar.textContent = 'Guardar';
            }
        }

        btnUpdateCocina?.addEventListener('click', () => {
            actualizarSubestado('cocina', modalSelectCocina.value, btnUpdateCocina);
        });

        btnUpdateBarista?.addEventListener('click', () => {
            actualizarSubestado('barista', modalSelectBarista.value, btnUpdateBarista);
        });

        cerrarModalOrdenBtn.addEventListener('click', cerrarModalOrden);
        modalOrden.addEventListener('click', (event) => {
            if (event.target === modalOrden) {
                cerrarModalOrden();
            }
        });

        buscarOrdenInput?.addEventListener('input', aplicarFiltroOrdenes);
        filtroTipo?.addEventListener('change', aplicarFiltroOrdenes);
        filtroEstado?.addEventListener('change', aplicarFiltroOrdenes);

        cargarOrdenes();
        setInterval(cargarOrdenes, 5000);

$(document).ready(function () {
  if (document.querySelector("#tablaProductos")) {
    new DataTable("#tablaProductos", {
      pageLength: 10,
      language: {
        url: "https://cdn.datatables.net/plug-ins/2.1.8/i18n/es-ES.json",
      },
    });
  }

  if (document.querySelector("#tablaCategorias")) {
    new DataTable("#tablaCategorias", {
      pageLength: 8,
      language: {
        url: "https://cdn.datatables.net/plug-ins/2.1.8/i18n/es-ES.json",
      },
    });
  }

  // Validar tamaño de archivo (máximo 20 MB) al seleccionar la imagen
  $(document).on("change", 'input[type="file"]', function () {
    const file = this.files[0];
    if (file && file.size > 20 * 1024 * 1024) { // 20 MB en bytes
      Swal.fire({
        title: "Archivo demasiado grande",
        text: "La imagen pesa más de 20 MB. Por favor, selecciona una imagen más pequeña para que el sistema pueda procesarla sin problemas.",
        icon: "error",
        confirmButtonText: "Entendido",
      });
      // Limpiar el input para evitar que se envíe el formulario
      this.value = "";
    }
  });

  $(document).on("submit", ".form-eliminar-producto", function (e) {
    e.preventDefault();
    const form = this;

    Swal.fire({
      title: "¿Eliminar producto?",
      text: "Esta acción no se puede deshacer.",
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Sí, eliminar",
      cancelButtonText: "Cancelar",
      reverseButtons: true,
    }).then((r) => {
      if (r.isConfirmed) form.submit();
    });
  });

  $(document).on("submit", ".form-eliminar-categoria", function (e) {
    e.preventDefault();
    const form = this;

    Swal.fire({
      title: "¿Eliminar categoría?",
      text: "Solo se puede eliminar si no tiene productos asociados.",
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Sí, eliminar",
      cancelButtonText: "Cancelar",
      reverseButtons: true,
    }).then((r) => {
      if (r.isConfirmed) form.submit();
    });
  });

  $(document).on("click", ".btn-editar-categoria", function () {
    const btn = $(this);
    $("#editCategoriaId").val(btn.data("id"));
    $("#editCategoriaNombre").val(btn.data("nombre"));
    $("#editCategoriaSlug").val(btn.data("slug"));
    $("#editCategoriaIcono").val(btn.data("icono"));
    $("#editCategoriaOrden").val(btn.data("orden"));
    $("#editCategoriaActiva").prop("checked", Number(btn.data("activo")) === 1);
  });

  $(document).on("click", ".btn-editar-producto", function () {
    const btn = $(this);
    $("#editProductoId").val(btn.attr("data-id"));
    $("#editProductoNombre").val(btn.attr("data-nombre"));
    $("#editProductoCategoria").val(btn.attr("data-categoria_id"));
    $("#editProductoPrecio").val(btn.attr("data-precio"));
    $("#editProductoImagenUrl").val(btn.attr("data-imagen_url"));
    $("#editProductoActivo").prop("checked", Number(btn.attr("data-activo")) === 1);
    
    // Update preview
    const editContainer = $("#editProductoImagenUrl").closest('.gap-4').find('.preview-container');
    const fileInput = $("#editProductoImagenUrl").closest('.gap-4').find('.input-file-img');
    fileInput.val('');
    
    const fullUrl = btn.attr("data-imagen_full_url");
    if (fullUrl) {
        window.updatePreview(editContainer, fullUrl);
    } else {
        window.updatePreview(editContainer, '');
    }
  });

  // Validaciones antes de enviar el formulario de edición de productos
  $("#modalEditarProducto form").on("submit", function(e) {
    const nombre = $("#editProductoNombre").val().trim();
    const categoria = $("#editProductoCategoria").val();
    const precio = $("#editProductoPrecio").val();

    if (!nombre || !categoria || !precio || precio <= 0) {
      e.preventDefault();
      Swal.fire({
        icon: 'error',
        title: 'Faltan datos',
        text: 'Por favor completa el nombre, la categoría y asegura que el precio sea mayor a 0.',
        confirmButtonText: 'Entendido'
      });
    }
  });

  $("#btnEliminarImagenDirecto").on("click", function() {
    const idProducto = $("#editProductoId").val();
    if (!idProducto) return;
    
    Swal.fire({
      title: '¿Eliminar imagen?',
      text: 'La imagen se eliminará inmediatamente del sistema.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Sí, eliminar',
      cancelButtonText: 'Cancelar',
      reverseButtons: true
    }).then((result) => {
      if (result.isConfirmed) {
        $.post('/public/api/eliminarImagenProducto.php', { id: idProducto }, function(res) {
          if (res.success) {
            Swal.fire({
                icon: 'success', 
                title: 'Eliminada', 
                text: 'La imagen ha sido eliminada exitosamente.',
                timer: 1500,
                showConfirmButton: false
            });
            $("#editProductoImagenUrl").val("");
            const fileInput = $("#editProductoImagenUrl").closest('.gap-4').find('.input-file-img');
            fileInput.val('');
            
            // Clear preview
            const editContainer = $("#editProductoImagenUrl").closest('.gap-4').find('.preview-container');
            if (typeof window.updatePreview === 'function') window.updatePreview(editContainer, '');
            
            const btn = $(`.btn-editar-producto[data-id='${idProducto}']`);
            if (btn.length) {
               btn.attr('data-imagen_url', '');
               
               // Update the table row image without reloading
               const imgContainer = btn.closest('tr').find('td:first-child .flex.items-center.gap-3');
               if (imgContainer.length) {
                   imgContainer.find('img').remove();
                   if (imgContainer.find('.bg-\\[\\#F5EEE5\\]').length === 0) {
                       imgContainer.prepend('<div class="w-12 h-12 rounded-xl bg-[#F5EEE5] flex items-center justify-center text-brownSoft shadow-sm"><i class="fa-solid fa-image text-lg"></i></div>');
                   }
               }
            }
          } else {
            Swal.fire('Error', res.message || 'No se pudo eliminar la imagen.', 'error');
          }
        }, 'json').fail(function() {
          Swal.fire('Error', 'Error de conexión con el servidor.', 'error');
        });
      }
    });
  });

  // Lógica de Modificadores (Nuevo Grupo)
  const container = document.getElementById('opcionesContainer');
  const btnAdd = document.getElementById('btnAgregarOpcion');

  if (btnAdd && container) {
      btnAdd.addEventListener('click', () => {
          const row = document.createElement('div');
          row.className = 'flex gap-3 items-start dynamic-row';
          row.innerHTML = `
              <div class="flex-grow">
                  <input type="text" name="opciones_nombre[]" class="w-full px-4 py-2 rounded-xl border border-gray-300 text-sm focus:ring-2 focus:ring-mintGreen" placeholder="Nombre" required>
              </div>
              <div class="w-1/3">
                  <input type="number" step="0.01" name="opciones_precio[]" class="w-full px-4 py-2 rounded-xl border border-gray-300 text-sm focus:ring-2 focus:ring-mintGreen" placeholder="Precio Extra (₡)" value="0" required>
              </div>
              <div class="w-10 flex justify-center items-center pt-2">
                  <button type="button" class="text-red-400 hover:text-red-600 transition-colors" onclick="this.parentElement.parentElement.remove()">
                      <i class="fa-solid fa-trash"></i>
                  </button>
              </div>
          `;
          container.appendChild(row);
      });
  }

  // Envío del formulario de Nuevo Grupo de Modificadores
  $('#formNuevoGrupo').on('submit', function(e) {
      e.preventDefault();
      $.post(this.action, $(this).serialize(), function(res) {
          if(res.success) {
              location.reload();
          } else {
              Swal.fire('Error', res.message || "Desconocido", 'error');
          }
      }, 'json').fail(function() {
          Swal.fire('Error', "Error de conexión", 'error');
      });
  });
});

// Función Global para eliminar grupo de modificadores
window.eliminarGrupo = function(id) {
  Swal.fire({
      title: "¿Eliminar grupo de modificadores?",
      text: "Esta acción no se puede deshacer.",
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Sí, eliminar",
      cancelButtonText: "Cancelar",
      reverseButtons: true,
  }).then((r) => {
      if (r.isConfirmed) {
          $.post('/public/api/eliminarGrupoModificador.php', {id: id}, function(res) {
              if(res.success) location.reload();
              else Swal.fire('Error', res.message || "No se pudo eliminar", 'error');
          }, 'json');
      }
  });
};

$(document).ready(function () {
  // Poblado del Modal de Edición de Grupo
  $(document).on('click', '.btn-editar-grupo', function() {
      const grupo = JSON.parse($(this).attr('data-grupo'));
      
      $('#editGrupoId').val(grupo.id);
      $('#editGrupoNombre').val(grupo.nombre);
      $('#editReqSwitch').prop('checked', parseInt(grupo.requerido) === 1);
      $('#editMulSwitch').prop('checked', parseInt(grupo.seleccion_multiple) === 1);
      
      // Limpiar y cargar opciones
      const container = document.getElementById('editOpcionesContainer');
      container.innerHTML = '';
      if (grupo.opciones && grupo.opciones.length > 0) {
          grupo.opciones.forEach(opt => {
              const row = document.createElement('div');
              row.className = 'flex gap-3 items-start dynamic-row';
              row.innerHTML = `
                  <div class="flex-grow">
                      <input type="text" name="opciones_nombre[]" class="w-full px-4 py-2 rounded-xl border border-gray-300 text-sm focus:ring-2 focus:ring-mintGreen" value="${opt.nombre}" required>
                  </div>
                  <div class="w-1/3">
                      <input type="number" step="0.01" name="opciones_precio[]" class="w-full px-4 py-2 rounded-xl border border-gray-300 text-sm focus:ring-2 focus:ring-mintGreen" value="${opt.precio_adicional}" required>
                  </div>
                  <div class="w-10 flex justify-center items-center pt-2">
                      <button type="button" class="text-red-400 hover:text-red-600 transition-colors" onclick="this.parentElement.parentElement.remove()">
                          <i class="fa-solid fa-trash"></i>
                      </button>
                  </div>
              `;
              container.appendChild(row);
          });
      }
      
      // Checkboxes de categorías
      $('.edit-cat-cb').prop('checked', false);
      if (grupo.categorias && grupo.categorias.length > 0) {
          grupo.categorias.forEach(catId => {
              $(`.edit-cat-cb[value="${catId}"]`).prop('checked', true);
          });
      }
      
      // Checkboxes de productos
      $('.edit-prod-cb').prop('checked', false);
      if (grupo.productos && grupo.productos.length > 0) {
          grupo.productos.forEach(prodId => {
              $(`.edit-prod-cb[value="${prodId}"]`).prop('checked', true);
          });
      }
  });

  // Agregar fila en Edición
  $('#btnEditAgregarOpcion').on('click', function() {
      const container = document.getElementById('editOpcionesContainer');
      const row = document.createElement('div');
      row.className = 'flex gap-3 items-start dynamic-row';
      row.innerHTML = `
          <div class="flex-grow">
              <input type="text" name="opciones_nombre[]" class="w-full px-4 py-2 rounded-xl border border-gray-300 text-sm focus:ring-2 focus:ring-mintGreen" placeholder="Nombre" required>
          </div>
          <div class="w-1/3">
              <input type="number" step="0.01" name="opciones_precio[]" class="w-full px-4 py-2 rounded-xl border border-gray-300 text-sm focus:ring-2 focus:ring-mintGreen" placeholder="Precio" value="0" required>
          </div>
          <div class="w-10 flex justify-center items-center pt-2">
              <button type="button" class="text-red-400 hover:text-red-600 transition-colors" onclick="this.parentElement.parentElement.remove()">
                  <i class="fa-solid fa-trash"></i>
              </button>
          </div>
      `;
      container.appendChild(row);
  });

  // Enviar form edición
  $('#formEditarGrupo').on('submit', function(e) {
      e.preventDefault();
      $.post(this.action, $(this).serialize(), function(res) {
          if(res.success) {
              location.reload();
          } else {
              Swal.fire('Error', res.message || "Desconocido", 'error');
          }
      }, 'json').fail(function() {
          Swal.fire('Error', "Error de conexión", 'error');
      });
  });

  // Image Preview Logic
  window.updatePreview = function(container, src) {
      const img = container.find('.preview-img');
      const icon = container.find('.preview-icon');
      if (src) {
          img.attr('src', src).removeClass('hidden');
          icon.addClass('hidden');
      } else {
          img.attr('src', '').addClass('hidden');
          icon.removeClass('hidden');
      }
  };

  $('.input-url-img').on('input', function() {
      const url = $(this).val();
      const container = $(this).closest('.gap-4').find('.preview-container');
      
      if (url) {
          window.updatePreview(container, url);
      } else {
          const fileInput = $(this).closest('.gap-4').find('.input-file-img')[0];
          if (!fileInput.files || !fileInput.files[0]) {
              window.updatePreview(container, '');
          }
      }
  });

  $('.input-file-img').on('change', function() {
      const file = this.files[0];
      const container = $(this).closest('.gap-4').find('.preview-container');
      const urlInput = $(this).closest('.gap-4').find('.input-url-img');
      
      if (file) {
          const reader = new FileReader();
          reader.onload = function(e) {
              window.updatePreview(container, e.target.result);
          }
          reader.readAsDataURL(file);
      } else {
          window.updatePreview(container, urlInput.val());
      }
  });

  window.openFullscreenPreview = function(elem) {
      const img = $(elem).find('.preview-img');
      if (!img.hasClass('hidden') && img.attr('src')) {
          $('#lightboxImage').attr('src', img.attr('src'));
          new bootstrap.Modal(document.getElementById('modalImagePreview')).show();
      }
  };

  window.openFullscreenPreviewFromTable = function(imgElem) {
      const src = $(imgElem).attr('src');
      if (src) {
          $('#lightboxImage').attr('src', src);
          new bootstrap.Modal(document.getElementById('modalImagePreview')).show();
      }
  };

  // Form Importar PDF via AJAX
  $('#formImportarMenu').on('submit', function(e) {
      e.preventDefault();
      
      const form = this;
      const formData = new FormData(form);
      const submitBtn = $('#btnSubmitImport');
      const retryBtn = $('#btnRetryImport');
      const cancelBtn = $('#btnCancelImport');
      const loading = $('#aiLoadingIndicator');
      
      submitBtn.addClass('hidden');
      retryBtn.addClass('hidden');
      cancelBtn.prop('disabled', true);
      loading.removeClass('hidden');
      
      $.ajax({
          url: form.action,
          type: 'POST',
          data: formData,
          processData: false,
          contentType: false,
          dataType: 'json',
          success: function(res) {
              loading.addClass('hidden');
              if (res.success) {
                  Swal.fire('¡Éxito!', res.message, 'success').then(() => {
                      location.reload();
                  });
              } else {
                  retryBtn.removeClass('hidden');
                  cancelBtn.prop('disabled', false);
                  Swal.fire('Error', res.message || 'Error al importar menú', 'error');
              }
          },
          error: function(xhr, status, error) {
              loading.addClass('hidden');
              retryBtn.removeClass('hidden');
              cancelBtn.prop('disabled', false);
              Swal.fire('Error', 'Hubo un problema de conexión o el servidor tardó demasiado.', 'error');
          }
      });
  });

  // Buscador de productos en modal Nuevo Grupo
  $('#search-new-products').on('input', function() {
      const term = $(this).val().toLowerCase();
      $('#newProductsContainer label').each(function() {
          const name = $(this).find('span').text().toLowerCase();
          $(this).toggle(name.includes(term));
      });
  });

  // Buscador de productos en modal Editar Grupo
  $('#search-edit-products').on('input', function() {
      const term = $(this).val().toLowerCase();
      $('#editProductosContainer label').each(function() {
          const name = $(this).find('span').text().toLowerCase();
          $(this).toggle(name.includes(term));
      });
  });
});

// ==========================================
// ICON PICKER LOGIC
// ==========================================
const curatedIcons = [
    'fa-tags', 'fa-burger', 'fa-pizza-slice', 'fa-hotdog', 'fa-mug-hot', 'fa-cup-togo', 
    'fa-wine-glass', 'fa-martini-glass-citrus', 'fa-beer-mug-empty', 'fa-ice-cream', 
    'fa-cake-candles', 'fa-bowl-food', 'fa-bread-slice', 'fa-bacon', 'fa-cheese', 
    'fa-fish', 'fa-shrimp', 'fa-drumstick-bite', 'fa-egg', 'fa-apple-whole', 
    'fa-carrot', 'fa-leaf', 'fa-pepper-hot', 'fa-cookie', 'fa-candy-cane', 
    'fa-utensils', 'fa-spoon', 'fa-bowl-rice', 'fa-fire-burner', 'fa-plate-wheat',
    'fa-glass-water', 'fa-bottle-water', 'fa-mug-saucer', 'fa-star', 
    'fa-heart', 'fa-fire', 'fa-award', 'fa-crown', 'fa-bell', 'fa-bolt'
];

function populateIconPicker(pickerId, inputId) {
    const picker = document.getElementById(pickerId);
    if (!picker) return;
    
    picker.innerHTML = '';
    curatedIcons.forEach(icon => {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'w-10 h-10 flex items-center justify-center rounded-lg bg-gray-50 hover:bg-mintGreen hover:text-white text-brownSoft transition-colors text-lg';
        btn.innerHTML = `<i class="fa-solid ${icon}"></i>`;
        btn.onclick = () => {
            const input = document.getElementById(inputId);
            if (input) {
                input.value = icon;
                updateIconPreview(inputId);
            }
            picker.classList.add('hidden');
        };
        picker.appendChild(btn);
    });
}

function toggleIconPicker(inputId) {
    const picker = document.getElementById('picker-' + inputId);
    if (picker) {
        if (picker.innerHTML.trim() === '') {
            populateIconPicker('picker-' + inputId, inputId);
        }
        picker.classList.toggle('hidden');
    }
}

function updateIconPreview(inputId) {
    const input = document.getElementById(inputId);
    const preview = document.getElementById('preview-' + inputId);
    if (input && preview) {
        preview.className = `fa-solid ${input.value} absolute left-3 top-1/2 transform -translate-y-1/2 text-mintGreen`;
    }
}

document.addEventListener('DOMContentLoaded', () => {
    // Escuchar cambios manuales en los inputs para actualizar el preview
    ['newCategoriaIcono', 'editCategoriaIcono'].forEach(id => {
        const input = document.getElementById(id);
        if (input) {
            input.addEventListener('input', () => updateIconPreview(id));
            updateIconPreview(id); // Inicial
        }
    });

    // Cerrar el picker al hacer click fuera
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.flex.gap-2.relative')) {
            document.querySelectorAll('[id^="picker-"]').forEach(p => p.classList.add('hidden'));
        }
    });
});

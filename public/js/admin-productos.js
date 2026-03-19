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
});

$(document).ready(function () {
  new DataTable("#tablaProductos", {
    pageLength: 10,
    language: {
      url: "https://cdn.datatables.net/plug-ins/2.1.8/i18n/es-ES.json",
    },
  });

  // Confirmación eliminar
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
});

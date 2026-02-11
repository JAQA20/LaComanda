$(function () {
  new DataTable("#tablaUsuarios", {
    pageLength: 10,
    lengthMenu: [10, 25, 50, 100],
    order: [[0, "asc"]],
    language: {
      search: "Buscar:",
      lengthMenu: "Mostrar _MENU_",
      info: "Mostrando _START_ a _END_ de _TOTAL_",
      infoEmpty: "Sin registros",
      zeroRecords: "No se encontraron resultados",
      paginate: { previous: "Prev", next: "Next" },
    },
  });

  // Placeholder eliminar (luego lo conectamos con controller + SweetAlert)
  $(document).on("click", ".btn-delete", function () {
    const id = $(this).data("id");
    alert("Eliminar usuario ID: " + id + " (pendiente conectar)");
  });
});

// Eliminar usuario con confirmación SweetAlert
document.addEventListener("DOMContentLoaded", () => {
  const forms = document.querySelectorAll(".form-eliminar-usuario");

  forms.forEach((form) => {
    form.addEventListener("submit", async (e) => {
      e.preventDefault();

      const result = await Swal.fire({
        title: "¿Eliminar usuario?",
        text: "Esta acción no se puede deshacer.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Sí, eliminar",
        cancelButtonText: "Cancelar",
        reverseButtons: true,
      });

      if (result.isConfirmed) form.submit();
    });
  });
});


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

  // Llenar Modal de Edición
  $(document).on("click", ".btn-editar-usuario", function () {
    const btn = $(this);
    $("#editUsuarioId").val(btn.attr("data-id"));
    $("#editUsuarioNombre").val(btn.attr("data-nombre"));
    $("#editUsuarioApellido").val(btn.attr("data-apellido"));
    $("#editUsuarioEmail").val(btn.attr("data-email"));
    
    // Set value and remember the original role
    const rol = btn.attr("data-rol_id");
    $("#editUsuarioRol").val(rol).attr("data-original-rol", rol);
    
    $("#editUsuarioPassword").val("");
    $("#editUsuarioPassword2").val("");
  });

  // Validaciones antes de crear usuario
  $("#formNuevoUsuario").on("submit", function(e) {
    const p1 = $("#nuevoUsuarioPassword").val();
    const p2 = $("#nuevoUsuarioPasswordConfirm").val();
    if (p1 !== p2) {
      e.preventDefault();
      Swal.fire({ icon: 'error', title: 'Error', text: 'Las contraseñas no coinciden.' });
    }
  });

  // Flujo de Seguridad al Editar Usuario (contraseña o rol vs cambios simples)
  $("#formEditarUsuario").on("submit", async function(e) {
    e.preventDefault();
    const form = this;
    const p1 = $("#editUsuarioPassword").val();
    const p2 = $("#editUsuarioPassword2").val();
    
    const originalRol = $("#editUsuarioRol").attr("data-original-rol");
    const currentRol = $("#editUsuarioRol").val();
    const rolChanged = (originalRol !== currentRol);
    const passwordChanged = (p1 !== "" || p2 !== "");

    // Si cambian contraseña, validamos que coincida
    if (passwordChanged) {
      if (p1 !== p2) {
        return Swal.fire({ icon: 'error', title: 'Error', text: 'Las nuevas contraseñas no coinciden.' });
      }
      if (p1.length < 8) {
        return Swal.fire({ icon: 'error', title: 'Error', text: 'La contraseña debe tener al menos 8 caracteres.' });
      }
    }

    const needsAdminValidation = (passwordChanged || rolChanged);

    if (needsAdminValidation) {
      // 1. Mostrar advertencia y enviar código
      const confirmSend = await Swal.fire({
        target: document.getElementById('modalEditarUsuario'),
        title: 'Verificación de Seguridad',
        text: 'Estás a punto de modificar datos sensibles (rol o contraseña). Necesitamos enviarte un código de seguridad de 6 dígitos a tu correo.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#70A38F',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Enviar Código',
        cancelButtonText: 'Cancelar'
      });

      if (!confirmSend.isConfirmed) return;

      // Mostrar cargando
      Swal.fire({
        target: document.getElementById('modalEditarUsuario'),
        title: 'Enviando código...',
        text: 'Por favor espera',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
      });

      try {
        const reqResponse = await fetch(BASE_URL + 'public/api/requestAdminOTP.php', {
          method: 'POST'
        });
        const reqResult = await reqResponse.json();

        if (!reqResponse.ok || !reqResult.success) {
          throw new Error(reqResult.message || 'Error al enviar código');
        }

        // 2. Pedir el código al usuario
        const { value: otpCode } = await Swal.fire({
          target: document.getElementById('modalEditarUsuario'),
          title: 'Código de Seguridad',
          text: 'Ingresa el código de 6 dígitos que enviamos a tu correo',
          input: 'text',
          inputAttributes: {
            autocapitalize: 'off',
            maxlength: 6,
            pattern: '[0-9]*',
            style: 'text-align: center; font-size: 24px; letter-spacing: 4px;'
          },
          showCancelButton: true,
          confirmButtonColor: '#70A38F',
          cancelButtonColor: '#d33',
          confirmButtonText: 'Verificar',
          cancelButtonText: 'Cancelar',
          inputValidator: (value) => {
            if (!value || value.length !== 6) return 'Debes ingresar un código de 6 dígitos';
          }
        });

        if (otpCode) {
          Swal.fire({
            target: document.getElementById('modalEditarUsuario'),
            title: 'Verificando...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
          });

          // 3. Verificar el código
          const verifyResponse = await fetch(BASE_URL + 'public/api/verifyAdminOTP.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ otpCode: otpCode })
          });
          
          let verifyResult;
          const text = await verifyResponse.text();
          try {
            verifyResult = JSON.parse(text);
          } catch(e) {
            throw new Error(`Invalid JSON: ${text}`);
          }

          if (!verifyResponse.ok || !verifyResult.success) {
            Swal.fire({
              target: document.getElementById('modalEditarUsuario'),
              icon: 'error',
              title: 'Error de verificación',
              text: verifyResult.message || 'Código incorrecto o expirado.',
              confirmButtonColor: '#70A38F'
            });
            return;
          }
          
          // Todo correcto, mostrar confirmación antes de enviar
          Swal.fire({
            target: document.getElementById('modalEditarUsuario'),
            title: 'OTP Verificado',
            text: 'El código es correcto. ¿Deseas aplicar estos cambios al usuario?',
            icon: 'success',
            showCancelButton: true,
            confirmButtonColor: '#70A38F',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, guardar cambios',
            cancelButtonText: 'Cancelar'
          }).then((res) => {
            if (res.isConfirmed) {
              form.submit();
            }
          });
        }

      } catch (err) {
        console.error("Error en OTP:", err);
        Swal.fire({
          target: document.getElementById('modalEditarUsuario'),
          icon: 'error',
          title: 'Error',
          text: err.message || 'Error de conexión.',
          confirmButtonColor: '#70A38F'
        });
      }
    } else {
      // Flujo simple: solo cambiaron datos básicos
      const confirmBasic = await Swal.fire({
        title: 'Confirmar actualización',
        text: '¿Estás seguro de hacer estos cambios en el perfil del usuario?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, guardar',
        cancelButtonText: 'Cancelar'
      });

      if (confirmBasic.isConfirmed) {
        form.submit();
      }
    }
  });
});


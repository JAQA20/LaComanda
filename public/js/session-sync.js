(function () {
  const script = document.currentScript;
  const src = script ? script.src : "";
  const baseUrl = src.includes("public/js/session-sync.js")
    ? src.slice(0, src.indexOf("public/js/session-sync.js"))
    : "/";
  const logoutKey = "laComandaLogoutAt";
  const loginPath = "views/login.php?logged_out=1";

  function isLoginPage() {
    return window.location.pathname.endsWith("/views/login.php");
  }

  function redirectToLogin() {
    if (isLoginPage()) return;
    window.location.replace(baseUrl + loginPath);
  }

  window.addEventListener("storage", (event) => {
    if (event.key === logoutKey && event.newValue) {
      redirectToLogin();
    }
  });

  window.LaComandaSessionSync = {
    notifyLogout: function () {
      try {
        localStorage.setItem(logoutKey, String(Date.now()));
      } catch (error) {
        console.warn("No se pudo notificar el cierre de sesión:", error);
      }
    },
    clearLogout: function () {
      try {
        localStorage.removeItem(logoutKey);
      } catch (error) {
        console.warn("No se pudo limpiar el estado de cierre de sesión:", error);
      }
    },
  };
})();

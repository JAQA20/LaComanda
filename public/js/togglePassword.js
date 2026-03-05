document.addEventListener("DOMContentLoaded", () => {
  const btn = document.getElementById("togglePassword");
  const input = document.getElementById("password");

  if (!btn || !input) return;

  btn.addEventListener("click", () => {
    const isPass = input.type === "password";
    input.type = isPass ? "text" : "password";
    btn.innerHTML = isPass
      ? '<i class="fa-solid fa-eye-slash"></i>'
      : '<i class="fa-solid fa-eye"></i>';
  });
});

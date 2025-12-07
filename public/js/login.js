document.getElementById("loginForm").addEventListener("submit", async (e) => {
  e.preventDefault();

  const payload = {
    email: email.value,
    password: password.value
  };

  const res = await fetch(`${API}/auth/login`, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(payload)
  });

  const data = await res.json();

  if (res.ok) {

    // 1. LIMPIAR VALORES ANTERIORES
    localStorage.removeItem("accessToken");
    localStorage.removeItem("refreshToken");
    localStorage.removeItem("user");
    localStorage.removeItem("sessionExpiresAt");

    // 2. GUARDAR NUEVOS TOKENS Y USUARIO
    saveTokens(data.access, data.refresh);
    localStorage.setItem("user", JSON.stringify(data.user));

    // 3. CREAR NUEVO TIEMPO DE EXPIRACIÓN: 1 MINUTO DESDE AHORA
    const expiresAt = Date.now() + 60 * 1000;
    localStorage.setItem("sessionExpiresAt", String(expiresAt));

    // 4. IR AL DASHBOARD
    location.href = "dashboard.html";

  } else {
    msg.textContent = data.msg;
    msg.className = "error";
  }
});

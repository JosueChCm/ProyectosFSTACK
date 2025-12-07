const API = "http://localhost:3977/api/v1";

/* Obtener tokens */
function getAccessToken() {
  return localStorage.getItem("accessToken");
}

function getRefreshToken() {
  return localStorage.getItem("refreshToken");
}

/* Guardar tokens */
function saveTokens(access, refresh) {
  localStorage.setItem("accessToken", access);
  localStorage.setItem("refreshToken", refresh);
}

/* Eliminar tokens */
function clearTokens() {
  localStorage.removeItem("accessToken");
  localStorage.removeItem("refreshToken");
  localStorage.removeItem("user");
}

/* Mostrar / ocultar contraseña */
function togglePass(id) {
  const i = document.getElementById(id);
  i.type = i.type === "password" ? "text" : "password";
}

/* Decodificar token */
function parseJwt(token) {
  try {
    const base64Url = token.split(".")[1];
    const base64 = base64Url.replace(/-/g, "+").replace(/_/g, "/");
    const json = atob(base64);
    return JSON.parse(json);
  } catch (e) {
    return null;
  }
}

/* Detectar expiración del token y cerrar sesión automáticamente */
function checkTokenExpiration() {
  const token = getAccessToken();
  if (!token) return;

  const payload = parseJwt(token);
  if (!payload) return;

  const expiresAt = payload.exp; // ya viene en ms
  const now = Date.now();

  if (now >= expiresAt) {
    alert("Tu sesión ha expirado.");
    clearTokens();
    window.location.href = "login.html";
  }
}

/* Revisión de expiración cada 5 segundos */
setInterval(checkTokenExpiration, 5000);

/* 🚫  IMPORTANTE: Eliminar completamente la renovación automática */

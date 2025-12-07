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
  localStorage.removeItem("sessionExpiresAt");
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

// 🚫 BORRADO: La función checkTokenExpiration
// 🚫 BORRADO: El setInterval cada 5 segundos
// 🚫 YA NO SE USA EXP DEL TOKEN PARA VALIDAR DESDE EL FRONT

console.warn("API.JS CARGADO DESDE:", document.currentScript.src);

/* ============================
   CONTADOR DE EXPIRACIÓN
============================ */
let countdownInterval;

function startSessionCountdown() {
  clearInterval(countdownInterval);

  const expiresAt = Number(localStorage.getItem("sessionExpiresAt"));

  // si no existe o ya expiró → sesión expirada
  if (!expiresAt || expiresAt <= Date.now()) {
    const timer = document.getElementById("sessionTimer");
    if (timer) timer.textContent = "00:00";
    alert("Tu sesión ha expirado.");
    clearTokens();
    window.location.href = "login.html";
    return;
  }

  const timer = document.getElementById("sessionTimer");

  function updateTimer() {
    const now = Date.now();
    let diff = expiresAt - now;

    // si llega a 0 → cerrar sesión
    if (diff <= 0) {
      timer.textContent = "00:00";
      alert("Tu sesión ha expirado.");
      clearTokens();
      window.location.href = "login.html";
      return;
    }

    // convertir a mm:ss
    let sec = Math.floor(diff / 1000);
    let min = Math.floor(sec / 60);
    sec = sec % 60;

    timer.textContent =
      `${min.toString().padStart(2,"0")}:${sec.toString().padStart(2,"0")}`;
  }

  updateTimer();
  countdownInterval = setInterval(updateTimer, 1000);
}

/* ===== INICIAR CONTADOR DESPUÉS QUE EL DOM CARGUE ===== */
document.addEventListener("DOMContentLoaded", () => {
  setTimeout(() => {
    startSessionCountdown();
  }, 150); // pequeño delay para asegurar que el token y sessionExpiresAt ya existen
});


/* ============================
   NAVIGATION
============================ */
function showSection(id) {
  document.querySelectorAll(".section").forEach(s => s.style.display = "none");
  document.getElementById(id).style.display = "block";

  document.querySelectorAll(".sidebar a").forEach(a => a.classList.remove("active"));
  document.querySelector(`[onclick="showSection('${id}')"]`).classList.add("active");
}


/* ============================
   LOAD USER DATA
============================ */
const user = JSON.parse(localStorage.getItem("user"));
if (!user) location.href = "login.html";

document.getElementById("userEmail").textContent = user.email;
document.getElementById("userName").textContent = `${user.firstname} ${user.lastname}`;
document.getElementById("usernameHeader").textContent = `Bienvenido, ${user.firstname} ${user.lastname}`;
document.getElementById("username").textContent = `${user.firstname} ${user.lastname}`;
document.getElementById("accessBox").textContent = getAccessToken();


/* ============================
   UPDATE USER
============================ */
async function updateUser() {
  const payload = {};

  if (editFirstname.value) payload.firstname = editFirstname.value;
  if (editLastname.value) payload.lastname = editLastname.value;
  if (editEmail.value) payload.email = editEmail.value;
  if (editPassword.value) payload.password = editPassword.value;

  const res = await fetch(`${API}/user`, {
    method: "PUT",
    headers: {
      "Content-Type": "application/json",
      "Authorization": "Bearer " + getAccessToken()
    },
    body: JSON.stringify(payload)
  });

  const data = await res.json();

  if (res.ok) {
    profileMsg.textContent = "Datos actualizados!";
    profileMsg.className = "success";

    if (payload.firstname) user.firstname = payload.firstname;
    if (payload.lastname) user.lastname = payload.lastname;
    if (payload.email) user.email = payload.email;

    localStorage.setItem("user", JSON.stringify(user));

  } else {
    profileMsg.textContent = data.msg;
    profileMsg.className = "error";
  }
}


/* ============================
   REFRESH TOKEN MANUAL
============================ */
async function refreshToken() {
  const refresh = getRefreshToken();

  const res = await fetch(`${API}/auth/refreshAccessToken`, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ token: refresh })
  });

  const data = await res.json();

  if (res.ok && data.accessToken) {

    // actualizar access token
    localStorage.setItem("accessToken", data.accessToken);
    accessBox.innerText = data.accessToken;

    // NUEVO TIEMPO: +1 minuto
    const expiresAt = Date.now() + 60 * 1000;
    localStorage.setItem("sessionExpiresAt", expiresAt);

    startSessionCountdown();

    alert("Token renovado por 1 minuto más.");
  } else {
    alert("No se pudo renovar token.");
  }
}


/* ============================
   LOGOUT
============================ */
function logout() {
  clearTokens();
  localStorage.removeItem("user");
  localStorage.removeItem("sessionExpiresAt");
  location.href = "login.html";
}


/* ============================
   GRAFICO EJEMPLO
============================ */
var options = {
  chart: {
    type: 'line',
    height: 350,
    foreColor: '#f3f4f6'
  },
  theme: {
    mode: 'dark',
  },
  series: [{
    name: 'Usuarios nuevos',
    data: [5, 20, 14, 32, 40, 55]
  }],
  xaxis: {
    categories: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun']
  }
};

var chart = new ApexCharts(document.querySelector("#grafico2"), options);
chart.render();

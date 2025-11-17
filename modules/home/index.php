<?php if (!defined('APP_INIT')) { http_response_code(403); exit; } ?>
<!-- Presentación -->
<section id="Presentacion" class="section site-scope visible">
  <h2>Bienvenido a mi página web - portafolio</h2>
  <hr />
  <div class="u-card">
    <h3>Proposito</h3>
    <p>La web es una interfaz de pruebas y un portafolio que permite almacenar proyectos de cada avance de los trabajos del curso de desarrollo wweb full stack</p>
  </div>
  <div class="grid cols-3">
    <article class="u-card"><h4>Objetivo</h4><p>Desarrollar habilidades de diseño y programacion de paginas en ambos aspectos de FrontEnd y Backend.</p></article>
    <article class="u-card"><h4>Estructura</h4><p>Desarrollo de CSS, JS, HTML, PHP. Aprendisaje de estructuracion mediante MySQL para el desarrollo de base de datos implementadas a la web.</p></article>
    <article class="u-card"><h4>Proyeccion</h4><p>Crear un dashboard estable y fuincional con la implementacion de base de datos para el manejo de informacion por usuario y la interconexion con diferentes plataformas.</p></article>
  </div>
</section>

<!-- Contáctame (con EmailJS tal cual tenías) -->
<section id="contactame" class="section site-scope">
  <h2>Contáctame</h2>
  <hr />

  <div class="ct-shell">
    <div class="ct-card">
      <aside class="ct-side">
        <div class="ct-badge"><i class="fa-regular fa-paper-plane"></i></div>
        <h3 class="ct-title">¿Trabajamos juntos?</h3>
        <p class="ct-sub">Cuéntame de tu idea o proyecto. Suelo responder rápido 🙂</p>
        <ul class="ct-list">
          <li><i class="fa-solid fa-circle-check"></i> Asesoría personalizada</li>
          <li><i class="fa-solid fa-circle-check"></i> Respuesta en menos de 24h</li>
          <li><i class="fa-solid fa-circle-check"></i> Español / Inglés</li>
        </ul>
      </aside>

      <div class="ct-form">
        <form id="contactameForm" novalidate onsubmit="enviarCorreo(event)">
          <div class="ct-grid">
            <div class="ct-field">
              <input id="nombre" name="fullname" type="text" class="ct-input" placeholder=" " autocomplete="name" required />
              <label for="fullname" class="ct-label">Nombres completos</label>
              <span class="ct-bar"></span>
            </div>

            <div class="ct-field">
              <input id="email" name="email" type="email" class="ct-input" placeholder=" " autocomplete="email" required />
              <label for="email" class="ct-label">Correo</label>
              <span class="ct-bar"></span>
            </div>

            <div class="ct-field ct-span">
              <textarea id="mensaje" name="message" class="ct-input ct-textarea" rows="5" placeholder=" " required></textarea>
              <label for="message" class="ct-label">Mensaje</label>
              <span class="ct-bar"></span>
            </div>
          </div>

          <div class="ct-actions">
            <button type="submit" class="ct-btn">
              <span class="ct-shine"></span>
              <i class="fa-solid fa-paper-plane-top" style="transform: rotate(45deg)"></i>
              Enviar
            </button>
            <p id="contactameMsg" class="ct-msg" aria-live="polite"></p>
          </div>
        </form>

        <!-- EmailJS (igual que tu index) -->
        <script src="https://cdn.jsdelivr.net/npm/emailjs-com@3/dist/email.min.js"></script>
        <script>
          (function(){ emailjs.init("-h6KMTSTux-YCKMMA"); })();
          function enviarCorreo(e) {
            e.preventDefault();
            emailjs.send("service_xql77xw", "template_1c0bo5o", {
              name: document.getElementById("nombre").value,
              email: document.getElementById("email").value,
            });
            emailjs.send("service_xql77xw", "template_wroamds", {
              name: document.getElementById("nombre").value,
              email: document.getElementById("email").value,
              message: document.getElementById("mensaje").value
            })
            .then((res) => { alert("Correo enviado correctamente!"); console.log("Respuesta:", res); })
            .catch((err) => { alert("Error al enviar el correo."); console.error("Error:", err); });
          }
        </script>
      </div>
    </div>

    <div class="ct-blob ct-blob--1"></div>
    <div class="ct-blob ct-blob--2"></div>
  </div>
</section>

<!-- Punto 4 -->
<section id="punto4" class="section site-scope">
  <h2>Punto 4</h2>
  <hr />
  <div class="u-card"><p>Contenido pendiente…</p></div>
</section>

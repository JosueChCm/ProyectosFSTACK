import React from "react";

export default function Contact() {
  return (
    <section id="contact" className="section">
      <div className="container contact">
        <h2 className="section-title">Contacto</h2>
        <p className="card-text">
          ¿Te interesa colaborar conmigo en un proyecto, práctica o puesto
          junior? Estoy abierto a aprender y aportar.
        </p>

        <div className="contact-info">
          <p>
            ✉️ Correo:{" "}
            <a href="mailto:tu-correo@example.com">
              tu-correo@example.com
            </a>
          </p>
          <p>
            💻 GitHub:{" "}
            <a
              href="https://github.com/tu-usuario"
              target="_blank"
              rel="noreferrer"
            >
              github.com/tu-usuario
            </a>
          </p>
          <p>
            💼 LinkedIn:{" "}
            <a
              href="https://www.linkedin.com/in/tu-perfil/"
              target="_blank"
              rel="noreferrer"
            >
              linkedin.com/in/tu-perfil
            </a>
          </p>
        </div>
      </div>
    </section>
  );
}

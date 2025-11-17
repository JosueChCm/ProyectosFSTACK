import React from "react";

export default function Hero() {
  return (
    <header id="inicio" className="section hero">
      <div className="container hero-content">
        <p className="hero-pill">Desarrollo Web · Full Stack</p>
        <h1 className="hero-title">
          Hola, soy <span>Josue Chulluncuy</span>
        </h1>
        <p className="hero-subtitle">
          Desarrollador web en formación, construyendo interfaces limpias y
          aplicaciones completas de frontend y backend.
        </p>

        <div className="hero-actions">
          <a href="mailto:tu-correo@example.com" className="btn btn-primary">
            Escríbeme
          </a>
          <a
            href="https://github.com/tu-usuario"
            target="_blank"
            rel="noreferrer"
            className="btn btn-outline"
          >
            Ver GitHub
          </a>
        </div>
      </div>
    </header>
  );
}

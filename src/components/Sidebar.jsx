// src/components/Sidebar.jsx
import React from "react";

export default function Sidebar({ darkMode, onToggleDarkMode }) {
  return (
    <aside className="sidebar">
      <div className="sidebar-top">
        <div className="sidebar-header">
          <div className="sidebar-avatar">JC</div>
          <div>
            <p className="sidebar-name">Josue Chulluncuy</p>
            <p className="sidebar-role">Desarrollador Web</p>
          </div>
        </div>

        <nav className="sidebar-nav">
          <a href="#inicio">Inicio</a>
          <a href="#skills">Habilidades</a>
          <a href="#projects">Proyectos</a>
          <a href="#contact">Contacto</a>
        </nav>
      </div>

      <button className="mode-toggle" onClick={onToggleDarkMode}>
        {darkMode ? "☀️ Modo claro" : "🌙 Modo oscuro"}
      </button>
    </aside>
  );
}

// src/App.jsx
import React, { useState } from "react";
import Hero from "./components/Hero.jsx";
import Skills from "./components/Skills.jsx";
import Projects from "./components/Projects.jsx";
import Contact from "./components/Contact.jsx";
import Sidebar from "./components/Sidebar.jsx";

export default function App() {
  const [darkMode, setDarkMode] = useState(true); // empieza en modo oscuro

  return (
    <div className={`app-layout ${darkMode ? "theme-dark" : "theme-light"}`}>
      {/* Sidebar recibe el modo actual y la función para cambiarlo */}
      <Sidebar
        darkMode={darkMode}
        onToggleDarkMode={() => setDarkMode((prev) => !prev)}
      />

      {/* Contenido principal */}
      <div className="app">
        <Hero />
        <main>
          <Skills />
          <Projects />
          <Contact />
        </main>
        <footer className="footer">
          <p>
            © {new Date().getFullYear()} Josue Chulluncuy · Portafolio React
          </p>
        </footer>
      </div>
    </div>
  );
}

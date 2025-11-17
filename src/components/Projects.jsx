import React from "react";

const projects = [
  {
    title: "Landing Page Responsiva",
    description:
      "Página de presentación para un servicio ficticio, con diseño adaptativo y maquetación en CSS Grid/Flexbox.",
    tech: ["HTML", "CSS", "JavaScript"],
  },
  {
    title: "Mini App de Tareas",
    description:
      "Aplicación para gestionar tareas (CRUD básico) usando componentes de React y estado local.",
    tech: ["React", "CSS"],
  },
  {
    title: "Consumo de API Pública",
    description:
      "Pequeña app que consume una API pública y muestra la información en tarjetas.",
    tech: ["React", "Fetch API"],
  },
];

export default function Projects() {
  return (
    <section id="projects" className="section section-alt">
      <div className="container">
        <h2 className="section-title">Proyectos</h2>
        <div className="grid">
          {projects.map((project) => (
            <article key={project.title} className="card">
              <h3 className="card-title">{project.title}</h3>
              <p className="card-text">{project.description}</p>
              <div className="tags">
                {project.tech.map((t) => (
                  <span key={t} className="tag">
                    {t}
                  </span>
                ))}
              </div>
            </article>
          ))}
        </div>
      </div>
    </section>
  );
}

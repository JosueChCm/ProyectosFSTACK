import React from "react";

const skillsData = [
  {
    title: "Frontend",
    items: ["HTML5", "CSS3", "JavaScript", "React", "Responsive Design"],
  },
  {
    title: "Backend",
    items: ["Node.js", "Express", "APIs REST", "Autenticación básica"],
  },
  {
    title: "Herramientas",
    items: ["Git", "GitHub", "NPM", "Buenas prácticas"],
  },
];

export default function Skills() {
  return (
    <section id="skills" className="section">
      <div className="container">
        <h2 className="section-title">Habilidades</h2>
        <div className="grid">
          {skillsData.map((block) => (
            <div key={block.title} className="card">
              <h3 className="card-title">{block.title}</h3>
              <ul className="list">
                {block.items.map((skill) => (
                  <li key={skill}>{skill}</li>
                ))}
              </ul>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
}

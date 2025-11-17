/* =============================================================
   SCRIPT — Core del panel
   - Tema (dark/light) unificado (topbar + FAB)
   - Router por hash (secciones del index)
   - Sidebar móvil (toggle + accesibilidad)
   - Submenús (genérico + casos específicos)
   - Popover de usuario (si existe)
   - Contacto (validación y feedback)
   ============================================================= */
(() => {
  'use strict';

  /* -----------------------------------------------------------
     0) HELPERS UNIVERSALES
     ----------------------------------------------------------- */
  const $  = (sel, ctx = document) => ctx.querySelector(sel);
  const $$ = (sel, ctx = document) => Array.from(ctx.querySelectorAll(sel));
  const on = (el, ev, fn, opts) => el && el.addEventListener(ev, fn, opts);

  const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  // Año dinámico (footer)
  (() => {
    const yearEl = $('#year');
    if (yearEl) yearEl.textContent = String(new Date().getFullYear());
  })();

  /* -----------------------------------------------------------
     1) TEMA (DARK/LIGHT) — UNIFICADO
     - Usa localStorage("theme")
     - Actualiza atributos de <html> (data-theme, data-bs-theme)
     - Sincroniza iconos del topbar y FAB
     ----------------------------------------------------------- */
  const Theme = (() => {
    const ROOT = document.documentElement;
    const KEY  = 'theme';
    const get  = () => localStorage.getItem(KEY) || ROOT.getAttribute('data-theme') || 'light';
    const set  = (v) => {
      localStorage.setItem(KEY, v);
      ROOT.setAttribute('data-theme', v);
      ROOT.setAttribute('data-bs-theme', v === 'dark' ? 'dark' : 'light'); // compat Bootstrap
      syncIcons();
    };
    const toggle = () => set(get() === 'dark' ? 'light' : 'dark');

    // Sincroniza iconos (topbar + FAB)
    const syncIcons = () => {
      const isDark = get() === 'dark';
      const topBtn = $('#themeToggle');
      if (topBtn) {
        topBtn.innerHTML = isDark ? '<i class="fa-solid fa-sun"></i>' : '<i class="fa-solid fa-moon"></i>';
        topBtn.title = isDark ? 'Cambiar a claro' : 'Cambiar a oscuro';
      }
      let fab = $('#modeFab');
      if (!fab) {
        // Crea FAB solo si no hay botón en topbar
        fab = document.createElement('button');
        fab.id = 'modeFab';
        fab.className = 'theme-fab';
        fab.type = 'button';
        fab.setAttribute('aria-label', 'Cambiar tema');
        document.body.appendChild(fab);
        on(fab, 'click', toggle);
      }
      fab.innerHTML = isDark ? '<i class="fa-solid fa-sun"></i>' : '<i class="fa-solid fa-moon"></i>';
      fab.title = isDark ? 'Cambiar a claro' : 'Cambiar a oscuro';
      // Asegura visibilidad del FAB (por si CSS lo oculta accidentalmente)
      fab.style.display = 'grid';
    };

    // Init: aplica tema guardado (o atributo existente) y sincroniza iconos
    set(get());
    // Eventos
    on($('#themeToggle'), 'click', toggle);

    return { get, set, toggle };
  })();

  /* -----------------------------------------------------------
     2) SIDEBAR MÓVIL — TOGGLE + ACCESIBILIDAD
     - Alterna clase .open en #sidebar
     - Actualiza aria-expanded del botón
     - Cierra con ESC y al navegar por enlaces de la propia sidebar
     ----------------------------------------------------------- */
  (() => {
    const sidebar   = $('#sidebar');        // <aside id="sidebar" class="sidebar app-sidebar">
    const btnToggle = $('#btnSidebar');     // botón hamburguesa en topbar
    if (!sidebar || !btnToggle) return;

    const closeSidebar = () => {
      sidebar.classList.remove('open');
      btnToggle.setAttribute('aria-expanded', 'false');
    };
    const toggleSidebar = () => {
      const isOpen = sidebar.classList.toggle('open');
      btnToggle.setAttribute('aria-expanded', String(isOpen));
    };

    on(btnToggle, 'click', toggleSidebar);

    // Cierra con ESC
    on(document, 'keydown', (e) => {
      if (e.key === 'Escape') closeSidebar();
    });

    // Al hacer click en un link del menú, cierra la sidebar en móvil
    $$('.menu a, .menu button').forEach(el => {
      on(el, 'click', () => {
        if (window.matchMedia('(max-width: 1023px)').matches) closeSidebar();
      });
    });
  })();

  /* -----------------------------------------------------------
     3) SUBMENÚS — GENÉRICO
     - Para botones con data-toggle="submenu" que controlan un panel
     - También soporta el patrón específico vendorsToggle/vendorsMenu
     ----------------------------------------------------------- */
  (() => {
    // Handler general por atributo:
    // <button data-toggle="submenu" aria-controls="ID_DEL_PANEL" aria-expanded="false">
    const toggles = $$('button[data-toggle="submenu"][aria-controls]');
    toggles.forEach(btn => {
      const panelId = btn.getAttribute('aria-controls');
      const panel   = panelId ? document.getElementById(panelId) : null;
      if (!panel) return;

      const open  = () => { panel.removeAttribute('hidden'); panel.offsetHeight; panel.classList.add('open'); };
      const close = () => {
        panel.classList.remove('open');
        const end = () => panel.setAttribute('hidden','');
        prefersReduced ? end() : setTimeout(end, 220);
      };

      on(btn, 'click', () => {
        const expanded = btn.getAttribute('aria-expanded') === 'true';
        btn.setAttribute('aria-expanded', String(!expanded));
        expanded ? close() : open();
      });
    });

    // Compatibilidad con IDs que ya usabas
    const bindPair = (btnId, panelId) => {
      const btn   = document.getElementById(btnId);
      const panel = document.getElementById(panelId);
      if (!btn || !panel) return;
      on(btn, 'click', () => {
        const expanded = btn.getAttribute('aria-expanded') === 'true';
        btn.setAttribute('aria-expanded', String(!expanded));
        if (expanded) {
          panel.classList.remove('open');
          prefersReduced ? panel.setAttribute('hidden','') : setTimeout(()=>panel.setAttribute('hidden',''),220);
        } else {
          panel.removeAttribute('hidden'); panel.offsetHeight; panel.classList.add('open');
        }
      });
    };
    bindPair('projToggle', 'projMenu');
    bindPair('backendToggle', 'backendMenu');
    bindPair('vendorsToggle', 'vendorsMenu');
  })();

  /* -----------------------------------------------------------
     4) ROUTER POR HASH — Muestra secciones dentro de la misma página
     - Enlaces: <a class="menu-item" data-section="ID_SECCION" href="#ID_SECCION">
     - Secciones: <section id="ID_SECCION" class="section">
     ----------------------------------------------------------- */
  const routeFromHash = () => {
    const links    = $$('.menu-item[data-section]');
    const sections = $$('.section');
    const raw      = location.hash.replace('#', '');
    const id       = raw ? decodeURIComponent(raw) : 'Presentacion';
    const exists   = sections.some(s => s.id === id);
    const showId   = exists ? id : 'Presentacion';

    sections.forEach(s => s.classList.toggle('visible', s.id === showId));
    links.forEach(l => l.classList.toggle('active', l.dataset.section === showId));

    // En móvil, cierra la sidebar tras la navegación
    const sidebar   = $('#sidebar');
    const btnToggle = $('#btnSidebar');
    if (window.matchMedia('(max-width: 840px)').matches) {
      sidebar?.classList.remove('open');
      btnToggle?.setAttribute('aria-expanded', 'false');
    }

    // Scroll al inicio con preferencia de motion
    window.scrollTo({ top: 0, behavior: prefersReduced ? 'auto' : 'smooth' });
  };
  // Init + eventos
  routeFromHash();
  on(window, 'hashchange', routeFromHash);

  /* -----------------------------------------------------------
     5) USERBOX — Popover flotante (solo si existe en el DOM)
     - Estructura esperada:
       * Trigger:  #userboxTrigger
       * Template: #userboxDropdown (oculto, con el HTML interno)
     ----------------------------------------------------------- */
  (() => {
    const trigger  = $('#userboxTrigger');
    const template = $('#userboxDropdown');
    if (!trigger || !template) return; // si se eliminó del navbar, no hace nada

    // Crea popover en <body>
    const pop = document.createElement('div');
    pop.id = 'userboxPopover';
    pop.setAttribute('role','dialog');
    pop.setAttribute('aria-modal','false');
    pop.style.display = 'none';
    pop.innerHTML = template.innerHTML;
    document.body.appendChild(pop);

    const applyBaseStyles = (measuring = false) => {
      const s = pop.style;
      s.position = 'fixed'; s.zIndex = '999999'; s.inset = 'auto';
      s.background = 'var(--card, #121826)'; s.color = 'var(--text, #e9ecff)';
      s.borderRadius = '16px'; s.boxShadow = '0 20px 50px rgba(0,0,0,.25), 0 2px 8px rgba(0,0,0,.2)';
      s.overflow = 'hidden'; s.maxWidth = 'calc(100vw - 16px)';
      if (measuring) { s.display = 'block'; s.visibility = 'hidden'; }
    };

    const place = () => {
      const r = trigger.getBoundingClientRect();
      applyBaseStyles(true);

      const w = pop.offsetWidth || 320;
      const h = pop.offsetHeight || 180;

      let left = r.right - w;
      left = Math.max(8, Math.min(left, window.innerWidth - w - 8));

      let top = r.bottom + 10;
      let placement = 'bottom';
      if (top + h > window.innerHeight) { top = r.top - h - 10; placement = 'top'; }
      if (top < 8) top = 8;

      pop.dataset.placement = placement;
      pop.style.left = `${left}px`;
      pop.style.top  = `${top}px`;
      pop.style.visibility = 'visible';
    };

    const open = () => {
      pop.style.display = 'block';
      place();
      trigger.setAttribute('aria-expanded','true');
      document.addEventListener('click', onDocClick, { capture:true });
      window.addEventListener('resize', place);
      window.addEventListener('scroll', place, true);
    };
    const close = () => {
      pop.style.display = 'none';
      trigger.setAttribute('aria-expanded','false');
      document.removeEventListener('click', onDocClick, { capture:true });
      window.removeEventListener('resize', place);
      window.removeEventListener('scroll', place, true);
    };
    const toggle = () => (getComputedStyle(pop).display !== 'none') ? close() : open();
    const onDocClick = (e) => {
      if (e.target === trigger || trigger.contains(e.target)) return;
      if (pop.contains(e.target)) return;
      close();
    };

    on(trigger, 'click', (e) => { e.preventDefault(); e.stopPropagation(); toggle(); });
    on(document, 'keydown', (e) => { if (e.key === 'Escape') close(); });
  })();

  /* -----------------------------------------------------------
     6) CONTACTO — Validación nativa + feedback animado
     - Totalmente aislado; no afecta otras vistas
     ----------------------------------------------------------- */
  (() => {
    const form = $('#contactameForm');
    const msg  = $('#contactameMsg');
    if (!form || !msg) return;

    // Inyecta CSS del "shake" solo una vez
    let injected = false;
    const addShakeOnce = (el) => {
      if (!injected) {
        const st = document.createElement('style');
        st.textContent = `
          @keyframes ct-shake { 0%,100%{transform:translateX(0)} 25%{transform:translateX(-6px)} 75%{transform:translateX(6px)} }
          .ct-shake { animation: ct-shake .24s ease; }
        `;
        document.head.appendChild(st);
        injected = true;
      }
      el.classList.add('ct-shake');
      el.addEventListener('animationend', () => el.classList.remove('ct-shake'), { once:true });
    };

    const showMsg = (text, ok = true) => {
      msg.textContent = text;
      msg.style.color = ok ? 'var(--ct-success, #16a34a)' : 'var(--ct-error, #ef4444)';
      msg.style.opacity = '0';
      msg.style.transform = 'translateY(-4px)';
      requestAnimationFrame(() => {
        msg.style.transition = 'opacity .25s ease, transform .25s ease';
        msg.style.opacity = '1';
        msg.style.transform = 'none';
      });
    };

    on(form, 'submit', async (e) => {
      e.preventDefault();

      if (!form.checkValidity()) {
        const invalid = form.querySelector(':invalid');
        if (invalid) addShakeOnce(invalid.closest('.ct-field') || invalid);
        form.reportValidity();
        return;
      }

      // Aquí iría tu envío real:
      // const fd = new FormData(form);
      // const res = await fetch('contacto_enviar.php', { method:'POST', body: fd });
      // const json = await res.json();
      // if (!json.ok) { showMsg(json.error || 'Error al enviar', false); return; }

      // Demo local:
      showMsg('Mensaje enviado. ¡Gracias por escribir!', true);
      form.reset();
    });

    on(form, 'input', () => { msg.textContent = ''; });
  })();

})();

// Preview rápida del avatar en el modal (opcional)
(() => {
  const file = document.getElementById('avatar');
  if (!file) return;
  file.addEventListener('change', (e) => {
    const img = document.querySelector('#modalEditarPerfil img.preview');
    if (!img || !file.files?.[0]) return;
    const url = URL.createObjectURL(file.files[0]);
    img.src = url;
  });
})();

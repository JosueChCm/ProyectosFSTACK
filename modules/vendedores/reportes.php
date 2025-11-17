<?php
if (!defined('APP_INIT')) { http_response_code(403); exit; }
require_once __DIR__ . '/../../includes/config.php';
?>
<div class="tw-card" style="max-width:900px;margin-inline:auto">
  <h2 style="margin:4px 0 10px"><i class="fa-solid fa-chart-line"></i> Reportes</h2>
  <p class="muted" style="margin-top:-2px">Genera un reporte PDF con todos los vendedores.</p>

  <form id="repForm" class="grid" style="gap:14px; margin-top:12px">
    <label class="flex flex-col">
      <span class="text-sm text-secondary">Tu nombre <span style="color:#ef4444">*</span></span>
      <input class="ct-input" id="r_nombre" name="nombre" required maxlength="120">
    </label>
    <label class="flex flex-col">
      <span class="text-sm text-secondary">Motivo del reporte <span style="color:#ef4444">*</span></span>
      <input class="ct-input" id="r_motivo" name="motivo" required maxlength="160">
    </label>
    <label class="flex flex-col">
      <span class="text-sm text-secondary">Orden</span>
      <select class="ct-input" id="r_orden" name="orden">
        <option value="vendedor">Por vendedor</option>
        <option value="id">Por ID</option>
        <option value="fechaventa">Por fecha</option>
      </select>
    </label>
    <div style="display:flex; gap:.6rem; margin-top:6px; flex-wrap:wrap">
      <button class="btn-pro" type="submit"><i class="fa-solid fa-file-pdf"></i> Generar reporte</button>
      <a class="btn-pro btn-pro--ghost" href="./?m=vendedores&action=lista"><i class="fa-solid fa-users"></i> Ir a la lista</a>
    </div>
  </form>
</div>

<!-- Modal preview -->
<div class="xmodal-mask" id="repMask">
  <div class="xmodal" role="dialog" aria-modal="true" aria-labelledby="repTitle">
    <div class="xmodal-head">
      <h5 id="repTitle" style="margin:0"><i class="fa-regular fa-eye"></i> Vista previa del reporte</h5>
      <button class="btn" id="repClose" type="button"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="xmodal-body" style="height:min(70vh,700px);padding:0">
      <iframe id="repFrame" title="Vista previa PDF" style="width:100%;height:100%;border:0"></iframe>
    </div>
    <div class="xmodal-foot" style="justify-content:space-between">
      <div style="display:flex;gap:.5rem">
        <a id="repOpen" class="btn btn-secondary" target="_blank" rel="noopener"><i class="fa-solid fa-up-right-from-square"></i> Abrir aparte</a>
        <a id="repDown" class="btn btn-primary"  target="_blank" rel="noopener"><i class="fa-solid fa-download"></i> Descargar</a>
      </div>
      <button class="btn btn-secondary" id="repCancel" type="button">Cerrar</button>
    </div>
  </div>
</div>

<script>
const $=(s,ctx=document)=>ctx.querySelector(s);
const repForm=$('#repForm'), repMask=$('#repMask'), repClose=$('#repClose'), repCancel=$('#repCancel'), repFrame=$('#repFrame'), repOpen=$('#repOpen'), repDown=$('#repDown');
function openPreview(urlPdf){
  const iframeUrl = urlPdf + '#toolbar=0&navpanes=0&pagemode=none&view=FitH';
  repFrame.src = iframeUrl;            // ← previsualiza sin barras
  repOpen.href = urlPdf;               // abrir aparte = PDF directo
  repDown.href = urlPdf + (urlPdf.includes('?') ? '&' : '?') + 'dl=1';
  repMask.style.display = 'flex';
}

function closePreview(){ repMask.style.display='none'; repFrame.src='about:blank'; }
repClose.addEventListener('click', closePreview); repCancel.addEventListener('click', closePreview); repMask.addEventListener('click',e=>{ if(e.target===repMask) closePreview(); });
repForm.addEventListener('submit',(ev)=>{ ev.preventDefault(); const nombre=$('#r_nombre').value.trim(); const motivo=$('#r_motivo').value.trim(); const orden=$('#r_orden').value;
  if(!nombre){ alert('Ingresa tu nombre'); return; } if(!motivo){ alert('Ingresa el motivo'); return; }
  const params = new URLSearchParams({ nombre, motivo, orden });
  openPreview(`../modules/vendedores/report_pdf.php?${params.toString()}`);
});
</script>

<style>
.ct-input{ width:100%; background:transparent; color:var(--text); border:1px solid var(--input-line-muted); border-radius:10px; padding:12px 14px; outline:none; }
.ct-input:focus{ border-color:var(--primary); box-shadow:0 0 0 3px var(--ring); }
</style>

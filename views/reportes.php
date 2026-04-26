<!-- views/reportes.php -->

<div class="card mb-2">
  <div class="card-body" style="padding:1rem 1.4rem">
    <div style="display:flex;gap:.5rem;flex-wrap:wrap;align-items:flex-end">
      <!-- Tabs tipo -->
      <div style="display:flex;gap:.3rem;flex-wrap:wrap">
        <button class="btn rep-tab active" data-tipo="ventas">📈 Ventas</button>
        <button class="btn rep-tab" data-tipo="compras">📦 Compras</button>
        <button class="btn rep-tab" data-tipo="devoluciones">↩ Devoluciones</button>
        <button class="btn rep-tab" data-tipo="caja">💰 Caja</button>
      </div>

      <div class="divider" style="margin:0;border:none;border-left:1px solid var(--border);height:30px;align-self:center"></div>

      <!-- Filtro período -->
      <select id="rep-filtro" class="form-control" style="max-width:150px">
        <option value="diario">Hoy</option>
        <option value="semanal">Última semana</option>
        <option value="quincenal">Última quincena</option>
        <option value="mensual">Este mes</option>
        <option value="todo">Todo el tiempo</option>
        <option value="">Personalizado</option>
      </select>

      <!-- Rango personalizado -->
      <input type="date" id="rep-desde" class="form-control" style="max-width:140px" value="<?= date('Y-m-d') ?>">
      <input type="date" id="rep-hasta" class="form-control" style="max-width:140px" value="<?= date('Y-m-d') ?>">

      <!-- <button class="btn btn-primary" id="btn-generar-rep">🔍 Generar</button> -->
      <button class="btn" id="btn-exportar-excel" style="background:#10b981;color:#fff;border-color:#10b981">📥 Exportar Excel</button>
    </div>
  </div>
</div>

<div id="rep-content">
  <div class="text-center text-muted" style="padding:4rem">Selecciona un reporte para comenzar</div>
</div>

<style>
.rep-tab { background: var(--surface); border: 1.5px solid var(--border); color: var(--text-muted); }
.rep-tab:hover  { background: var(--bg); color: var(--text); }
.rep-tab.active { background: var(--brand); color: #fff; border-color: var(--brand); }
</style>
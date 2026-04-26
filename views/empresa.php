<!-- views/empresa.php -->

<div style="display:grid;grid-template-columns:1fr 340px;gap:1.2rem;align-items:start">

  <div class="card">
    <div class="card-header"><h3>🏢 Información de la Empresa</h3></div>
    <div class="card-body">
      <div class="form-row">
        <div class="form-group">
          <label for="emp-nombre">Nombre de la empresa *</label>
          <input type="text" id="emp-nombre" class="form-control" placeholder="Mi Farmacia">
        </div>
        <div class="form-group">
          <label for="emp-nit">NIT / RUC</label>
          <input type="text" id="emp-nit" class="form-control" placeholder="900.XXX.XXX-X">
        </div>
      </div>
      <div class="form-group">
        <label for="emp-direccion">Dirección</label>
        <input type="text" id="emp-direccion" class="form-control" placeholder="Calle 123 # 45-67">
      </div>
      <div class="form-row">
        <div class="form-group">
          <label for="emp-telefono">Teléfono</label>
          <input type="text" id="emp-telefono" class="form-control" placeholder="(+57) 300 000 0000">
        </div>
        <div class="form-group">
          <label for="emp-email">Email</label>
          <input type="email" id="emp-email" class="form-control" placeholder="contacto@farmacia.com">
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label for="emp-moneda">Moneda</label>
          <select id="emp-moneda" class="form-control">
            <option value="COP">COP — Peso Colombiano</option>
            <option value="USD">USD — Dólar</option>
            <option value="EUR">EUR — Euro</option>
            <option value="MXN">MXN — Peso Mexicano</option>
          </select>
        </div>
        <div class="form-group">
          <label for="emp-impuesto">Impuesto por defecto (%)</label>
          <input type="number" id="emp-impuesto" class="form-control" min="0" max="100" step="0.01" value="0">
        </div>
      </div>

      <button class="btn btn-primary mt-2" id="btn-guardar-empresa">💾 Guardar cambios</button>
    </div>
  </div>

  <!-- Preview -->
  <div>
    <div class="card">
      <div class="card-header"><h3>Vista previa</h3></div>
      <div class="card-body">
        <div id="emp-preview">
          <div class="text-center text-muted" style="padding:2rem">Cargando...</div>
        </div>
      </div>
    </div>

    <div class="card mt-2">
      <div class="card-header"><h3>ℹ️ Información del sistema</h3></div>
      <div class="card-body" style="font-size:.85rem;color:var(--text-muted)">
        <div style="display:flex;justify-content:space-between;padding:.3rem 0;border-bottom:1px solid var(--border)">
          <span>Sistema</span><span class="fw-700">FarmaSys v1.0</span>
        </div>
        <div style="display:flex;justify-content:space-between;padding:.3rem 0;border-bottom:1px solid var(--border)">
          <span>PHP</span><span class="fw-700"><?= phpversion() ?></span>
        </div>
        <div style="display:flex;justify-content:space-between;padding:.3rem 0">
          <span>Base de datos</span><span class="fw-700">MySQL (farmacia)</span>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- views/devoluciones.php -->

<!-- Buscar venta -->
<div class="card mb-2">
  <div class="card-header"><h3>↩ Procesar Devolución</h3></div>
  <div class="card-body">
    <div style="display:flex;gap:.5rem;align-items:flex-end;flex-wrap:wrap">
      <div class="form-group" style="flex:1;min-width:180px;margin:0">
        <label for="dev-venta-id">Número de venta</label>
        <input type="number" id="dev-venta-id" class="form-control" placeholder="Ej: 42" min="1">
      </div>
      <button class="btn btn-primary" id="btn-buscar-venta-dev">🔍 Buscar Venta</button>
    </div>
    <div id="dev-venta-info" style="margin-top:1rem"></div>
  </div>
</div>

<!-- Ítems a devolver -->
<div id="dev-items-wrap" style="display:none">
  <div class="card mb-2">
    <div class="card-header"><h3>Selecciona los productos a devolver</h3></div>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Producto</th>
            <th class="text-center">Vendido</th>
            <th class="text-center">Ya devuelto</th>
            <th class="text-center">Disponible</th>
            <th class="text-center">A devolver</th>
          </tr>
        </thead>
        <tbody id="dev-items-table">
          <tr><td colspan="5" class="text-center text-muted" style="padding:2rem">Buscando venta...</td></tr>
        </tbody>
      </table>
    </div>
    <div class="card-body">
      <div class="form-group">
        <label for="dev-motivo">Motivo de la devolución</label>
        <input type="text" id="dev-motivo" class="form-control" placeholder="Ej: Producto en mal estado, error de venta...">
      </div>
      <div style="display:flex;justify-content:space-between;align-items:center;margin-top:1rem">
        <div>
          <span class="text-muted" style="font-size:.88rem">Total a devolver:</span>
          <span class="fw-700 text-danger" style="font-size:1.2rem;margin-left:.5rem" id="dev-total">$ 0</span>
        </div>
        <div style="display:flex;gap:.5rem">
          <button class="btn btn-ghost" id="btn-cancelar-dev">Cancelar</button>
          <button class="btn btn-danger" id="btn-confirmar-devolucion" disabled>↩ Confirmar Devolución</button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Historial de devoluciones -->
<div class="card">
  <div class="card-header">
    <h3>📋 Historial de Devoluciones</h3>
    <div style="display:flex;gap:.5rem;align-items:center">
      <input type="date" id="dev-hist-desde" class="form-control" style="max-width:140px" value="<?= date('Y-m-d') ?>">
      <input type="date" id="dev-hist-hasta" class="form-control" style="max-width:140px" value="<?= date('Y-m-d') ?>">
      <button class="btn btn-ghost btn-sm" id="btn-buscar-hist-dev">Buscar</button>
    </div>
  </div>
  <div class="table-wrap" id="tabla-historial-dev">
    <table>
      <thead>
        <tr>
          <th>#Dev</th><th>#Venta</th><th>Fecha</th><th>Cajero</th><th>Motivo</th>
          <th class="text-right">Total devuelto</th>
        </tr>
      </thead>
      <tbody><tr><td colspan="6" class="text-center text-muted" style="padding:2rem">Cargando...</td></tr></tbody>
    </table>
  </div>
</div>
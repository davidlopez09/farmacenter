<!-- views/productos.php -->

<div class="card">
  <div class="card-header">
    <div>
      <h3>📦 Gestión de Productos</h3>
      <span id="prod-count" class="text-muted" style="font-size:.8rem"></span>
    </div>
    <button class="btn btn-primary" id="btn-nuevo-producto">+ Nuevo Producto</button>
  </div>
  <div class="card-body" style="padding-bottom:.5rem">
    <div style="display:flex;gap:.5rem;flex-wrap:wrap;align-items:flex-end">
      <input type="text" id="prod-search" class="form-control" placeholder="Buscar por nombre o código..." style="max-width:280px">
      <select id="filter-categoria" class="form-control" style="max-width:200px">
        <option value="">Todas las categorías</option>
      </select>
      <button class="btn btn-ghost" id="btn-buscar-producto">🔍 Buscar</button>
    </div>
  </div>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Código</th><th>Nombre</th><th>Categoría</th>
          <th>P. Compra</th><th>P. Venta</th><th>Stock</th>
          <th>Stock mín.</th><th>Acciones</th>
        </tr>
      </thead>
      <tbody id="tbody-productos">
        <tr><td colspan="8" class="text-center text-muted" style="padding:2rem">Cargando...</td></tr>
      </tbody>
    </table>
  </div>
  <div id="prod-pagination" class="pagination"></div>
</div>

<!-- Leyenda -->
<div style="display:flex;gap:1rem;margin-top:.8rem;font-size:.78rem;color:var(--text-muted)">
  <span>🟡 Stock bajo (≤ mínimo)</span>
  <span>🔴 Sin stock</span>
</div>

<!-- ─ Modal Producto ────────────────────────────────────────────────── -->
<div id="modal-producto" class="modal-overlay">
  <div class="modal modal-lg">
    <div class="modal-header">
      <h3 id="modal-prod-title">Nuevo Producto</h3>
      <button class="modal-close" onclick="Modal.close('modal-producto')">✕</button>
    </div>
    <div class="modal-body">
      <form id="form-producto" onsubmit="return false">
        <div class="form-row">
          <div class="form-group">
            <label for="prod-nombre">Nombre *</label>
            <input type="text" id="prod-nombre" class="form-control" placeholder="Nombre del producto">
          </div>
          <div class="form-group">
            <label for="prod-codigo">Código de barras</label>
            <input type="text" id="prod-codigo" class="form-control" placeholder="Opcional">
          </div>
        </div>
        <div class="form-group">
          <label for="prod-descripcion">Descripción</label>
          <textarea id="prod-descripcion" class="form-control" rows="2" placeholder="Descripción opcional"></textarea>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label for="prod-categoria">Categoría</label>
            <select id="prod-categoria" class="form-control">
              <option value="">Sin categoría</option>
            </select>
          </div>
          <div class="form-group">
            <label for="prod-precio-compra">Precio de compra *</label>
            <input type="number" id="prod-precio-compra" class="form-control" min="0" step="0.01" placeholder="0.00">
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label for="prod-precio-venta">Precio de venta *</label>
            <input type="number" id="prod-precio-venta" class="form-control" min="0" step="0.01" placeholder="0.00">
          </div>
          <div class="form-group">
            <label for="prod-stock">Stock inicial</label>
            <input type="number" id="prod-stock" class="form-control" min="0" value="0">
          </div>
        </div>
        <div class="form-group">
          <label for="prod-stock-min">Stock mínimo (alerta)</label>
          <input type="number" id="prod-stock-min" class="form-control" min="0" value="5">
        </div>
      </form>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" onclick="Modal.close('modal-producto')">Cancelar</button>
      <button class="btn btn-primary" id="btn-guardar-producto">💾 Guardar</button>
    </div>
  </div>
</div>
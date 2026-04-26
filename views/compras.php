<!-- views/compras.php -->

<div class="pos-layout">

  <!-- ─ Formulario de compra ───────────────────────────────────────── -->
  <div>
    <div class="card mb-2">
      <div class="card-header"><h3>📥 Registrar Compra de Inventario</h3></div>
      <div class="card-body">
        <div id="compra-search-wrap" style="position:relative;margin-bottom:.5rem">
          <input type="text" id="compra-search" class="form-control" placeholder="Buscar producto por nombre...">
          <div id="compra-search-result" class="product-result" style="display:none;position:absolute;top:100%;left:0;right:0;z-index:200;background:#fff;border-radius:var(--radius);box-shadow:var(--shadow-md);max-height:260px;overflow-y:auto;margin-top:2px"></div>
        </div>
        <div style="font-size:.78rem;color:var(--text-muted)">💡 Busca el producto y ajusta el precio y cantidad de compra.</div>
      </div>
    </div>

    <div class="card mb-2">
      <div class="card-header"><h3>Productos a comprar</h3></div>
      <div class="table-wrap">
        <table>
          <thead><tr>
            <th>Producto</th><th>Precio compra</th><th>Cantidad</th><th>Subtotal</th><th></th>
          </tr></thead>
          <tbody id="compra-items">
            <tr><td colspan="5" class="text-center text-muted" style="padding:2rem">Agrega productos usando el buscador</td></tr>
          </tbody>
        </table>
      </div>
      <div class="card-body" style="display:flex;justify-content:space-between;align-items:center">
        <div>
          <span class="text-muted">Total de compra:</span>
          <span class="fw-700" style="font-size:1.2rem;margin-left:.5rem" id="compra-total">$ 0</span>
        </div>
        <button class="btn btn-primary" id="btn-registrar-compra" disabled>📥 Registrar Compra</button>
      </div>
    </div>
  </div>

  <!-- ─ Historial de compras ──────────────────────────────────────── -->
  <div>
    <div class="card">
      <div class="card-header">
        <h3>📋 Historial</h3>
      </div>
      <div class="card-body" style="padding-bottom:.5rem">
        <div style="display:flex;gap:.4rem;flex-wrap:wrap">
          <input type="date" id="compra-hist-desde" class="form-control" style="max-width:140px" value="<?= date('Y-m-d') ?>">
          <input type="date" id="compra-hist-hasta" class="form-control" style="max-width:140px" value="<?= date('Y-m-d') ?>">
          <button class="btn btn-ghost btn-sm" id="btn-buscar-hist-compra">Buscar</button>
        </div>
      </div>
      <div class="table-wrap" id="tabla-historial-compras">
        <table>
          <thead><tr><th>#</th><th>Fecha</th><th>Usuario</th><th>Total</th></tr></thead>
          <tbody><tr><td colspan="4" class="text-center text-muted" style="padding:2rem">Cargando...</td></tr></tbody>
        </table>
      </div>
    </div>
  </div>
</div>
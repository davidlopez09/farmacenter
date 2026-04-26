<!-- views/ventas.php -->
<div class="pos-layout">

  <!-- ─ Panel izquierdo: búsqueda ─────────────────────────────────── -->
  <div>
    <div class="card mb-2">
      <div class="card-header"><h3>🔍 Buscar producto</h3></div>
      <div class="card-body">
        <div style="display:flex;gap:.5rem;margin-bottom:.8rem">
          <select id="venta-search-tipo" class="form-control" style="max-width:160px">
            <option value="nombre">Por nombre</option>
            <option value="codigo">Por código de barras</option>
          </select>
          <div id="venta-search-wrap" style="position:relative;flex:1">
            <input type="text" id="venta-search" class="form-control" placeholder="Escribe el nombre o escanea el código...">
            <div id="venta-search-result" class="product-result" style="display:none;position:absolute;top:100%;left:0;right:0;z-index:200;background:#fff;border-radius:var(--radius);box-shadow:var(--shadow-md);max-height:280px;overflow-y:auto;margin-top:2px"></div>
          </div>
        </div>
        <div style="font-size:.78rem;color:var(--text-muted)">
          💡 Usa un lector de código de barras o escribe el nombre. Presiona Enter para buscar.
        </div>
      </div>
    </div>

    <!-- Historial de ventas -->
    <div class="card">
      <div class="card-header">
        <h3>📋 Historial de ventas</h3>
        <div style="display:flex;gap:.5rem;align-items:center">
          <input type="date" id="hist-desde" class="form-control" style="max-width:140px" value="<?= date('Y-m-d') ?>">
          <input type="date" id="hist-hasta" class="form-control" style="max-width:140px" value="<?= date('Y-m-d') ?>">
          <button class="btn btn-ghost btn-sm" id="btn-buscar-historial">Buscar</button>
        </div>
      </div>
      <div class="table-wrap" id="tabla-historial-ventas">
        <table>
          <thead>
            <tr>
              <th>#</th><th>Fecha</th><th>Cajero</th><th>Método</th>
              <th class="text-right">Total</th><th>Acción</th>
            </tr>
          </thead>
          <tbody><tr><td colspan="6" class="text-center text-muted" style="padding:2rem">Cargando...</td></tr></tbody>
        </table>
      </div>
      <div id="hist-pagination" class="pagination"></div>
    </div>
  </div>

  <!-- ─ Panel derecho: carrito ────────────────────────────────────── -->
  <div>
    <div class="cart-wrap">
      <div class="cart-header">
        <h3>🛒 Carrito <span id="cart-badge" class="badge badge-green" style="display:none"></span></h3>
      </div>
      <div class="cart-items" id="cart-items">
        <div class="cart-empty">
          <p>Carrito vacío</p>
          <p>Busca un producto para agregar</p>
        </div>
      </div>
      <div class="cart-footer">
        <div class="cart-total-row">
          <span>Subtotal</span>
          <span id="cart-subtotal">$ 0</span>
        </div>
        <div class="cart-total-row total">
          <span>TOTAL</span>
          <span id="cart-total">$ 0</span>
        </div>
        <hr class="divider">
        <div class="form-group mb-2">
          <label for="metodo-pago">Método de pago</label>
          <select id="metodo-pago" class="form-control">
            <option value="EFECTIVO">💵 Efectivo</option>
            <option value="TARJETA">💳 Tarjeta</option>
            <option value="TRANSFERENCIA">📱 Transferencia</option>
            <option value="NEQUI">🟣 Nequi</option>
            <option value="DAVIPLATA">🔴 Daviplata</option>
          </select>
        </div>
        <button class="btn btn-primary btn-full" id="btn-confirmar-venta" disabled>
          ✓ Confirmar Venta
        </button>
        <button class="btn btn-ghost btn-full mt-1" id="btn-limpiar-cart" style="font-size:.8rem">
          🗑 Limpiar carrito
        </button>
      </div>
    </div>
  </div>
</div>

<!-- ─ Modal Ticket ────────────────────────────────────────────────── -->
<div id="modal-ticket" class="modal-overlay">
  <div class="modal">
    <div class="modal-header">
      <h3>🧾 Recibo de Venta</h3>
      <button class="modal-close" data-close-modal="modal-ticket">✕</button>
    </div>
    <div class="modal-body" id="ticket-content"></div>
    <div class="modal-footer">
      <button class="btn btn-ghost" data-close-modal="modal-ticket">Cerrar</button>
      <button class="btn btn-primary" onclick="window.print()">🖨 Imprimir</button>
    </div>
  </div>
</div>
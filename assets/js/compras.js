// assets/js/compras.js
// ── Módulo de Compras ──────────────────────────────────────────────────────

const Compras = (() => {
  let items         = []; // [{producto_id, nombre, precio_compra, cantidad}]
  let searchTimeout = null;

  // ── Búsqueda de producto ──────────────────────────────────────────────────
  function bindSearch() {
    const input  = document.getElementById('compra-search');
    const result = document.getElementById('compra-search-result');
    if (!input) return;

    input.addEventListener('input', () => {
      clearTimeout(searchTimeout);
      const q = input.value.trim();
      if (!q) { result.innerHTML = ''; result.style.display = 'none'; return; }
      searchTimeout = setTimeout(() => buscarProducto(q, result), 320);
    });

    input.addEventListener('keydown', e => {
      if (e.key === 'Enter') {
        clearTimeout(searchTimeout);
        buscarProducto(input.value.trim(), result);
      }
    });

    document.addEventListener('click', e => {
      if (!e.target.closest('#compra-search-wrap')) result.style.display = 'none';
    });
  }

  async function buscarProducto(q, result) {
    result.innerHTML = '<div style="padding:.7rem 1rem;color:var(--text-muted);font-size:.85rem">Buscando...</div>';
    result.style.display = '';

    const res = await API.get(`api/productos.php?action=buscar&q=${encodeURIComponent(q)}&tipo=nombre`);
    if (!res.success || !res.data.length) {
      result.innerHTML = '<div style="padding:.7rem 1rem;color:var(--text-muted);font-size:.85rem">Sin resultados</div>';
      return;
    }

    result.innerHTML = res.data.map(p => `
      <div class="product-result-item" onclick="Compras.agregarItem(${JSON.stringify(p).replace(/"/g, '&quot;')})">
        <div>
          <div class="product-name">${p.nombre}</div>
          <div class="product-meta">Stock actual: <strong>${p.stock}</strong></div>
        </div>
        <div class="fw-700">${Fmt.money(p.precio_compra)}</div>
      </div>`).join('');
  }

  // ── Gestión de ítems ──────────────────────────────────────────────────────
  function agregarItem(producto) {
    const idx = items.findIndex(i => i.producto_id === producto.id);
    if (idx >= 0) {
      items[idx].cantidad++;
    } else {
      items.push({
        producto_id:  producto.id,
        nombre:       producto.nombre,
        precio_compra: parseFloat(producto.precio_compra),
        cantidad:     1,
      });
    }

    const input  = document.getElementById('compra-search');
    const result = document.getElementById('compra-search-result');
    if (input)  input.value = '';
    if (result) result.style.display = 'none';

    renderItems();
  }

  function renderItems() {
    const el = document.getElementById('compra-items');
    if (!el) return;

    if (!items.length) {
      el.innerHTML = '<tr><td colspan="5" class="text-center text-muted" style="padding:2rem">Agrega productos usando el buscador</td></tr>';
      actualizarTotal();
      return;
    }

    el.innerHTML = items.map((item, i) => `
      <tr>
        <td class="fw-700">${item.nombre}</td>
        <td>
          <input type="number" class="form-control" style="width:90px;text-align:center"
            value="${item.precio_compra}" min="0" step="0.01"
            onchange="Compras.updatePrecio(${i}, this.value)">
        </td>
        <td>
          <div style="display:flex;align-items:center;gap:.3rem">
            <button class="qty-btn" onclick="Compras.cambiarCant(${i}, -1)">−</button>
            <span class="qty-display">${item.cantidad}</span>
            <button class="qty-btn" onclick="Compras.cambiarCant(${i}, 1)">+</button>
          </div>
        </td>
        <td class="fw-700">${Fmt.money(item.precio_compra * item.cantidad)}</td>
        <td><button class="btn btn-danger btn-sm" onclick="Compras.quitarItem(${i})">✕</button></td>
      </tr>`).join('');

    actualizarTotal();
  }

  function cambiarCant(idx, delta) {
    items[idx].cantidad = Math.max(1, items[idx].cantidad + delta);
    renderItems();
  }

  function updatePrecio(idx, val) {
    items[idx].precio_compra = parseFloat(val) || 0;
    renderItems();
  }

  function quitarItem(idx) {
    items.splice(idx, 1);
    renderItems();
  }

  function actualizarTotal() {
    const total = items.reduce((s, i) => s + i.precio_compra * i.cantidad, 0);
    const el = document.getElementById('compra-total');
    if (el) el.textContent = Fmt.money(total);
    const btn = document.getElementById('btn-registrar-compra');
    if (btn) btn.disabled = items.length === 0;
  }

  // ── Registrar compra ──────────────────────────────────────────────────────
  async function registrar() {
    if (!items.length) { Toast.error('Agrega al menos un producto.'); return; }

    const hasInvalid = items.some(i => i.precio_compra <= 0 || i.cantidad <= 0);
    if (hasInvalid) { Toast.error('Verifica los precios y cantidades.'); return; }

    const btn = document.getElementById('btn-registrar-compra');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner"></span> Registrando...';

    const res = await API.post('api/compras.php', {
      items: items.map(({ producto_id, cantidad, precio_compra }) => ({ producto_id, cantidad, precio_compra })),
    });

    btn.disabled = false;
    btn.innerHTML = '📥 Registrar Compra';

    if (res.success) {
      Toast.success(`Compra #${res.data.compra_id} registrada. Inventario actualizado.`);
      items = [];
      renderItems();
      loadHistorial();
    } else {
      Toast.error(res.message || 'Error al registrar la compra.');
    }
  }

  // ── Historial de compras ──────────────────────────────────────────────────
  async function loadHistorial() {
    const desde = document.getElementById('compra-hist-desde')?.value || new Date().toISOString().slice(0,10);
    const hasta = document.getElementById('compra-hist-hasta')?.value || desde;
    const el    = document.getElementById('tabla-historial-compras');
    if (!el) return;

    const res = await API.get(`api/compras.php?desde=${desde}&hasta=${hasta}`);
    if (!res.success) return;

    const rows = (res.data.compras || []).map(c => `
      <tr>
        <td class="mono">#${c.id}</td>
        <td>${Fmt.datetime(c.fecha)}</td>
        <td>${c.cajero}</td>
        <td class="fw-700 text-danger">${Fmt.money(c.total)}</td>
      </tr>`).join('') || '<tr><td colspan="4" class="text-center text-muted" style="padding:2rem">Sin compras en el período</td></tr>';

    el.querySelector('tbody').innerHTML = rows;
  }

  // ── Init ──────────────────────────────────────────────────────────────────
  function init() {
    bindSearch();
    renderItems();
    loadHistorial();

    document.getElementById('btn-registrar-compra')?.addEventListener('click', registrar);
    document.getElementById('btn-buscar-hist-compra')?.addEventListener('click', loadHistorial);
  }

  return { init, agregarItem, cambiarCant, updatePrecio, quitarItem };
})();

if (!window.viewHandlers) window.viewHandlers = {};
window.viewHandlers.compras = () => Compras.init();
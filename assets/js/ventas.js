// assets/js/ventas.js
// ── Módulo de Ventas (Punto de Venta) ──────────────────────────────────────

const Ventas = (() => {
    let cart = [];   // [{producto_id, nombre, precio_unitario, cantidad, stock}]
    let searchTimeout = null;

    // ── Render carrito ────────────────────────────────────────────────────────
    function renderCart() {
        const el = document.getElementById('cart-items');
        const badge = document.getElementById('cart-badge');
        if (!el) return;

        if (cart.length === 0) {
            el.innerHTML = `<div class="cart-empty">
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
        <p style="margin-top:.5rem">Carrito vacío</p>
        <p>Busca un producto para agregar</p>
      </div>`;
            updateTotals();
            if (badge) badge.style.display = 'none';
            return;
        }

        if (badge) { badge.textContent = cart.length; badge.style.display = ''; }

        el.innerHTML = cart.map((item, i) => `
      <div class="cart-item">
        <div>
          <div class="cart-item-name">${item.nombre}</div>
          <div class="cart-item-price">${Fmt.money(item.precio_unitario)} c/u · Stock: ${item.stock}</div>
        </div>
        <div style="text-align:right">
          <div class="cart-item-controls">
            <button class="qty-btn" onclick="Ventas.changeQty(${i}, -1)">−</button>
            <span class="qty-display">${item.cantidad}</span>
            <button class="qty-btn" onclick="Ventas.changeQty(${i}, 1)">+</button>
            <button class="qty-btn" style="color:var(--danger)" onclick="Ventas.removeItem(${i})">✕</button>
          </div>
          <div style="font-weight:700;font-size:.88rem;margin-top:.25rem">${Fmt.money(item.precio_unitario * item.cantidad)}</div>
        </div>
      </div>`).join('');

        updateTotals();
    }

    function updateTotals() {
        const subtotal = cart.reduce((s, i) => s + i.precio_unitario * i.cantidad, 0);
        const el = id => { const e = document.getElementById(id); if (e) e.textContent = Fmt.money(el); };
        const set = (id, val) => { const e = document.getElementById(id); if (e) e.textContent = val; };
        set('cart-subtotal', Fmt.money(subtotal));
        set('cart-total', Fmt.money(subtotal));
        const btn = document.getElementById('btn-confirmar-venta');
        if (btn) btn.disabled = cart.length === 0;
    }

    // ── Búsqueda de producto ──────────────────────────────────────────────────
    function bindSearch() {
        const input = document.getElementById('venta-search');
        const tipo = document.getElementById('venta-search-tipo');
        const result = document.getElementById('venta-search-result');
        if (!input) return;

        input.addEventListener('input', () => {
            clearTimeout(searchTimeout);
            const q = input.value.trim();
            if (!q) { result.innerHTML = ''; result.style.display = 'none'; return; }
            searchTimeout = setTimeout(() => doSearch(q, tipo.value, result), 320);
        });

        // Barcode: Enter dispara búsqueda inmediata por código
        input.addEventListener('keydown', e => {
            if (e.key === 'Enter') {
                clearTimeout(searchTimeout);
                doSearch(input.value.trim(), tipo.value, result);
            }
        });

        document.addEventListener('click', e => {
            if (!e.target.closest('#venta-search-wrap')) {
                result.style.display = 'none';
            }
        });
    }

    async function doSearch(q, tipo, result) {
        if (!q) return;
        result.innerHTML = '<div style="padding:.7rem 1rem;color:var(--text-muted);font-size:.85rem">Buscando...</div>';
        result.style.display = '';

        const res = await API.get(`api/productos.php?action=buscar&q=${encodeURIComponent(q)}&tipo=${tipo}`);
        if (!res.success || !res.data.length) {
            result.innerHTML = '<div style="padding:.7rem 1rem;color:var(--text-muted);font-size:.85rem">Sin resultados</div>';
            return;
        }

        result.innerHTML = res.data.map(p => `
      <div class="product-result-item" onclick="Ventas.addToCart(${JSON.stringify(p).replace(/"/g, '&quot;')})">
        <div>
          <div class="product-name">${p.nombre}</div>
          <div class="product-meta">${p.categoria || 'Sin categoría'} · Stock: <strong>${p.stock}</strong></div>
        </div>
        <div style="text-align:right">
          <div class="fw-700">${Fmt.money(p.precio_venta)}</div>
          ${p.stock === 0 ? '<span class="badge badge-red">Sin stock</span>' : ''}
        </div>
      </div>`).join('');
    }

    // ── Carrito actions ───────────────────────────────────────────────────────
    function addToCart(producto) {
        if (producto.stock <= 0) { Toast.error('Producto sin stock disponible.'); return; }

        const idx = cart.findIndex(i => i.producto_id === producto.id);
        if (idx >= 0) {
            if (cart[idx].cantidad >= cart[idx].stock) {
                Toast.error(`Stock máximo disponible: ${cart[idx].stock}`); return;
            }
            cart[idx].cantidad++;
        } else {
            cart.push({
                producto_id: producto.id,
                nombre: producto.nombre,
                precio_unitario: parseFloat(producto.precio_venta),
                cantidad: 1,
                stock: parseInt(producto.stock),
            });
        }

        // Limpiar búsqueda
        const input = document.getElementById('venta-search');
        const result = document.getElementById('venta-search-result');
        if (input) input.value = '';
        if (result) result.style.display = 'none';
        input?.focus();

        renderCart();
        Toast.success(`${producto.nombre} agregado al carrito.`, 1800);
    }

    function changeQty(idx, delta) {
        cart[idx].cantidad += delta;
        if (cart[idx].cantidad <= 0) { removeItem(idx); return; }
        if (cart[idx].cantidad > cart[idx].stock) { cart[idx].cantidad = cart[idx].stock; Toast.error('Stock máximo alcanzado.'); }
        renderCart();
    }

    function removeItem(idx) {
        cart.splice(idx, 1);
        renderCart();
    }

    function clearCart() { cart = []; renderCart(); }

    // ── Confirmar venta ───────────────────────────────────────────────────────
    async function confirmarVenta() {
        if (cart.length === 0) return;
        const metodo = document.getElementById('metodo-pago')?.value || 'EFECTIVO';

        const btn = document.getElementById('btn-confirmar-venta');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner"></span> Procesando...';

        const res = await API.post('api/ventas.php', {
            items: cart.map(({ producto_id, cantidad, precio_unitario }) => ({ producto_id, cantidad, precio_unitario })),
            metodo_pago: metodo,
        });

        btn.disabled = false;
        btn.innerHTML = '✓ Confirmar Venta';

        if (res.success) {
            Toast.success(`Venta #${res.data.venta_id} registrada — ${Fmt.money(res.data.total)}`);
            clearCart();
            loadHistorial();
            mostrarTicket(res.data.venta_id);
        } else {
            Toast.error(res.message || 'Error al registrar la venta.');
        }
    }

    // ── Mini ticket (recibo) ──────────────────────────────────────────────────
    async function mostrarTicket(ventaId) {
        const res = await API.get(`api/ventas.php?action=detalle&id=${ventaId}`);
        if (!res.success) return;
        const v = res.data;

        const rows = (v.detalle || []).map(d => `
      <tr>
        <td>${d.producto}</td>
        <td style="text-align:center">${d.cantidad}</td>
        <td style="text-align:right">${Fmt.money(d.precio_unitario)}</td>
        <td style="text-align:right">${Fmt.money(d.subtotal)}</td>
      </tr>`).join('');

        document.getElementById('ticket-content').innerHTML = `
      <div style="text-align:center;margin-bottom:1rem">
        <div style="font-size:1.2rem;font-weight:700">RECIBO DE VENTA</div>
        <div style="color:var(--text-muted);font-size:.82rem">Venta #${v.id} · ${Fmt.datetime(v.fecha)}</div>
        <div style="font-size:.82rem">Cajero: ${v.cajero}</div>
      </div>
      <table style="width:100%;font-size:.85rem;border-collapse:collapse">
        <thead><tr style="border-bottom:1px solid var(--border)">
          <th style="text-align:left;padding:.4rem 0">Producto</th>
          <th style="text-align:center">Cant.</th>
          <th style="text-align:right">P.Unit</th>
          <th style="text-align:right">Subtotal</th>
        </tr></thead>
        <tbody>${rows}</tbody>
      </table>
      <div class="divider"></div>
      <div style="display:flex;justify-content:space-between;font-weight:700;font-size:1.05rem">
        <span>TOTAL</span><span>${Fmt.money(v.total)}</span>
      </div>
      <div style="text-align:center;margin-top:.8rem;font-size:.78rem;color:var(--text-muted)">
        Método de pago: ${v.metodo_pago}
      </div>`;

        Modal.open('modal-ticket');
    }

    // ── Historial de ventas ───────────────────────────────────────────────────
    let historialPagina = 1;

    async function loadHistorial() {
        const hoyLocal = new Date().toLocaleDateString('en-CA');
        const desde = document.getElementById('hist-desde')?.value || hoyLocal;
        const hasta = document.getElementById('hist-hasta')?.value || hoyLocal;
        const res = await API.get(`api/ventas.php?action=listar&desde=${desde}&hasta=${hasta}&pagina=${historialPagina}`);
        const el = document.getElementById('tabla-historial-ventas');
        if (!res.success || !el) return;

        const rows = (res.data.ventas || []).map(v => `
      <tr>
        <td class="mono">#${v.id}</td>
        <td>${Fmt.datetime(v.fecha)}</td>
        <td>${v.cajero}</td>
        <td><span class="badge badge-blue">${v.metodo_pago}</span></td>
        <td class="text-right fw-700">${Fmt.money(v.total)}</td>
        <td>
          <button class="btn btn-ghost btn-sm" onclick="Ventas.verDetalle(${v.id})">Ver detalle</button>
        </td>
      </tr>`).join('') || `<tr><td colspan="6" class="text-center text-muted" style="padding:2rem">Sin ventas en el período</td></tr>`;

        el.querySelector('tbody').innerHTML = rows;
        renderPagination('hist-pagination', historialPagina, res.data.paginas, p => { historialPagina = p; loadHistorial(); });
    }

    async function verDetalle(id) {
        const res = await API.get(`api/ventas.php?action=detalle&id=${id}`);
        if (!res.success) { Toast.error(res.message); return; }
        mostrarTicket(id);
    }

    // ── Init ──────────────────────────────────────────────────────────────────
    function init() {
        renderCart();
        bindSearch();

        document.getElementById('btn-confirmar-venta')?.addEventListener('click', confirmarVenta);
        document.getElementById('btn-limpiar-cart')?.addEventListener('click', () => {
            if (cart.length && confirm('¿Limpiar el carrito?')) clearCart();
        });

        // Historial
        ['hist-desde', 'hist-hasta'].forEach(id => {
            document.getElementById(id)?.addEventListener('change', () => { historialPagina = 1; loadHistorial(); });
        });
        loadHistorial();

        // Cerrar modales
        document.querySelectorAll('[data-close-modal]').forEach(btn => {
            btn.addEventListener('click', () => Modal.close(btn.dataset.closeModal));
        });
    }

    return { init, addToCart, changeQty, removeItem, clearCart, verDetalle };
})();

// Registrar en viewHandlers
if (!window.viewHandlers) window.viewHandlers = {};
window.viewHandlers.ventas = () => Ventas.init();
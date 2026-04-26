// assets/js/devoluciones.js
// ── Módulo de Devoluciones ─────────────────────────────────────────────────

const Devoluciones = (() => {
    let ventaActual = null;
    let itemsDevolver = []; // [{producto_id, nombre, cantidad, precio_unitario, disponible, aDevolver}]

    // ── Buscar venta ──────────────────────────────────────────────────────────
    async function buscarVenta() {
        const ventaId = parseInt(document.getElementById('dev-venta-id')?.value);
        const infoEl = document.getElementById('dev-venta-info');
        const itemsEl = document.getElementById('dev-items-wrap');

        if (!ventaId || ventaId <= 0) { Toast.error('Ingresa un número de venta válido.'); return; }

        infoEl.innerHTML = '<div class="text-muted" style="padding:.5rem 0">Buscando...</div>';
        itemsEl.style.display = 'none';

        const res = await API.get(`api/devoluciones.php?action=venta_detalle&venta_id=${ventaId}`);
        if (!res.success) { Toast.error(res.message); infoEl.innerHTML = ''; return; }

        ventaActual = res.data.venta;
        itemsDevolver = res.data.detalle
            .filter(d => d.cantidad_disponible > 0)
            .map(d => ({ ...d, aDevolver: 0 }));

        infoEl.innerHTML = `
      <div class="alert alert-success" style="margin:0">
        <strong>Venta #${ventaActual.id}</strong> —
        Fecha: ${Fmt.datetime(ventaActual.fecha)} —
        Total original: <strong>${Fmt.money(ventaActual.total)}</strong>
      </div>`;

        renderItems();
        itemsEl.style.display = '';
    }

    // ── Render tabla de ítems a devolver ──────────────────────────────────────
    function renderItems() {
        const el = document.getElementById('dev-items-table');
        if (!el) return;

        if (itemsDevolver.length === 0) {
            el.innerHTML = '<tr><td colspan="5" class="text-center text-muted" style="padding:1.5rem">Todos los ítems ya fueron devueltos.</td></tr>';
            return;
        }

        el.innerHTML = itemsDevolver.map((item, i) => `
      <tr>
        <td class="fw-700">${item.producto}</td>
        <td class="text-center">${item.cantidad_vendida}</td>
        <td class="text-center">${item.cantidad_devuelta}</td>
        <td class="text-center">
          <span class="badge ${item.cantidad_disponible > 0 ? 'badge-green' : 'badge-red'}">${item.cantidad_disponible}</span>
        </td>
        <td>
          <div style="display:flex;align-items:center;gap:.4rem;justify-content:center">
            <button class="qty-btn" onclick="Devoluciones.cambiarCant(${i}, -1)">−</button>
            <span class="qty-display" id="dev-qty-${i}">${item.aDevolver}</span>
            <button class="qty-btn" onclick="Devoluciones.cambiarCant(${i}, 1)">+</button>
          </div>
        </td>
      </tr>`).join('');

        actualizarTotal();
    }

    function cambiarCant(idx, delta) {
        itemsDevolver[idx].aDevolver += delta;
        if (itemsDevolver[idx].aDevolver < 0) itemsDevolver[idx].aDevolver = 0;
        if (itemsDevolver[idx].aDevolver > itemsDevolver[idx].cantidad_disponible) {
            itemsDevolver[idx].aDevolver = itemsDevolver[idx].cantidad_disponible;
            Toast.error('No puedes devolver más de lo disponible.');
        }
        const el = document.getElementById(`dev-qty-${idx}`);
        if (el) el.textContent = itemsDevolver[idx].aDevolver;
        actualizarTotal();
    }

    function actualizarTotal() {
        const total = itemsDevolver.reduce((s, i) => s + i.precio_unitario * i.aDevolver, 0);
        const el = document.getElementById('dev-total');
        if (el) el.textContent = Fmt.money(total);
        const btn = document.getElementById('btn-confirmar-devolucion');
        if (btn) btn.disabled = total <= 0;
    }

    // ── Confirmar devolución ──────────────────────────────────────────────────
    async function confirmarDevolucion() {
        const motivo = document.getElementById('dev-motivo')?.value?.trim() || '';
        const items = itemsDevolver
            .filter(i => i.aDevolver > 0)
            .map(({ producto_id, aDevolver: cantidad, precio_unitario }) => ({ producto_id, cantidad, precio_unitario }));

        if (!items.length) { Toast.error('Selecciona al menos un producto a devolver.'); return; }

        const btn = document.getElementById('btn-confirmar-devolucion');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner"></span> Procesando...';

        const res = await API.post('api/devoluciones.php', {
            venta_id: ventaActual.id,
            items,
            motivo,
        });

        btn.disabled = false;
        btn.innerHTML = '↩ Confirmar Devolución';

        if (res.success) {
            Toast.success(`Devolución #${res.data.devolucion_id} registrada correctamente.`);
            resetForm();
            loadHistorial();
        } else {
            Toast.error(res.message || 'Error al registrar la devolución.');
        }
    }

    function resetForm() {
        ventaActual = null;
        itemsDevolver = [];
        const el = document.getElementById('dev-venta-id');
        if (el) el.value = '';
        const infoEl = document.getElementById('dev-venta-info');
        if (infoEl) infoEl.innerHTML = '';
        const wrapEl = document.getElementById('dev-items-wrap');
        if (wrapEl) wrapEl.style.display = 'none';
        const motivoEl = document.getElementById('dev-motivo');
        if (motivoEl) motivoEl.value = '';
    }

    // ── Historial devoluciones ────────────────────────────────────────────────
    async function loadHistorial() {
        const desde = document.getElementById('dev-hist-desde')?.value || new Date().toISOString().slice(0, 10);
        const hasta = document.getElementById('dev-hist-hasta')?.value || desde;

        const res = await API.get(`api/devoluciones.php?action=listar&desde=${desde}&hasta=${hasta}`);
        const el = document.getElementById('tabla-historial-dev');
        if (!res.success || !el) return;

        const rows = (res.data || []).map(d => `
      <tr>
        <td class="mono">#${d.id}</td>
        <td class="mono">Venta #${d.venta_id}</td>
        <td>${Fmt.datetime(d.fecha)}</td>
        <td>${d.cajero}</td>
        <td>${d.motivo || '—'}</td>
        <td class="text-right fw-700 text-danger">${Fmt.money(d.total)}</td>
      </tr>`).join('') || `<tr><td colspan="6" class="text-center text-muted" style="padding:2rem">Sin devoluciones en el período</td></tr>`;

        el.querySelector('tbody').innerHTML = rows;
    }

    // ── Init ──────────────────────────────────────────────────────────────────
    function init() {
        document.getElementById('btn-buscar-venta-dev')?.addEventListener('click', buscarVenta);
        document.getElementById('dev-venta-id')?.addEventListener('keydown', e => { if (e.key === 'Enter') buscarVenta(); });
        document.getElementById('btn-confirmar-devolucion')?.addEventListener('click', confirmarDevolucion);
        document.getElementById('btn-cancelar-dev')?.addEventListener('click', resetForm);
        document.getElementById('btn-buscar-hist-dev')?.addEventListener('click', loadHistorial);
        loadHistorial();
    }

    return { init, cambiarCant };
})();

if (!window.viewHandlers) window.viewHandlers = {};
window.viewHandlers.devoluciones = () => Devoluciones.init();
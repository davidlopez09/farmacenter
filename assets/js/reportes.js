// assets/js/reportes.js
// ── Módulo de Reportes ─────────────────────────────────────────────────────

const Reportes = (() => {
    let chartInstance = null;
    let currentData = null;
    let currentTipo = null;

    // ── Cargar reporte ────────────────────────────────────────────────────────
    async function cargar(tipo) {
        const filtro = document.getElementById('rep-filtro')?.value || 'diario';
        const desde = document.getElementById('rep-desde')?.value || '';
        const hasta = document.getElementById('rep-hasta')?.value || '';

        // Highlight tab activo
        document.querySelectorAll('.rep-tab').forEach(t => t.classList.toggle('active', t.dataset.tipo === tipo));

        const params = new URLSearchParams({ tipo, filtro });
        if (desde) params.set('desde', desde);
        if (hasta) params.set('hasta', hasta);

        const el = document.getElementById('rep-content');
        if (el) el.innerHTML = '<div class="text-center text-muted" style="padding:3rem">Generando reporte...</div>';

        const res = await API.get(`api/reportes.php?${params}`);
        if (!res.success) { Toast.error(res.message || 'Error al generar el reporte.'); return; }

        currentData = res.data;
        currentTipo = tipo;

        switch (tipo) {
            case 'ventas': renderVentas(res.data); break;
            case 'compras': renderCompras(res.data); break;
            case 'devoluciones': renderDevoluciones(res.data); break;
            case 'caja': renderCaja(res.data); break;
        }
    }

    // ── Render: Ventas ────────────────────────────────────────────────────────
    function renderVentas(data) {
        const t = data.totales;
        const rows = (data.resumen || []).map(r => `
      <tr>
        <td>${Fmt.date(r.fecha)}</td>
        <td><span class="badge badge-blue">${r.metodo_pago}</span></td>
        <td class="text-center">${r.num_ventas}</td>
        <td class="text-right fw-700">${Fmt.money(r.total)}</td>
      </tr>`).join('') || '<tr><td colspan="4" class="text-center text-muted" style="padding:2rem">Sin datos en el período</td></tr>';

        document.getElementById('rep-content').innerHTML = `
      <div class="stats-grid" style="margin-bottom:1.5rem">
        <div class="stat-card">
          <div class="stat-icon green">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M7 18c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm10 0c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm-9.83-3.25l.03-.12.9-1.63H18c.75 0 1.41-.41 1.75-1.03l3.58-6.49A1 1 0 0022.45 4H5.21L4.27 2H1v2h2l3.6 7.59L5.25 14c-.16.28-.25.61-.25.96C5 16.1 5.9 17 7 17h11v-2H7.42a.25.25 0 01-.25-.25z"/></svg>
          </div>
          <div><div class="stat-value">${t.ventas || 0}</div><div class="stat-label">Ventas totales</div></div>
        </div>
        <div class="stat-card">
          <div class="stat-icon purple">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M11.8 10.9c-2.27-.59-3-1.2-3-2.15 0-1.09 1.01-1.85 2.7-1.85 1.78 0 2.44.85 2.5 2.1h2.21c-.07-1.72-1.12-3.3-3.21-3.81V3h-3v2.16c-1.94.42-3.5 1.68-3.5 3.61 0 2.31 1.91 3.46 4.7 4.13 2.5.6 3 1.48 3 2.41 0 .69-.49 1.79-2.7 1.79-2.06 0-2.87-.92-2.98-2.1h-2.2c.12 2.19 1.76 3.42 3.68 3.83V21h3v-2.15c1.95-.37 3.5-1.5 3.5-3.55 0-2.84-2.43-3.81-4.7-4.4z"/></svg>
          </div>
          <div><div class="stat-value">${Fmt.money(t.total || 0)}</div><div class="stat-label">Ingresos totales</div></div>
        </div>
      </div>
      <div class="card">
        <div class="card-header"><h3>Detalle por día y método</h3><span class="text-muted" style="font-size:.82rem">${Fmt.date(data.desde)} — ${Fmt.date(data.hasta)}</span></div>
        <div class="table-wrap">
          <table>
            <thead><tr><th>Fecha</th><th>Método de pago</th><th class="text-center">N° Ventas</th><th class="text-right">Total</th></tr></thead>
            <tbody>${rows}</tbody>
          </table>
        </div>
      </div>
      <div class="card mt-2">
        <div class="card-header"><h3>Gráfico de ingresos</h3></div>
        <div class="card-body"><canvas id="rep-chart" height="80"></canvas></div>
      </div>`;

        dibujarBarras(data.resumen || [], 'fecha', 'total', '#00b894');
    }

    // ── Render: Compras ───────────────────────────────────────────────────────
    function renderCompras(data) {
        const t = data.totales;
        const rows = (data.resumen || []).map(r => `
      <tr>
        <td>${Fmt.date(r.fecha)}</td>
        <td>${r.usuario}</td>
        <td class="text-center">${r.num_compras}</td>
        <td class="text-right fw-700 text-danger">${Fmt.money(r.total)}</td>
      </tr>`).join('') || '<tr><td colspan="4" class="text-center text-muted" style="padding:2rem">Sin datos</td></tr>';

        document.getElementById('rep-content').innerHTML = `
      <div class="stats-grid" style="margin-bottom:1.5rem">
        <div class="stat-card"><div class="stat-icon red"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M19 3H5c-1.11 0-2 .89-2 2v14c0 1.11.89 2 2 2h14c1.11 0 2-.89 2-2V5c0-1.11-.89-2-2-2zm-7 3c1.1 0 2 .9 2 2s-.9 2-2 2-2-.9-2-2 .9-2 2-2zm0 14c-2.67 0-8-1.34-8-4v-2c0-2.66 5.33-4 8-4s8 1.34 8 4v2c0 2.66-5.33 4-8 4z"/></svg></div>
          <div><div class="stat-value">${t.compras || 0}</div><div class="stat-label">Compras totales</div></div></div>
        <div class="stat-card"><div class="stat-icon yellow"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M11.8 10.9c-2.27-.59-3-1.2-3-2.15 0-1.09 1.01-1.85 2.7-1.85 1.78 0 2.44.85 2.5 2.1h2.21c-.07-1.72-1.12-3.3-3.21-3.81V3h-3v2.16c-1.94.42-3.5 1.68-3.5 3.61 0 2.31 1.91 3.46 4.7 4.13 2.5.6 3 1.48 3 2.41 0 .69-.49 1.79-2.7 1.79-2.06 0-2.87-.92-2.98-2.1h-2.2c.12 2.19 1.76 3.42 3.68 3.83V21h3v-2.15c1.95-.37 3.5-1.5 3.5-3.55 0-2.84-2.43-3.81-4.7-4.4z"/></svg></div>
          <div><div class="stat-value">${Fmt.money(t.total || 0)}</div><div class="stat-label">Total invertido</div></div></div>
      </div>
      <div class="card">
        <div class="card-header"><h3>Detalle de compras</h3></div>
        <div class="table-wrap"><table>
          <thead><tr><th>Fecha</th><th>Usuario</th><th class="text-center">N° Compras</th><th class="text-right">Total</th></tr></thead>
          <tbody>${rows}</tbody>
        </table></div>
      </div>`;
    }

    // ── Render: Devoluciones ──────────────────────────────────────────────────
    function renderDevoluciones(data) {
        const t = data.totales;
        const rows = (data.resumen || []).map(r => `
      <tr>
        <td>${Fmt.date(r.fecha)}</td>
        <td class="text-center">${r.num_devoluciones}</td>
        <td class="text-right fw-700 text-danger">${Fmt.money(r.total)}</td>
      </tr>`).join('') || '<tr><td colspan="3" class="text-center text-muted" style="padding:2rem">Sin datos</td></tr>';

        document.getElementById('rep-content').innerHTML = `
      <div class="stats-grid" style="margin-bottom:1.5rem">
        <div class="stat-card"><div class="stat-icon red"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 5V1L7 6l5 5V7c3.31 0 6 2.69 6 6s-2.69 6-6 6-6-2.69-6-6H4c0 4.42 3.58 8 8 8s8-3.58 8-8-3.58-8-8-8z"/></svg></div>
          <div><div class="stat-value">${t.devoluciones || 0}</div><div class="stat-label">Devoluciones</div></div></div>
        <div class="stat-card"><div class="stat-icon yellow"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M11.8 10.9c-2.27-.59-3-1.2-3-2.15 0-1.09 1.01-1.85 2.7-1.85 1.78 0 2.44.85 2.5 2.1h2.21c-.07-1.72-1.12-3.3-3.21-3.81V3h-3v2.16c-1.94.42-3.5 1.68-3.5 3.61 0 2.31 1.91 3.46 4.7 4.13 2.5.6 3 1.48 3 2.41 0 .69-.49 1.79-2.7 1.79-2.06 0-2.87-.92-2.98-2.1h-2.2c.12 2.19 1.76 3.42 3.68 3.83V21h3v-2.15c1.95-.37 3.5-1.5 3.5-3.55 0-2.84-2.43-3.81-4.7-4.4z"/></svg></div>
          <div><div class="stat-value">${Fmt.money(t.total || 0)}</div><div class="stat-label">Total devuelto</div></div></div>
      </div>
      <div class="card">
        <div class="card-header"><h3>Detalle de devoluciones</h3></div>
        <div class="table-wrap"><table>
          <thead><tr><th>Fecha</th><th class="text-center">N° Devoluciones</th><th class="text-right">Total</th></tr></thead>
          <tbody>${rows}</tbody>
        </table></div>
      </div>`;
    }

    // ── Render: Caja ──────────────────────────────────────────────────────────
    function renderCaja(data) {
        const ingreso = (data.resumen || []).find(r => r.tipo === 'INGRESO') || { total: 0, movimientos: 0 };
        const egreso = (data.resumen || []).find(r => r.tipo === 'EGRESO') || { total: 0, movimientos: 0 };
        const saldo = parseFloat(ingreso.total) - parseFloat(egreso.total);

        const movRows = (data.movimientos || []).map(m => `
      <tr>
        <td>${Fmt.datetime(m.fecha)}</td>
        <td><span class="badge ${m.tipo === 'INGRESO' ? 'badge-green' : 'badge-red'}">${m.tipo}</span></td>
        <td class="mono" style="font-size:.8rem">${m.referencia || '—'}</td>
        <td>${m.descripcion || '—'}</td>
        <td>${m.usuario}</td>
        <td class="text-right fw-700 ${m.tipo === 'INGRESO' ? 'text-success' : 'text-danger'}">${Fmt.money(m.monto)}</td>
      </tr>`).join('') || '<tr><td colspan="6" class="text-center text-muted" style="padding:2rem">Sin movimientos</td></tr>';

        document.getElementById('rep-content').innerHTML = `
      <div class="stats-grid" style="margin-bottom:1.5rem">
        <div class="stat-card"><div class="stat-icon green"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M11.8 10.9c-2.27-.59-3-1.2-3-2.15 0-1.09 1.01-1.85 2.7-1.85 1.78 0 2.44.85 2.5 2.1h2.21c-.07-1.72-1.12-3.3-3.21-3.81V3h-3v2.16c-1.94.42-3.5 1.68-3.5 3.61 0 2.31 1.91 3.46 4.7 4.13 2.5.6 3 1.48 3 2.41 0 .69-.49 1.79-2.7 1.79-2.06 0-2.87-.92-2.98-2.1h-2.2c.12 2.19 1.76 3.42 3.68 3.83V21h3v-2.15c1.95-.37 3.5-1.5 3.5-3.55 0-2.84-2.43-3.81-4.7-4.4z"/></svg></div>
          <div><div class="stat-value">${Fmt.money(ingreso.total)}</div><div class="stat-label">Ingresos (${ingreso.movimientos} mov.)</div></div></div>
        <div class="stat-card"><div class="stat-icon red"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M11.8 10.9c-2.27-.59-3-1.2-3-2.15 0-1.09 1.01-1.85 2.7-1.85 1.78 0 2.44.85 2.5 2.1h2.21c-.07-1.72-1.12-3.3-3.21-3.81V3h-3v2.16c-1.94.42-3.5 1.68-3.5 3.61 0 2.31 1.91 3.46 4.7 4.13 2.5.6 3 1.48 3 2.41 0 .69-.49 1.79-2.7 1.79-2.06 0-2.87-.92-2.98-2.1h-2.2c.12 2.19 1.76 3.42 3.68 3.83V21h3v-2.15c1.95-.37 3.5-1.5 3.5-3.55 0-2.84-2.43-3.81-4.7-4.4z"/></svg></div>
          <div><div class="stat-value">${Fmt.money(egreso.total)}</div><div class="stat-label">Egresos (${egreso.movimientos} mov.)</div></div></div>
        <div class="stat-card"><div class="stat-icon ${saldo >= 0 ? 'green' : 'red'}"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg></div>
          <div><div class="stat-value ${saldo >= 0 ? 'text-success' : 'text-danger'}">${Fmt.money(saldo)}</div><div class="stat-label">Saldo período</div></div></div>
      </div>
      <div class="card">
        <div class="card-header"><h3>Movimientos de caja</h3></div>
        <div class="table-wrap"><table>
          <thead><tr><th>Fecha</th><th>Tipo</th><th>Referencia</th><th>Descripción</th><th>Usuario</th><th class="text-right">Monto</th></tr></thead>
          <tbody>${movRows}</tbody>
        </table></div>
      </div>`;
    }

    // ── Mini gráfico de barras (Canvas API puro) ──────────────────────────────
    function dibujarBarras(data, labelKey, valueKey, color) {
        const canvas = document.getElementById('rep-chart');
        if (!canvas || !data.length) return;

        // Agrupar por fecha (suma)
        const agrupado = {};
        data.forEach(d => {
            agrupado[d[labelKey]] = (agrupado[d[labelKey]] || 0) + parseFloat(d[valueKey]);
        });
        const labels = Object.keys(agrupado).slice(-14);
        const values = labels.map(l => agrupado[l]);
        const max = Math.max(...values, 1);

        const ctx = canvas.getContext('2d');
        const W = canvas.width = canvas.offsetWidth;
        const H = canvas.height = 200;
        const pad = 40;
        const barW = Math.max(8, (W - pad * 2) / labels.length - 6);

        ctx.clearRect(0, 0, W, H);

        // Grid lines
        ctx.strokeStyle = '#e2e8f0';
        ctx.lineWidth = 1;
        [0.25, 0.5, 0.75, 1].forEach(f => {
            const y = H - pad - (H - pad * 2) * f;
            ctx.beginPath(); ctx.moveTo(pad, y); ctx.lineTo(W - pad, y); ctx.stroke();
            ctx.fillStyle = '#718096'; ctx.font = '10px Sora, sans-serif'; ctx.textAlign = 'right';
            ctx.fillText(Fmt.money(max * f), pad - 4, y + 4);
        });

        // Bars
        labels.forEach((label, i) => {
            const x = pad + i * ((W - pad * 2) / labels.length);
            const val = values[i];
            const bH = ((H - pad * 2) * val) / max;
            const y = H - pad - bH;

            ctx.fillStyle = color + 'cc';
            ctx.beginPath();
            ctx.roundRect(x + 2, y, barW, bH, [4, 4, 0, 0]);
            ctx.fill();

            ctx.fillStyle = '#718096'; ctx.font = '9px Sora, sans-serif'; ctx.textAlign = 'center';
            ctx.fillText(label.slice(5), x + barW / 2 + 2, H - 4);
        });
    }

    // ── Exportar a Excel (CSV) ────────────────────────────────────────────────
    function exportarExcel() {
        if (!currentData) {
            Toast.error('No hay información para exportar.');
            return;
        }

        const items = currentTipo === 'caja' ? currentData.movimientos : currentData.resumen;
        if (!items || items.length === 0) {
            Toast.error('No hay información para exportar en este período.');
            return;
        }

        let csv = '\uFEFF'; // BOM para Excel
        const sep = ';';

        if (currentTipo === 'ventas') {
            csv += `Fecha${sep}Método de pago${sep}N° Ventas${sep}Total\n`;
            items.forEach(r => csv += `${r.fecha}${sep}${r.metodo_pago}${sep}${r.num_ventas}${sep}${r.total}\n`);
        } else if (currentTipo === 'compras') {
            csv += `Fecha${sep}Usuario${sep}N° Compras${sep}Total\n`;
            items.forEach(r => csv += `${r.fecha}${sep}${r.usuario}${sep}${r.num_compras}${sep}${r.total}\n`);
        } else if (currentTipo === 'devoluciones') {
            csv += `Fecha${sep}N° Devoluciones${sep}Total\n`;
            items.forEach(r => csv += `${r.fecha}${sep}${r.num_devoluciones}${sep}${r.total}\n`);
        } else if (currentTipo === 'caja') {
            csv += `Fecha${sep}Tipo${sep}Referencia${sep}Descripción${sep}Usuario${sep}Monto\n`;
            items.forEach(m => csv += `${m.fecha}${sep}${m.tipo}${sep}${m.referencia}${sep}${m.descripcion}${sep}${m.usuario}${sep}${m.monto}\n`);
        }

        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `Reporte_${currentTipo}_${new Date().toISOString().slice(0,10)}.csv`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
    }

    // ── Init ──────────────────────────────────────────────────────────────────
    function init() {
        // Default: ventas diarias
        // const hoy = new Date().toISOString().slice(0, 10);
        const hoy = new Date().toLocaleDateString('en-CA');
        const desdeEl = document.getElementById('rep-desde');
        const hastaEl = document.getElementById('rep-hasta');
        if (desdeEl && !desdeEl.value) desdeEl.value = hoy;
        if (hastaEl && !hastaEl.value) hastaEl.value = hoy;

        document.querySelectorAll('.rep-tab').forEach(btn => {
            btn.addEventListener('click', () => cargar(btn.dataset.tipo));
        });

        document.getElementById('btn-generar-rep')?.addEventListener('click', () => {
            const tipoActivo = document.querySelector('.rep-tab.active')?.dataset.tipo || 'ventas';
            cargar(tipoActivo);
        });

        document.getElementById('btn-exportar-excel')?.addEventListener('click', exportarExcel);

        cargar('ventas');
    }

    return { init };
})();

if (!window.viewHandlers) window.viewHandlers = {};
window.viewHandlers.reportes = () => Reportes.init();
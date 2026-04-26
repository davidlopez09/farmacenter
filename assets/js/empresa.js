// assets/js/empresa.js
// ── Módulo de Empresa ──────────────────────────────────────────────────────

const Empresa = (() => {

    async function load() {
        const res = await API.get('api/empresa.php');
        if (!res.success) { Toast.error('No se pudo cargar la información de la empresa.'); return; }
        const e = res.data;

        const set = (id, val) => { const el = document.getElementById(id); if (el) el.value = val || ''; };
        set('emp-nombre', e.nombre);
        set('emp-direccion', e.direccion);
        set('emp-telefono', e.telefono);
        set('emp-email', e.email);
        set('emp-nit', e.nit);
        set('emp-moneda', e.moneda || 'COP');
        set('emp-impuesto', e.impuesto || 0);

        // Preview
        const preview = document.getElementById('emp-preview');
        if (preview) {
            preview.innerHTML = `
        <div style="padding:1.5rem;border:2px dashed var(--border);border-radius:var(--radius);background:var(--surface-2)">
          <div style="font-size:1.3rem;font-weight:700;color:var(--brand)">${e.nombre}</div>
          <div style="color:var(--text-muted);margin-top:.3rem;font-size:.88rem">
            ${e.direccion || ''} ${e.telefono ? '· Tel: ' + e.telefono : ''}<br>
            ${e.email || ''} ${e.nit ? '· NIT: ' + e.nit : ''}
          </div>
        </div>`;
        }
    }

    async function save() {
        const data = {
            nombre: document.getElementById('emp-nombre')?.value?.trim(),
            direccion: document.getElementById('emp-direccion')?.value?.trim(),
            telefono: document.getElementById('emp-telefono')?.value?.trim(),
            email: document.getElementById('emp-email')?.value?.trim(),
            nit: document.getElementById('emp-nit')?.value?.trim(),
            moneda: document.getElementById('emp-moneda')?.value || 'COP',
            impuesto: parseFloat(document.getElementById('emp-impuesto')?.value) || 0,
        };

        if (!data.nombre) { Toast.error('El nombre de la empresa es requerido.'); return; }

        const btn = document.getElementById('btn-guardar-empresa');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner"></span> Guardando...';

        const res = await API.put('api/empresa.php', data);

        btn.disabled = false;
        btn.innerHTML = '💾 Guardar cambios';

        if (res.success) {
            Toast.success('Información de empresa actualizada correctamente.');
            // Actualizar nombre en sidebar
            const sEl = document.getElementById('empresa-nombre');
            if (sEl) sEl.textContent = data.nombre;
            load();
        } else {
            Toast.error(res.message || 'Error al actualizar la empresa.');
        }
    }

    function init() {
        load();
        document.getElementById('btn-guardar-empresa')?.addEventListener('click', save);
    }

    return { init };
})();

if (!window.viewHandlers) window.viewHandlers = {};
window.viewHandlers.empresa = () => Empresa.init();
// assets/js/usuarios.js
// ── Módulo de Usuarios ─────────────────────────────────────────────────────

const Usuarios = (() => {
    let editingId = null;

    async function load() {
        const tbody = document.getElementById('tbody-usuarios');
        if (!tbody) return;
        tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted" style="padding:2rem">Cargando...</td></tr>';

        const res = await API.get('api/usuarios.php');
        if (!res.success) { Toast.error(res.message); return; }

        if (!res.data.length) {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted" style="padding:2rem">No hay usuarios registrados.</td></tr>';
            return;
        }

        tbody.innerHTML = res.data.map(u => `
      <tr>
        <td>${u.id}</td>
        <td>${u.usuario}</td>
        <td>
          <div style="display:flex;align-items:center;gap:.6rem">
            <div class="sidebar-avatar" style="width:30px;height:30px;font-size:.75rem;flex-shrink:0">${u.nombre.charAt(0).toUpperCase()}</div>
            <span class="fw-700">${u.nombre}</span>
          </div>
        </td>
        <td>${u.email}</td>
        <td><span class="badge ${u.rol === 'ADMIN' ? 'badge-blue' : 'badge-gray'}">${u.rol}</span></td>
        <td><span class="badge ${u.estado == 1 ? 'badge-green' : 'badge-red'}">${u.estado == 1 ? 'Activo' : 'Inactivo'}</span></td>
        <td>
          <button class="btn btn-ghost btn-sm" onclick="Usuarios.edit(${JSON.stringify(u).replace(/"/g, '&quot;')})">✏️ Editar</button>
        </td>
      </tr>`).join('');
    }

    function openNew() {
        editingId = null;
        document.getElementById('modal-usr-title').textContent = 'Nuevo Usuario';
        document.getElementById('form-usuario').reset();
        document.getElementById('usr-password-help').style.display = 'none';
        document.getElementById('usr-estado-wrap').style.display = 'none';
        Modal.open('modal-usuario');
    }

    function edit(u) {
        editingId = u.id;
        document.getElementById('modal-usr-title').textContent = 'Editar Usuario';
        document.getElementById('usr-usuario').value = u.usuario || '';
        document.getElementById('usr-nombre').value = u.nombre;
        document.getElementById('usr-email').value = u.email;
        document.getElementById('usr-password').value = '';
        document.getElementById('usr-rol').value = u.rol === 'ADMIN' ? '1' : '2';
        document.getElementById('usr-estado').value = u.estado;
        document.getElementById('usr-password-help').style.display = '';
        document.getElementById('usr-estado-wrap').style.display = '';
        Modal.open('modal-usuario');
    }

    async function save() {
        const usuario = document.getElementById('usr-usuario')?.value?.trim();
        const nombre = document.getElementById('usr-nombre')?.value?.trim();
        const email = document.getElementById('usr-email')?.value?.trim();
        const password = document.getElementById('usr-password')?.value;
        const rol_id = parseInt(document.getElementById('usr-rol')?.value);
        const estado = parseInt(document.getElementById('usr-estado')?.value ?? 1);

        if (!usuario || !nombre || !email) { Toast.error('Usuario, nombre y email son requeridos.'); return; }
        if (!editingId && !password) { Toast.error('La contraseña es requerida para nuevos usuarios.'); return; }

        const btn = document.getElementById('btn-guardar-usr');
        btn.disabled = true;

        const data = { usuario, nombre, email, rol_id, estado };
        if (password) data.password = password;

        let res;
        if (editingId) {
            res = await API.put('api/usuarios.php', { ...data, id: editingId });
        } else {
            res = await API.post('api/usuarios.php', data);
        }

        btn.disabled = false;

        if (res.success) {
            Toast.success(res.message);
            Modal.close('modal-usuario');
            load();
        } else {
            Toast.error(res.message || 'Error al guardar el usuario.');
        }
    }

    function init() {
        load();
        document.getElementById('btn-nuevo-usr')?.addEventListener('click', openNew);
        document.getElementById('btn-guardar-usr')?.addEventListener('click', save);
    }

    return { init, edit };
})();

if (!window.viewHandlers) window.viewHandlers = {};
window.viewHandlers.usuarios = () => Usuarios.init();
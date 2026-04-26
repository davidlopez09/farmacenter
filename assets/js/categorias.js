// assets/js/categorias.js
// ── Módulo de Categorías ───────────────────────────────────────────────────

const Categorias = (() => {
  let editingId = null;

  async function load() {
    const tbody = document.getElementById('tbody-categorias');
    if (!tbody) return;
    tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted" style="padding:2rem">Cargando...</td></tr>';

    const res = await API.get('api/categorias.php');
    if (!res.success) { Toast.error(res.message); return; }

    if (!res.data.length) {
      tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted" style="padding:2rem">No hay categorías registradas.</td></tr>';
      return;
    }

    tbody.innerHTML = res.data.map(c => `
      <tr>
        <td>${c.id}</td>
        <td class="fw-700">${c.nombre}</td>
        <td>${c.descripcion || '—'}</td>
        <td>
          <div style="display:flex;gap:.3rem">
            <button class="btn btn-ghost btn-sm" onclick="Categorias.edit(${c.id}, '${c.nombre.replace(/'/g,"\\'")}', '${(c.descripcion||'').replace(/'/g,"\\'")}')">✏️ Editar</button>
            <button class="btn btn-danger btn-sm" onclick="Categorias.remove(${c.id}, '${c.nombre.replace(/'/g,"\\'")}')">🗑 Eliminar</button>
          </div>
        </td>
      </tr>`).join('');
  }

  function openNew() {
    editingId = null;
    document.getElementById('modal-cat-title').textContent = 'Nueva Categoría';
    document.getElementById('cat-nombre').value      = '';
    document.getElementById('cat-descripcion').value = '';
    Modal.open('modal-categoria');
  }

  function edit(id, nombre, descripcion) {
    editingId = id;
    document.getElementById('modal-cat-title').textContent = 'Editar Categoría';
    document.getElementById('cat-nombre').value      = nombre;
    document.getElementById('cat-descripcion').value = descripcion;
    Modal.open('modal-categoria');
  }

  async function save() {
    const nombre      = document.getElementById('cat-nombre')?.value?.trim();
    const descripcion = document.getElementById('cat-descripcion')?.value?.trim();

    if (!nombre) { Toast.error('El nombre de la categoría es requerido.'); return; }

    const btn = document.getElementById('btn-guardar-cat');
    btn.disabled = true;

    let res;
    if (editingId) {
      res = await API.put('api/categorias.php', { id: editingId, nombre, descripcion });
    } else {
      res = await API.post('api/categorias.php', { nombre, descripcion });
    }

    btn.disabled = false;

    if (res.success) {
      Toast.success(res.message);
      Modal.close('modal-categoria');
      load();
    } else {
      Toast.error(res.message || 'Error al guardar la categoría.');
    }
  }

  function remove(id, nombre) {
    confirmAction(`¿Eliminar la categoría "${nombre}"?`, async () => {
      const res = await API.delete(`api/categorias.php?id=${id}`);
      if (res.success) { Toast.success(res.message); load(); }
      else Toast.error(res.message);
    });
  }

  function init() {
    load();
    document.getElementById('btn-nueva-cat')?.addEventListener('click', openNew);
    document.getElementById('btn-guardar-cat')?.addEventListener('click', save);
  }

  return { init, edit, remove };
})();

if (!window.viewHandlers) window.viewHandlers = {};
window.viewHandlers.categorias = () => Categorias.init();
<!-- views/usuarios.php -->

<div class="card">
  <div class="card-header">
    <h3>👥 Gestión de Usuarios</h3>
    <button class="btn btn-primary" id="btn-nuevo-usr">+ Nuevo Usuario</button>
  </div>
  <div class="table-wrap">
    <table>
      <thead>
        <tr><th>#</th><th>Usuario</th><th>Nombre</th><th>Email</th><th>Rol</th><th>Estado</th><th>Acciones</th></tr>
      </thead>
      <tbody id="tbody-usuarios">
        <tr><td colspan="7" class="text-center text-muted" style="padding:2rem">Cargando...</td></tr>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal usuario -->
<div id="modal-usuario" class="modal-overlay">
  <div class="modal">
    <div class="modal-header">
      <h3 id="modal-usr-title">Nuevo Usuario</h3>
      <button class="modal-close" onclick="Modal.close('modal-usuario')">✕</button>
    </div>
    <div class="modal-body">
      <form id="form-usuario" onsubmit="return false">
        <div class="form-group">
          <label for="usr-usuario">Usuario de acceso *</label>
          <input type="text" id="usr-usuario" class="form-control" placeholder="Nombre de usuario para iniciar sesión" maxlength="20">
        </div>
        <div class="form-group">
          <label for="usr-nombre">Nombre completo *</label>
          <input type="text" id="usr-nombre" class="form-control" placeholder="Nombre del usuario">
        </div>
        <div class="form-group">
          <label for="usr-email">Correo electrónico *</label>
          <input type="email" id="usr-email" class="form-control" placeholder="correo@farmacia.com">
        </div>
        <div class="form-group">
          <label for="usr-password">
            Contraseña *
            <span id="usr-password-help" style="display:none;font-weight:400;font-size:.75rem;color:var(--text-muted)"> (déjalo vacío para no cambiar)</span>
          </label>
          <input type="password" id="usr-password" class="form-control" placeholder="Mínimo 6 caracteres" autocomplete="new-password">
        </div>
        <div class="form-group">
          <label for="usr-rol">Rol *</label>
          <select id="usr-rol" class="form-control">
            <option value="1">ADMIN</option>
            <option value="2">EMPLEADO</option>
          </select>
        </div>
        <div id="usr-estado-wrap" class="form-group" style="display:none">
          <label for="usr-estado">Estado</label>
          <select id="usr-estado" class="form-control">
            <option value="1">Activo</option>
            <option value="0">Inactivo</option>
          </select>
        </div>
      </form>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" onclick="Modal.close('modal-usuario')">Cancelar</button>
      <button class="btn btn-primary" id="btn-guardar-usr">💾 Guardar</button>
    </div>
  </div>
</div>
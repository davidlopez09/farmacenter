<!-- views/categorias.php -->

<div class="card">
  <div class="card-header">
    <h3>🏷 Categorías de Productos</h3>
    <button class="btn btn-primary" id="btn-nueva-cat">+ Nueva Categoría</button>
  </div>
  <div class="table-wrap">
    <table>
      <thead>
        <tr><th>#</th><th>Nombre</th><th>Descripción</th><th>Acciones</th></tr>
      </thead>
      <tbody id="tbody-categorias">
        <tr><td colspan="4" class="text-center text-muted" style="padding:2rem">Cargando...</td></tr>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal categoría -->
<div id="modal-categoria" class="modal-overlay">
  <div class="modal">
    <div class="modal-header">
      <h3 id="modal-cat-title">Nueva Categoría</h3>
      <button class="modal-close" onclick="Modal.close('modal-categoria')">✕</button>
    </div>
    <div class="modal-body">
      <div class="form-group">
        <label for="cat-nombre">Nombre *</label>
        <input type="text" id="cat-nombre" class="form-control" placeholder="Ej: Analgésicos, Vitaminas...">
      </div>
      <div class="form-group">
        <label for="cat-descripcion">Descripción</label>
        <textarea id="cat-descripcion" class="form-control" rows="3" placeholder="Descripción opcional"></textarea>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" onclick="Modal.close('modal-categoria')">Cancelar</button>
      <button class="btn btn-primary" id="btn-guardar-cat">💾 Guardar</button>
    </div>
  </div>
</div>
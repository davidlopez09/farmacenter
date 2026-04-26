// assets/js/productos.js
// ── Módulo de Productos ────────────────────────────────────────────────────

const Productos = (() => {
    let pagina = 1;
    let totalPaginas = 1;
    let categorias = [];
    let editingId = null;

    // ── Cargar categorías para selects ────────────────────────────────────────
    async function loadCategorias() {
        const res = await API.get("api/categorias.php");
        if (res.success) {
            categorias = res.data;
            ["prod-categoria", "filter-categoria"].forEach((id) => {
                const el = document.getElementById(id);
                if (!el) return;
                const blank =
                    id === "filter-categoria"
                        ? '<option value="">Todas las categorías</option>'
                        : '<option value="">Sin categoría</option>';
                el.innerHTML = blank + categorias.map((c) => `<option value="${c.id}">${c.nombre}</option>`).join("");
            });
        }
    }

    // ── Cargar y renderizar tabla ─────────────────────────────────────────────
    async function loadProductos() {
        const search = document.getElementById("prod-search")?.value?.trim() || "";
        const catId = document.getElementById("filter-categoria")?.value || "";
        const tbody = document.getElementById("tbody-productos");
        if (!tbody) return;

        tbody.innerHTML =
            '<tr><td colspan="8" class="text-center text-muted" style="padding:2rem">Cargando...</td></tr>';

        const res = await API.get(
            `api/productos.php?pagina=${pagina}&search=${encodeURIComponent(search)}&categoria=${catId}`,
        );
        if (!res.success) {
            Toast.error(res.message);
            return;
        }

        totalPaginas = res.data.paginas || 1;

        if (!res.data.productos.length) {
            tbody.innerHTML =
                '<tr><td colspan="8" class="text-center text-muted" style="padding:2rem">No se encontraron productos.</td></tr>';
            renderPagination("prod-pagination", pagina, totalPaginas, (p) => {
                pagina = p;
                loadProductos();
            });
            return;
        }

        tbody.innerHTML = res.data.productos
            .map((p) => {
                const stockClass = p.stock === 0 ? "no-stock" : p.stock <= p.stock_minimo ? "low-stock" : "";
                const stockBadge =
                    p.stock === 0
                        ? '<span class="badge badge-red">Sin stock</span>'
                        : p.stock <= p.stock_minimo
                            ? '<span class="badge badge-yellow">Stock bajo</span>'
                            : `<span class="badge badge-green">${p.stock}</span>`;
                return `
        <tr class="${stockClass}">
          <td class="mono" style="font-size:.78rem">${p.codigo_barras || "—"}</td>
          <td class="fw-700">${p.nombre}</td>
          <td>${p.categoria || "—"}</td>
          <td>${Fmt.money(p.precio_compra)}</td>
          <td class="fw-700">${Fmt.money(p.precio_venta)}</td>
          <td>${stockBadge}</td>
          <td>${p.stock_minimo}</td>
          <td>
            <div style="display:flex;gap:.3rem">
              <button class="btn btn-ghost btn-sm" onclick="Productos.edit(${p.id})">✏️</button>
              <button class="btn btn-danger btn-sm" onclick="Productos.remove(${p.id}, '${p.nombre.replace(/'/g, "\\'")}')">🗑</button>
            </div>
          </td>
        </tr>`;
            })
            .join("");

        renderPagination("prod-pagination", pagina, totalPaginas, (p) => {
            pagina = p;
            loadProductos();
        });

        // Stat
        const statEl = document.getElementById("prod-count");
        if (statEl) statEl.textContent = `${res.data.total} productos`;
    }

    // ── Abrir modal nuevo ─────────────────────────────────────────────────────
    function openNew() {
        editingId = null;
        document.getElementById("modal-prod-title").textContent = "Nuevo Producto";
        document.getElementById("form-producto").reset();
        Modal.open("modal-producto");
    }

    // ── Editar ────────────────────────────────────────────────────────────────
    async function edit(id) {
        const res = await API.get(`api/productos.php?action=uno&id=${id}`);
        if (!res.success) {
            Toast.error(res.message);
            return;
        }
        const p = res.data;
        editingId = id;
        document.getElementById("modal-prod-title").textContent = "Editar Producto";
        setField("prod-codigo", p.codigo_barras || "");
        setField("prod-nombre", p.nombre);
        setField("prod-descripcion", p.descripcion || "");
        setField("prod-categoria", p.categoria_id || "");
        setField("prod-precio-compra", p.precio_compra);
        setField("prod-precio-venta", p.precio_venta);
        setField("prod-stock", p.stock);
        setField("prod-stock-min", p.stock_minimo);
        Modal.open("modal-producto");
    }

    // ── Guardar (crear o actualizar) ──────────────────────────────────────────
    async function save() {
        const data = {
            codigo_barras: getField("prod-codigo"),
            nombre: getField("prod-nombre"),
            descripcion: getField("prod-descripcion"),
            categoria_id: getField("prod-categoria") || null,
            precio_compra: parseFloat(getField("prod-precio-compra")) || 0,
            precio_venta: parseFloat(getField("prod-precio-venta")) || 0,
            stock: parseInt(getField("prod-stock")) || 0,
            stock_minimo: parseInt(getField("prod-stock-min")) || 0,
        };

        if (!data.nombre) {
            Toast.error("El nombre del producto es requerido.");
            return;
        }
        if (data.precio_venta <= 0) {
            Toast.error("El precio de venta debe ser mayor a 0.");
            return;
        }

        const btn = document.getElementById("btn-guardar-producto");
        btn.disabled = true;

        let res;
        if (editingId) {
            res = await API.put("api/productos.php", { ...data, id: editingId });
        } else {
            res = await API.post("api/productos.php", data);
        }

        btn.disabled = false;

        if (res.success) {
            Toast.success(res.message);
            Modal.close("modal-producto");
            loadProductos();
        } else {
            Toast.error(res.message || "Error al guardar el producto.");
        }
    }

    // ── Eliminar (soft) ───────────────────────────────────────────────────────
    function remove(id, nombre) {
        confirmAction(`¿Eliminar el producto "${nombre}"? Esta acción lo desactivará.`, async () => {
            const res = await API.delete(`api/productos.php?id=${id}`);
            if (res.success) {
                Toast.success(res.message);
                loadProductos();
            } else Toast.error(res.message);
        });
    }

    // ── Helpers ───────────────────────────────────────────────────────────────
    const getField = (id) => document.getElementById(id)?.value ?? "";
    const setField = (id, val) => {
        const el = document.getElementById(id);
        if (el) el.value = val;
    };

    // ── Init ──────────────────────────────────────────────────────────────────
    function init() {
        loadCategorias();
        loadProductos();

        document.getElementById("btn-nuevo-producto")?.addEventListener("click", openNew);
        document.getElementById("btn-guardar-producto")?.addEventListener("click", save);
        document.getElementById("btn-buscar-producto")?.addEventListener("click", () => {
            pagina = 1;
            loadProductos();
        });
        document.getElementById("prod-search")?.addEventListener("keydown", (e) => {
            if (e.key === "Enter") {
                pagina = 1;
                loadProductos();
            }
        });
        document.getElementById("filter-categoria")?.addEventListener("change", () => {
            pagina = 1;
            loadProductos();
        });
    }

    return { init, edit, remove };
})();

if (!window.viewHandlers) window.viewHandlers = {};
window.viewHandlers.productos = () => Productos.init();

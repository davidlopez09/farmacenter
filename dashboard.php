<?php
// dashboard.php

require_once __DIR__ . '/config/session.php';
requireLogin();

$user    = currentUser();
$isAdmin = isAdmin();
$initial = strtoupper(mb_substr($user['nombre'], 0, 1));
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FarmaSys — Dashboard</title>
  <link rel="stylesheet" href="assets/css/main.css">
</head>

<body>
  <div class="app">

    <!-- ══ SIDEBAR ══════════════════════════════════════════════════════ -->
    <aside class="sidebar">
      <div class="sidebar-header">
        <div class="sidebar-logo">
          <svg viewBox="0 0 24 24">
            <path d="M19 3H5C3.89 3 3 3.89 3 5v14c0 1.11.89 2 2 2h14c1.11 0 2-.89 2-2V5c0-1.11-.89-2-2-2zm-6 9h-2v2H9v-2H7v-2h2V8h2v2h2v2z" />
          </svg>
        </div>
        <div class="sidebar-title">
          FarmaSys
          <small id="empresa-nombre">Cargando...</small>
        </div>
      </div>

      <nav class="sidebar-nav">
        <div class="nav-section">
          <div class="nav-section-label">General</div>
          <div class="nav-item active" data-view="inicio" onclick="navigateTo('inicio')">
            <?= svgIcon('home') ?> Dashboard
          </div>
          <div class="nav-item" data-view="ventas" onclick="navigateTo('ventas')">
            <?= svgIcon('cart') ?> Ventas
          </div>
          <div class="nav-item" data-view="devoluciones" onclick="navigateTo('devoluciones')">
            <?= svgIcon('return') ?> Devoluciones
          </div>
        </div>

        <?php if ($isAdmin): ?>
          <div class="nav-section">
            <div class="nav-section-label">Inventario</div>
            <div class="nav-item" data-view="productos" onclick="navigateTo('productos')">
              <?= svgIcon('box') ?> Productos
            </div>
            <div class="nav-item" data-view="categorias" onclick="navigateTo('categorias')">
              <?= svgIcon('tag') ?> Categorías
            </div>
            <div class="nav-item" data-view="compras" onclick="navigateTo('compras')">
              <?= svgIcon('purchase') ?> Compras
            </div>
          </div>
          <div class="nav-section">
            <div class="nav-section-label">Administración</div>
            <div class="nav-item" data-view="reportes" onclick="navigateTo('reportes')">
              <?= svgIcon('chart') ?> Reportes
            </div>
            <div class="nav-item" data-view="usuarios" onclick="navigateTo('usuarios')">
              <?= svgIcon('users') ?> Usuarios
            </div>
            <div class="nav-item" data-view="empresa" onclick="navigateTo('empresa')">
              <?= svgIcon('settings') ?> Empresa
            </div>
          </div>
        <?php endif; ?>
      </nav>

      <div class="sidebar-footer">
        <div class="sidebar-user">
          <div class="sidebar-avatar"><?= htmlspecialchars($initial) ?></div>
          <div>
            <div class="sidebar-user-name"><?= htmlspecialchars($user['nombre']) ?></div>
            <div class="sidebar-user-role"><?= htmlspecialchars($user['rol']) ?></div>
          </div>
        </div>
        <button class="btn btn-ghost btn-sm btn-full mt-2" id="btn-logout" style="color:#a0aec0;border-color:rgba(255,255,255,.1)">
          Cerrar sesión
        </button>
      </div>
    </aside>

    <!-- ══ MAIN ═════════════════════════════════════════════════════════ -->
    <main class="main">

      <!-- Topbar -->
      <div class="topbar">
        <div class="topbar-title">
          <h2 id="topbar-title">Dashboard</h2>
          <p id="topbar-subtitle">Resumen del sistema</p>
        </div>
        <div class="topbar-actions" id="topbar-actions"></div>
      </div>

      <div class="content">

        <!-- ─ INICIO ─────────────────────────────────────────────────── -->
        <div id="view-inicio" class="view-section">
          <div class="stats-grid" id="stats-grid">
            <div class="stat-card">
              <div class="stat-icon green">
                <svg viewBox="0 0 24 24" fill="currentColor">
                  <path d="M7 18c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm10 0c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm-9.83-3.25l.03-.12.9-1.63H18c.75 0 1.41-.41 1.75-1.03l3.58-6.49A1 1 0 0022.45 4H5.21L4.27 2H1v2h2l3.6 7.59L5.25 14c-.16.28-.25.61-.25.96C5 16.1 5.9 17 7 17h11v-2H7.42a.25.25 0 01-.25-.25z" />
                </svg>
              </div>
              <div>
                <div class="stat-value" id="stat-ventas-hoy">—</div>
                <div class="stat-label">Ventas hoy</div>
              </div>
            </div>
            <div class="stat-card">
              <div class="stat-icon purple">
                <svg viewBox="0 0 24 24" fill="currentColor">
                  <path d="M11.8 10.9c-2.27-.59-3-1.2-3-2.15 0-1.09 1.01-1.85 2.7-1.85 1.78 0 2.44.85 2.5 2.1h2.21c-.07-1.72-1.12-3.3-3.21-3.81V3h-3v2.16c-1.94.42-3.5 1.68-3.5 3.61 0 2.31 1.91 3.46 4.7 4.13 2.5.6 3 1.48 3 2.41 0 .69-.49 1.79-2.7 1.79-2.06 0-2.87-.92-2.98-2.1h-2.2c.12 2.19 1.76 3.42 3.68 3.83V21h3v-2.15c1.95-.37 3.5-1.5 3.5-3.55 0-2.84-2.43-3.81-4.7-4.4z" />
                </svg>
              </div>
              <div>
                <div class="stat-value" id="stat-ingresos-hoy">—</div>
                <div class="stat-label">Ingresos hoy</div>
              </div>
            </div>
            <div class="stat-card">
              <div class="stat-icon yellow">
                <svg viewBox="0 0 24 24" fill="currentColor">
                  <path d="M20 6h-2.18c.07-.44.18-.86.18-1.3C18 2.12 15.88 0 13.3 0c-1.3 0-2.4.5-3.2 1.3L9 2.5 7.9 1.3C7.1.5 6 0 4.7 0 2.12 0 0 2.12 0 4.7c0 .44.11.86.18 1.3H2C.9 6 0 6.9 0 8v12c0 1.1.9 2 2 2h20c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2zm-7.3-4c1.28 0 2.3 1.02 2.3 2.3 0 .44-.14.84-.37 1.18L9.9 4.85c.07-1.21 1.05-2.85 2.8-2.85zM4.7 2C5.98 2 7 3.02 7 4.3c0 .44-.14.84-.37 1.18L5.4 4.36A2.3 2.3 0 014.7 2zM2 20V8h9v12H2zm18 0h-7V8h7v12z" />
                </svg>
              </div>
              <div>
                <div class="stat-value" id="stat-productos">—</div>
                <div class="stat-label">Productos activos</div>
              </div>
            </div>
            <div class="stat-card">
              <div class="stat-icon red">
                <svg viewBox="0 0 24 24" fill="currentColor">
                  <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z" />
                </svg>
              </div>
              <div>
                <div class="stat-value" id="stat-low-stock">—</div>
                <div class="stat-label">Stock bajo</div>
              </div>
            </div>
          </div>

          <div class="card">
            <div class="card-header">
              <h3>Últimas ventas del día</h3>
            </div>
            <div class="card-body table-wrap" id="table-latest-ventas">
              <div class="text-center text-muted" style="padding:2rem">Cargando...</div>
            </div>
          </div>
        </div>

        <!-- ─ VENTAS ──────────────────────────────────────────────────── -->
        <div id="view-ventas" class="view-section" style="display:none">
          <?php include __DIR__ . '/views/ventas.php'; ?>
        </div>

        <!-- ─ DEVOLUCIONES ────────────────────────────────────────────── -->
        <div id="view-devoluciones" class="view-section" style="display:none">
          <?php include __DIR__ . '/views/devoluciones.php'; ?>
        </div>

        <?php if ($isAdmin): ?>
          <!-- ─ PRODUCTOS ───────────────────────────────────────────────── -->
          <div id="view-productos" class="view-section" style="display:none">
            <?php include __DIR__ . '/views/productos.php'; ?>
          </div>

          <!-- ─ CATEGORÍAS ─────────────────────────────────────────────── -->
          <div id="view-categorias" class="view-section" style="display:none">
            <?php include __DIR__ . '/views/categorias.php'; ?>
          </div>

          <!-- ─ COMPRAS ─────────────────────────────────────────────────── -->
          <div id="view-compras" class="view-section" style="display:none">
            <?php include __DIR__ . '/views/compras.php'; ?>
          </div>

          <!-- ─ REPORTES ────────────────────────────────────────────────── -->
          <div id="view-reportes" class="view-section" style="display:none">
            <?php include __DIR__ . '/views/reportes.php'; ?>
          </div>

          <!-- ─ USUARIOS ───────────────────────────────────────────────── -->
          <div id="view-usuarios" class="view-section" style="display:none">
            <?php include __DIR__ . '/views/usuarios.php'; ?>
          </div>

          <!-- ─ EMPRESA ────────────────────────────────────────────────── -->
          <div id="view-empresa" class="view-section" style="display:none">
            <?php include __DIR__ . '/views/empresa.php'; ?>
          </div>
        <?php endif; ?>

      </div><!-- /content -->
    </main>
  </div>

  <div id="toast-container"></div>
  <script src="assets/js/app.js"></script>
  <script src="assets/js/ventas.js"></script>
  <script src="assets/js/devoluciones.js"></script>
  <?php if ($isAdmin): ?>
    <script src="assets/js/productos.js"></script>
    <script src="assets/js/categorias.js"></script>
    <script src="assets/js/compras.js"></script>
    <script src="assets/js/reportes.js"></script>
    <script src="assets/js/usuarios.js"></script>
    <script src="assets/js/empresa.js"></script>
  <?php endif; ?>
  <script>
    // ── Dashboard init ──────────────────────────────────────────────────────
    const topbarTitles = {
      inicio: ['Dashboard', 'Resumen del sistema'],
      ventas: ['Punto de Venta', 'Registrar nueva venta'],
      devoluciones: ['Devoluciones', 'Gestión de devoluciones'],
      productos: ['Productos', 'Gestión de inventario'],
      categorias: ['Categorías', 'Clasificación de productos'],
      compras: ['Compras', 'Entrada de inventario'],
      reportes: ['Reportes', 'Análisis y estadísticas'],
      usuarios: ['Usuarios', 'Gestión de accesos'],
      empresa: ['Mi Empresa', 'Configuración general'],
    };

    // Override navigateTo to update topbar
    const _nav = navigateTo;
    window.navigateTo = function(view) {
      _nav(view);
      const [title, sub] = topbarTitles[view] || ['', ''];
      document.getElementById('topbar-title').textContent = title;
      document.getElementById('topbar-subtitle').textContent = sub;
    };

    // Logout
    document.getElementById('btn-logout').addEventListener('click', async () => {
      await API.get('api/logout.php');
      window.location.href = 'index.php';
    });

    // Load empresa name
    (async () => {
      const res = await API.get('api/empresa.php');
      if (res.success && res.data) {
        document.getElementById('empresa-nombre').textContent = res.data.nombre;
      }
    })();

    // Load dashboard stats
    async function loadDashboard() {
      // const hoy = new Date().toISOString().slice(0, 10);
      const hoy = new Date().toLocaleDateString('en-CA');

      // Ventas hoy
      const vRes = await API.get(`api/ventas.php?action=listar&desde=${hoy}&hasta=${hoy}&pagina=1`);
      if (vRes.success) {
        document.getElementById('stat-ventas-hoy').textContent = vRes.data.total;
        // sum of totals
        const sum = (vRes.data.ventas || []).reduce((a, v) => a + parseFloat(v.total), 0);
        document.getElementById('stat-ingresos-hoy').textContent = Fmt.money(sum);

        // Render table
        const tbody = (vRes.data.ventas || []).map(v => `
      <tr>
        <td class="mono">#${v.id}</td>
        <td>${Fmt.datetime(v.fecha)}</td>
        <td>${v.cajero}</td>
        <td><span class="badge badge-blue">${v.metodo_pago}</span></td>
        <td class="text-right fw-700">${Fmt.money(v.total)}</td>
      </tr>`).join('') || '<tr><td colspan="5" class="text-center text-muted" style="padding:1.5rem">Sin ventas hoy</td></tr>';
        document.getElementById('table-latest-ventas').innerHTML = `
      <table>
        <thead><tr><th>#</th><th>Fecha</th><th>Cajero</th><th>Método</th><th class="text-right">Total</th></tr></thead>
        <tbody>${tbody}</tbody>
      </table>`;
      }

      // Productos stats
      const pRes = await API.get('api/productos.php?pagina=1');
      if (pRes.success) {
        document.getElementById('stat-productos').textContent = pRes.data.total;
      }

      // Low stock (products where stock <= stock_minimo)
      const lowRes = await API.get('api/productos.php?pagina=1&low_stock=1');
      // We'll handle this via a simple filter; for now show placeholder
      document.getElementById('stat-low-stock').textContent = '—';
    }

    window.viewHandlers = {
      inicio: loadDashboard,
      ventas: Ventas.init,
      devoluciones: Devoluciones.init,
      productos: Productos.init,
      categorias: Categorias.init,
      compras: Compras.init,
      reportes: Reportes.init,
      usuarios: Usuarios.init,
      empresa: Empresa.init,

    };

    // Navigate to saved view on load, or default to 'inicio'
    const savedView = localStorage.getItem('activeView') || 'inicio';
    if (window.viewHandlers[savedView]) {
      navigateTo(savedView);
    } else {
      navigateTo('inicio');
    }
  </script>
</body>

</html>

<?php
function svgIcon(string $name): string
{
  $icons = [
    'home'     => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>',
    'cart'     => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M7 18c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm10 0c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm-9.83-3.25l.03-.12.9-1.63H18c.75 0 1.41-.41 1.75-1.03l3.58-6.49A1 1 0 0022.45 4H5.21L4.27 2H1v2h2l3.6 7.59L5.25 14c-.16.28-.25.61-.25.96C5 16.1 5.9 17 7 17h11v-2H7.42a.25.25 0 01-.25-.25z"/></svg>',
    'return'   => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 5V1L7 6l5 5V7c3.31 0 6 2.69 6 6s-2.69 6-6 6-6-2.69-6-6H4c0 4.42 3.58 8 8 8s8-3.58 8-8-3.58-8-8-8z"/></svg>',
    'box'      => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M20 2H4C3 2 2 3 2 4v3.01c0 .72.43 1.34 1 1.72V20c0 1.1 1.1 2 2 2h14c.9 0 2-.9 2-2V8.72c.57-.39 1-.99 1-1.71V4c0-1-1-2-2-2zm-5 12H9v-2h6v2zm5-8H4V4l16-.01V6z"/></svg>',
    'tag'      => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M17.63 5.84C17.27 5.33 16.67 5 16 5L5 5.01C3.9 5.01 3 5.9 3 7v10c0 1.1.9 1.99 2 1.99L16 19c.67 0 1.27-.33 1.63-.84L22 12l-4.37-6.16z"/></svg>',
    'purchase' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M19 3H5c-1.11 0-2 .89-2 2v14c0 1.11.89 2 2 2h14c1.11 0 2-.89 2-2V5c0-1.11-.89-2-2-2zm-7 3c1.1 0 2 .9 2 2s-.9 2-2 2-2-.9-2-2 .9-2 2-2zm0 14c-2.67 0-8-1.34-8-4v-2c0-2.66 5.33-4 8-4s8 1.34 8 4v2c0 2.66-5.33 4-8 4z"/></svg>',
    'chart'    => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M9 17H7v-7h2v7zm4 0h-2V7h2v10zm4 0h-2v-4h2v4zm2.5 2.1h-15V5h15v14.1zm0-16.1h-15c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h15c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2z"/></svg>',
    'users'    => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>',
    'settings' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M19.14 12.94c.04-.3.06-.61.06-.94 0-.32-.02-.64-.07-.94l2.03-1.58c.18-.14.23-.41.12-.61l-1.92-3.32c-.12-.22-.37-.29-.59-.22l-2.39.96c-.5-.38-1.03-.7-1.62-.94l-.36-2.54c-.04-.24-.24-.41-.48-.41h-3.84c-.24 0-.43.17-.47.41l-.36 2.54c-.59.24-1.13.57-1.62.94l-2.39-.96c-.22-.08-.47 0-.59.22L2.74 8.87c-.12.21-.08.47.12.61l2.03 1.58c-.05.3-.09.63-.09.94s.02.64.07.94l-2.03 1.58c-.18.14-.23.41-.12.61l1.92 3.32c.12.22.37.29.59.22l2.39-.96c.5.38 1.03.7 1.62.94l.36 2.54c.05.24.24.41.48.41h3.84c.24 0 .44-.17.47-.41l.36-2.54c.59-.24 1.13-.56 1.62-.94l2.39.96c.22.08.47 0 .59-.22l1.92-3.32c.12-.22.07-.47-.12-.61l-2.01-1.58zM12 15.6c-1.98 0-3.6-1.62-3.6-3.6s1.62-3.6 3.6-3.6 3.6 1.62 3.6 3.6-1.62 3.6-3.6 3.6z"/></svg>',
  ];
  return $icons[$name] ?? '';
}
?>
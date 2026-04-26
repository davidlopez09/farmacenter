<?php
// index.php

require_once __DIR__ . '/config/session.php';

// Si ya hay sesión, redirigir al dashboard
if (!empty($_SESSION['usuario_id'])) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Farmacia — Iniciar sesión</title>
  <link rel="stylesheet" href="assets/css/main.css">
  <style>
    .login-brand-circles { position: absolute; inset: 0; overflow: hidden; z-index: 0; }
    .circle { position: absolute; border-radius: 50%; background: rgba(255,255,255,.04); }
    .c1 { width: 300px; height: 300px; bottom: -80px; left: -80px; }
    .c2 { width: 200px; height: 200px; top: 40px; right: -40px; }
    .c3 { width: 120px; height: 120px; top: 50%; left: 30%; }
  </style>
</head>
<body>
<div class="login-page">

  <!-- Branding side -->
  <div class="login-brand">
    <div class="login-brand-icon">
     <img src="assets/img/logo.png" alt="logo">
    </div>
  </div>

  <!-- Form side -->
  <div class="login-form-wrap">
    <div class="login-box">
      <h2>Bienvenido 👋</h2>
      <p class="subtitle">Ingresa tus credenciales para continuar</p>

      <div id="login-alert"></div>

      <div class="form-group">
        <label for="usuario">Usuario</label>
        <input type="text" id="usuario" class="form-control" placeholder="Usuario" autocomplete="username">
      </div>

      <div class="form-group">
        <label for="password">Contraseña</label>
        <input type="password" id="password" class="form-control" placeholder="••••••••" autocomplete="current-password">
      </div>

      <button class="btn btn-primary btn-full mt-2" id="btn-login">
        <span id="btn-login-text">Iniciar sesión</span>
        <span id="btn-login-spinner" class="spinner" style="display:none;"></span>
      </button>
    </div>
  </div>
</div>

<div id="toast-container"></div>
<script src="assets/js/app.js"></script>
<script>
(function () {
  const usuarioEl = document.getElementById('usuario');
  const passEl  = document.getElementById('password');
  const btnEl   = document.getElementById('btn-login');
  const alertEl = document.getElementById('login-alert');

  function setLoading(on) {
    btnEl.disabled = on;
    document.getElementById('btn-login-text').textContent    = on ? 'Verificando...' : 'Iniciar sesión';
    document.getElementById('btn-login-spinner').style.display = on ? '' : 'none';
  }

  function showAlert(msg, type = 'danger') {
    alertEl.innerHTML = `<div class="alert alert-${type}">${msg}</div>`;
  }

  async function doLogin() {
    alertEl.innerHTML = '';
    const usuario    = usuarioEl.value.trim();
    const password = passEl.value;

    if (!usuario || !password) { showAlert('Por favor completa todos los campos.'); return; }

    setLoading(true);
    const res = await API.post('api/login.php', { usuario, password });
    setLoading(false);

    if (res.success) {
      showAlert('¡Acceso correcto! Redirigiendo...', 'success');
      setTimeout(() => { window.location.href = res.data.redirect; }, 600);
    } else {  
      showAlert(res.message || 'Error al iniciar sesión.');
      passEl.value = '';
      passEl.focus();
    }
  }

  btnEl.addEventListener('click', doLogin);
  [usuarioEl, passEl].forEach(el => el.addEventListener('keydown', e => { if (e.key === 'Enter') doLogin(); }));
})();
</script>
</body>
</html>
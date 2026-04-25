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
    <div class="login-brand-circles">
      <div class="circle c1"></div>
      <div class="circle c2"></div>
      <div class="circle c3"></div>
    </div>
    <div class="login-brand-icon">
      <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
        <path d="M19 3H5C3.89 3 3 3.89 3 5v14c0 1.11.89 2 2 2h14c1.11 0 2-.89 2-2V5c0-1.11-.89-2-2-2zm-7 3c1.1 0 2 .9 2 2s-.9 2-2 2-2-.9-2-2 .9-2 2-2zm4 10H8v-1c0-2 4-3.1 6-3.1 2 0 6 1.1 6 3.1v1H8z"/>
        <path d="M11 10h2v2h2v2h-2v2h-2v-2H9v-2h2z"/>
      </svg>
    </div>
    <h1>FarmaSys</h1>
    <p>Sistema de gestión farmacéutica</p>
  </div>

  <!-- Form side -->
  <div class="login-form-wrap">
    <div class="login-box">
      <h2>Bienvenido 👋</h2>
      <p class="subtitle">Ingresa tus credenciales para continuar</p>

      <div id="login-alert"></div>

      <div class="form-group">
        <label for="email">Correo electrónico</label>
        <input type="email" id="email" class="form-control" placeholder="usuario@farmacia.com" autocomplete="username">
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
  const emailEl = document.getElementById('email');
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
    const email    = emailEl.value.trim();
    const password = passEl.value;

    if (!email || !password) { showAlert('Por favor completa todos los campos.'); return; }

    setLoading(true);
    const res = await API.post('api/login.php', { email, password });
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
  [emailEl, passEl].forEach(el => el.addEventListener('keydown', e => { if (e.key === 'Enter') doLogin(); }));
})();
</script>
</body>
</html>
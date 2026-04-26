<?php
// api/login.php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/response.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonError('Método no permitido.', 405);
}

$body  = json_decode(file_get_contents('php://input'), true);
$usuario = trim($body['usuario'] ?? '');
$pass  = trim($body['password'] ?? '');

if (empty($usuario) || empty($pass)) {
    jsonError('Usuario y contraseña son requeridos.');
}

$db  = Database::connect();
$sql = "SELECT u.id, u.nombre, u.password, u.estado, r.nombre AS rol
        FROM usuarios u
        JOIN roles r ON r.id = u.rol_id
        WHERE u.usuario = ?
        LIMIT 1";

$stmt = $db->prepare($sql);
$stmt->execute([$usuario]);
$user = $stmt->fetch();

// if (!$user || !password_verify($pass, $user['password'])) {
if (!$user || $pass !== $user['password']) {
    jsonError('Credenciales incorrectas.', 401);
}

if ((int)$user['estado'] === 0) {
    jsonError('Usuario inactivo. Contacte al administrador.', 403);
}

// Crear sesión
$_SESSION['usuario_id'] = $user['id'];
$_SESSION['nombre']     = $user['nombre'];
$_SESSION['rol']        = $user['rol'];

jsonSuccess(['redirect' => 'dashboard.php'], 'Bienvenido, ' . $user['nombre']);
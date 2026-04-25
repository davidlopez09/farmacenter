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
$email = trim($body['email'] ?? '');
$pass  = trim($body['password'] ?? '');

if (empty($email) || empty($pass)) {
    jsonError('Email y contraseña son requeridos.');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonError('Email inválido.');
}

$db  = Database::connect();
$sql = "SELECT u.id, u.nombre, u.password, u.estado, r.nombre AS rol
        FROM usuarios u
        JOIN roles r ON r.id = u.rol_id
        WHERE u.email = ?
        LIMIT 1";

$stmt = $db->prepare($sql);
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user || !password_verify($pass, $user['password'])) {
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
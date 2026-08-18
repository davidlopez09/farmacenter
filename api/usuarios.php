<?php
// api/usuarios.php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/response.php';

requireLogin();
requireAdmin();
header('Content-Type: application/json');

$db     = Database::connect();
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $stmt = $db->query("SELECT u.id, u.usuario, u.nombre, u.email, u.estado, r.nombre AS rol, u.fecha_creacion FROM usuarios u JOIN roles r ON r.id = u.rol_id ORDER BY u.nombre");
        jsonSuccess($stmt->fetchAll());
        break;

    case 'POST':
        $d = json_decode(file_get_contents('php://input'), true);
        if (empty($d['usuario']) || empty($d['nombre']) || empty($d['email']) || empty($d['password'])) jsonError('Campos incompletos.');
        if (!filter_var($d['email'], FILTER_VALIDATE_EMAIL)) jsonError('Email inválido.');
        if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $d['usuario'])) jsonError('El nombre de usuario debe tener 3-20 caracteres (letras, números o _).');

        $check = $db->prepare("SELECT id FROM usuarios WHERE email = ? OR usuario = ?");
        $check->execute([$d['email'], $d['usuario']]);
        if ($check->fetch()) jsonError('El email o nombre de usuario ya está registrado.');

        $hash = password_hash($d['password'], PASSWORD_DEFAULT);
        $stmt = $db->prepare("INSERT INTO usuarios (nombre, email, usuario, password, rol_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$d['nombre'], $d['email'], $d['usuario'], $hash, (int)$d['rol_id']]);
        jsonSuccess(['id' => $db->lastInsertId()], 'Usuario creado correctamente.');
        break;

    case 'PUT':
        $d  = json_decode(file_get_contents('php://input'), true);
        $id = (int)($d['id'] ?? 0);
        if (!$id) jsonError('ID requerido.');
        if (empty($d['usuario']) || empty($d['nombre']) || empty($d['email'])) jsonError('Campos incompletos.');
        if (!filter_var($d['email'], FILTER_VALIDATE_EMAIL)) jsonError('Email inválido.');
        if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $d['usuario'])) jsonError('El nombre de usuario debe tener 3-20 caracteres (letras, números o _).');

        $check = $db->prepare("SELECT id FROM usuarios WHERE (email = ? OR usuario = ?) AND id <> ?");
        $check->execute([$d['email'], $d['usuario'], $id]);
        if ($check->fetch()) jsonError('El email o nombre de usuario ya está registrado.');

        if (!empty($d['password'])) {
            $hash = password_hash($d['password'], PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE usuarios SET nombre=?, email=?, usuario=?, password=?, rol_id=?, estado=? WHERE id=?");
            $stmt->execute([$d['nombre'], $d['email'], $d['usuario'], $hash, (int)$d['rol_id'], (int)$d['estado'], $id]);
        } else {
            $stmt = $db->prepare("UPDATE usuarios SET nombre=?, email=?, usuario=?, rol_id=?, estado=? WHERE id=?");
            $stmt->execute([$d['nombre'], $d['email'], $d['usuario'], (int)$d['rol_id'], (int)$d['estado'], $id]);
        }
        jsonSuccess(null, 'Usuario actualizado correctamente.');
        break;

    default:
        jsonError('Método no permitido.', 405);
}
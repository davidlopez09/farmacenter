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
        $stmt = $db->query("SELECT u.id, u.nombre, u.email, u.estado, r.nombre AS rol, u.fecha_creacion FROM usuarios u JOIN roles r ON r.id = u.rol_id ORDER BY u.nombre");
        jsonSuccess($stmt->fetchAll());
        break;

    case 'POST':
        $d = json_decode(file_get_contents('php://input'), true);
        if (empty($d['nombre']) || empty($d['email']) || empty($d['password'])) jsonError('Campos incompletos.');
        if (!filter_var($d['email'], FILTER_VALIDATE_EMAIL)) jsonError('Email inválido.');

        $check = $db->prepare("SELECT id FROM usuarios WHERE email = ?");
        $check->execute([$d['email']]);
        if ($check->fetch()) jsonError('El email ya está registrado.');

        $hash = password_hash($d['password'], PASSWORD_DEFAULT);
        $stmt = $db->prepare("INSERT INTO usuarios (nombre, email, password, rol_id) VALUES (?, ?, ?, ?)");
        $stmt->execute([$d['nombre'], $d['email'], $hash, (int)$d['rol_id']]);
        jsonSuccess(['id' => $db->lastInsertId()], 'Usuario creado correctamente.');
        break;

    case 'PUT':
        $d  = json_decode(file_get_contents('php://input'), true);
        $id = (int)($d['id'] ?? 0);
        if (!$id) jsonError('ID requerido.');

        if (!empty($d['password'])) {
            $hash = password_hash($d['password'], PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE usuarios SET nombre=?, email=?, password=?, rol_id=?, estado=? WHERE id=?");
            $stmt->execute([$d['nombre'], $d['email'], $hash, (int)$d['rol_id'], (int)$d['estado'], $id]);
        } else {
            $stmt = $db->prepare("UPDATE usuarios SET nombre=?, email=?, rol_id=?, estado=? WHERE id=?");
            $stmt->execute([$d['nombre'], $d['email'], (int)$d['rol_id'], (int)$d['estado'], $id]);
        }
        jsonSuccess(null, 'Usuario actualizado correctamente.');
        break;

    default:
        jsonError('Método no permitido.', 405);
}
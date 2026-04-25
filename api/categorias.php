<?php
// api/categorias.php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/response.php';

requireLogin();
header('Content-Type: application/json');

$db     = Database::connect();
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $stmt = $db->query("SELECT * FROM categorias WHERE estado = 1 ORDER BY nombre");
        jsonSuccess($stmt->fetchAll());
        break;

    case 'POST':
        requireAdmin();
        $d = json_decode(file_get_contents('php://input'), true);
        if (empty($d['nombre'])) jsonError('El nombre es requerido.');
        $stmt = $db->prepare("INSERT INTO categorias (nombre, descripcion) VALUES (?, ?)");
        $stmt->execute([$d['nombre'], $d['descripcion'] ?? null]);
        jsonSuccess(['id' => $db->lastInsertId()], 'Categoría creada.');
        break;

    case 'PUT':
        requireAdmin();
        $d  = json_decode(file_get_contents('php://input'), true);
        $id = (int)($d['id'] ?? 0);
        if (!$id || empty($d['nombre'])) jsonError('Datos incompletos.');
        $stmt = $db->prepare("UPDATE categorias SET nombre=?, descripcion=? WHERE id=?");
        $stmt->execute([$d['nombre'], $d['descripcion'] ?? null, $id]);
        jsonSuccess(null, 'Categoría actualizada.');
        break;

    case 'DELETE':
        requireAdmin();
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) jsonError('ID requerido.');
        $stmt = $db->prepare("UPDATE categorias SET estado = 0 WHERE id = ?");
        $stmt->execute([$id]);
        jsonSuccess(null, 'Categoría eliminada.');
        break;

    default:
        jsonError('Método no permitido.', 405);
}
<?php
// api/empresa.php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/response.php';

requireLogin();
header('Content-Type: application/json');

$db     = Database::connect();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = $db->query("SELECT * FROM empresa LIMIT 1");
    jsonSuccess($stmt->fetch());
} elseif ($method === 'PUT') {
    requireAdmin();
    $d = json_decode(file_get_contents('php://input'), true);
    $stmt = $db->prepare("UPDATE empresa SET nombre=?, direccion=?, telefono=?, email=?, nit=?, moneda=?, impuesto=? WHERE id=1");
    $stmt->execute([$d['nombre'], $d['direccion'], $d['telefono'], $d['email'], $d['nit'] ?? null, $d['moneda'] ?? 'COP', $d['impuesto'] ?? 0]);
    jsonSuccess(null, 'Datos de empresa actualizados.');
} else {
    jsonError('Método no permitido.', 405);
}
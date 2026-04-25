<?php
// api/compras.php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/response.php';

requireLogin();
requireAdmin();
header('Content-Type: application/json');

$db     = Database::connect();
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

if ($method === 'GET') {
    $desde  = $_GET['desde']  ?? date('Y-m-d');
    $hasta  = $_GET['hasta']  ?? date('Y-m-d');
    $pagina = max(1, (int)($_GET['pagina'] ?? 1));
    $limit  = 20;
    $offset = ($pagina - 1) * $limit;

    $cStmt = $db->prepare("SELECT COUNT(*) FROM compras WHERE DATE(fecha) BETWEEN ? AND ?");
    $cStmt->execute([$desde, $hasta]);
    $total = (int)$cStmt->fetchColumn();

    $stmt = $db->prepare("SELECT c.*, u.nombre AS cajero FROM compras c JOIN usuarios u ON u.id = c.usuario_id WHERE DATE(c.fecha) BETWEEN ? AND ? ORDER BY c.fecha DESC LIMIT ? OFFSET ?");
    $stmt->execute([$desde, $hasta, $limit, $offset]);

    jsonSuccess(['compras' => $stmt->fetchAll(), 'total' => $total, 'paginas' => ceil($total / $limit)]);
} elseif ($method === 'POST') {
    $d       = json_decode(file_get_contents('php://input'), true);
    $items   = $d['items'] ?? [];
    $usuario = currentUser();

    if (empty($items)) jsonError('Debe ingresar al menos un producto.');

    $db->beginTransaction();
    try {
        $total = 0;
        foreach ($items as $item) {
            $total += (float)$item['precio_compra'] * (int)$item['cantidad'];
        }

        $stmt = $db->prepare("INSERT INTO compras (usuario_id, total) VALUES (?, ?)");
        $stmt->execute([$usuario['id'], $total]);
        $compraId = (int)$db->lastInsertId();

        $detStmt   = $db->prepare("INSERT INTO compras_detalle (compra_id, producto_id, cantidad, precio_compra, subtotal) VALUES (?, ?, ?, ?, ?)");
        $stockStmt = $db->prepare("UPDATE productos SET stock = stock + ?, precio_compra = ? WHERE id = ?");

        foreach ($items as $item) {
            $cantidad = (int)$item['cantidad'];
            $precio   = (float)$item['precio_compra'];
            $sub      = $precio * $cantidad;
            $detStmt->execute([$compraId, $item['producto_id'], $cantidad, $precio, $sub]);
            $stockStmt->execute([$cantidad, $precio, $item['producto_id']]);
        }

        // Registrar egreso en caja
        $cajaStmt = $db->prepare("INSERT INTO caja_movimientos (tipo, referencia, descripcion, monto, usuario_id) VALUES ('EGRESO', ?, ?, ?, ?)");
        $cajaStmt->execute(["COMPRA-$compraId", "Compra de inventario #$compraId", $total, $usuario['id']]);

        $db->commit();
        jsonSuccess(['compra_id' => $compraId], 'Compra registrada correctamente.');
    } catch (Throwable $e) {
        $db->rollBack();
        jsonError('Error al registrar la compra: ' . $e->getMessage(), 500);
    }
} else {
    jsonError('Método no permitido.', 405);
}
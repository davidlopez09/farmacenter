<?php
// api/devoluciones.php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/response.php';

requireLogin();
header('Content-Type: application/json');

$db     = Database::connect();
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

if ($method === 'GET') {
    if ($action === 'listar') {
        listarDevoluciones($db);
    } elseif ($action === 'venta_detalle') {
        detalleVentaParaDevolucion($db);
    } else {
        jsonError('Acción no reconocida.');
    }
} elseif ($method === 'POST') {
    registrarDevolucion($db);
} else {
    jsonError('Método no permitido.', 405);
}

function listarDevoluciones(PDO $db): void {
    $desde = $_GET['desde'] ?? date('Y-m-d');
    $hasta = $_GET['hasta'] ?? date('Y-m-d');

    $stmt = $db->prepare("SELECT d.*, u.nombre AS cajero, v.total AS total_venta
        FROM devoluciones d
        JOIN usuarios u ON u.id = d.usuario_id
        JOIN ventas v ON v.id = d.venta_id
        WHERE DATE(d.fecha) BETWEEN ? AND ?
        ORDER BY d.fecha DESC");
    $stmt->execute([$desde, $hasta]);
    jsonSuccess($stmt->fetchAll());
}

function detalleVentaParaDevolucion(PDO $db): void {
    $ventaId = (int)($_GET['venta_id'] ?? 0);
    if (!$ventaId) jsonError('ID de venta requerido.');

    // Verificar que la venta exista
    $vStmt = $db->prepare("SELECT id, total, fecha FROM ventas WHERE id = ?");
    $vStmt->execute([$ventaId]);
    $venta = $vStmt->fetch();
    if (!$venta) jsonError('Venta no encontrada.', 404);

    // Traer detalle con cantidades ya devueltas
    $sql = "SELECT vd.producto_id, p.nombre AS producto, vd.cantidad AS cantidad_vendida,
                   vd.precio_unitario,
                   COALESCE(SUM(dd.cantidad), 0) AS cantidad_devuelta
            FROM ventas_detalle vd
            JOIN productos p ON p.id = vd.producto_id
            LEFT JOIN devoluciones dev ON dev.venta_id = vd.venta_id
            LEFT JOIN devoluciones_detalle dd ON dd.devolucion_id = dev.id AND dd.producto_id = vd.producto_id
            WHERE vd.venta_id = ?
            GROUP BY vd.producto_id, p.nombre, vd.cantidad, vd.precio_unitario";
    $stmt = $db->prepare($sql);
    $stmt->execute([$ventaId]);
    $detalle = $stmt->fetchAll();

    // Calcular cantidad disponible para devolver
    foreach ($detalle as &$item) {
        $item['cantidad_disponible'] = $item['cantidad_vendida'] - $item['cantidad_devuelta'];
    }

    jsonSuccess(['venta' => $venta, 'detalle' => $detalle]);
}

function registrarDevolucion(PDO $db): void {
    $d       = json_decode(file_get_contents('php://input'), true);
    $ventaId = (int)($d['venta_id'] ?? 0);
    $items   = $d['items']   ?? [];
    $motivo  = $d['motivo']  ?? '';
    $usuario = currentUser();

    if (!$ventaId) jsonError('ID de venta requerido.');
    if (empty($items)) jsonError('Debe seleccionar al menos un producto.');

    // Validar cantidades disponibles para devolver
    foreach ($items as $item) {
        $prodId   = (int)($item['producto_id'] ?? 0);
        $cantidad = (int)($item['cantidad']    ?? 0);
        if ($cantidad <= 0) continue; // omitir ceros

        $sql = "SELECT vd.cantidad - COALESCE(SUM(dd.cantidad), 0) AS disponible
                FROM ventas_detalle vd
                LEFT JOIN devoluciones dev ON dev.venta_id = vd.venta_id
                LEFT JOIN devoluciones_detalle dd ON dd.devolucion_id = dev.id AND dd.producto_id = vd.producto_id
                WHERE vd.venta_id = ? AND vd.producto_id = ?
                GROUP BY vd.cantidad";
        $stmt = $db->prepare($sql);
        $stmt->execute([$ventaId, $prodId]);
        $row = $stmt->fetch();

        if (!$row || $cantidad > (int)$row['disponible']) {
            jsonError("Cantidad a devolver supera lo permitido para el producto ID $prodId.");
        }
    }

    $db->beginTransaction();
    try {
        $total = 0;
        foreach ($items as $item) {
            if ((int)$item['cantidad'] > 0) {
                $total += (float)$item['precio_unitario'] * (int)$item['cantidad'];
            }
        }

        $stmt = $db->prepare("INSERT INTO devoluciones (venta_id, usuario_id, total, motivo) VALUES (?, ?, ?, ?)");
        $stmt->execute([$ventaId, $usuario['id'], $total, $motivo]);
        $devId = (int)$db->lastInsertId();

        $detStmt   = $db->prepare("INSERT INTO devoluciones_detalle (devolucion_id, producto_id, cantidad, precio_unitario, subtotal) VALUES (?, ?, ?, ?, ?)");
        $stockStmt = $db->prepare("UPDATE productos SET stock = stock + ? WHERE id = ?");

        foreach ($items as $item) {
            $cantidad = (int)$item['cantidad'];
            if ($cantidad <= 0) continue;
            $sub = (float)$item['precio_unitario'] * $cantidad;
            $detStmt->execute([$devId, $item['producto_id'], $cantidad, $item['precio_unitario'], $sub]);
            $stockStmt->execute([$cantidad, $item['producto_id']]);
        }

        // Registrar egreso en caja
        $cajaStmt = $db->prepare("INSERT INTO caja_movimientos (tipo, referencia, descripcion, monto, usuario_id) VALUES ('EGRESO', ?, ?, ?, ?)");
        $cajaStmt->execute(["DEV-$devId", "Devolución #$devId de Venta #$ventaId", $total, $usuario['id']]);

        $db->commit();
        jsonSuccess(['devolucion_id' => $devId], 'Devolución registrada correctamente.');
    } catch (Throwable $e) {
        $db->rollBack();
        jsonError('Error al procesar la devolución: ' . $e->getMessage(), 500);
    }
}
<?php
// api/ventas.php

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
        listarVentas($db);
    } elseif ($action === 'detalle') {
        detalleVenta($db);
    } else {
        jsonError('Acción no reconocida.');
    }
} elseif ($method === 'POST') {
    registrarVenta($db);
} else {
    jsonError('Método no permitido.', 405);
}

// ─── Listar ventas ─────────────────────────────────────────────────────────────
function listarVentas(PDO $db): void {
    $desde  = $_GET['desde']  ?? date('Y-m-d');
    $hasta  = $_GET['hasta']  ?? date('Y-m-d');
    $pagina = max(1, (int)($_GET['pagina'] ?? 1));
    $limit  = 20;
    $offset = ($pagina - 1) * $limit;

    $sql = "SELECT v.id, v.total, v.metodo_pago, v.fecha, u.nombre AS cajero
            FROM ventas v
            JOIN usuarios u ON u.id = v.usuario_id
            WHERE DATE(v.fecha) BETWEEN ? AND ?
            ORDER BY v.fecha DESC
            LIMIT ? OFFSET ?";

    $countSql = "SELECT COUNT(*) FROM ventas v WHERE DATE(v.fecha) BETWEEN ? AND ?";
    $cStmt = $db->prepare($countSql);
    $cStmt->execute([$desde, $hasta]);
    $total = (int)$cStmt->fetchColumn();

    $stmt = $db->prepare($sql);
    $stmt->execute([$desde, $hasta, $limit, $offset]);

    jsonSuccess([
        'ventas'  => $stmt->fetchAll(),
        'total'   => $total,
        'paginas' => ceil($total / $limit),
    ]);
}

// ─── Detalle de una venta ──────────────────────────────────────────────────────
function detalleVenta(PDO $db): void {
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) jsonError('ID requerido.');

    $stmt = $db->prepare("SELECT v.*, u.nombre AS cajero FROM ventas v JOIN usuarios u ON u.id = v.usuario_id WHERE v.id = ?");
    $stmt->execute([$id]);
    $venta = $stmt->fetch();
    if (!$venta) jsonError('Venta no encontrada.', 404);

    $dStmt = $db->prepare("SELECT vd.*, p.nombre AS producto FROM ventas_detalle vd JOIN productos p ON p.id = vd.producto_id WHERE vd.venta_id = ?");
    $dStmt->execute([$id]);
    $venta['detalle'] = $dStmt->fetchAll();

    jsonSuccess($venta);
}

// ─── Registrar venta ───────────────────────────────────────────────────────────
function registrarVenta(PDO $db): void {
    $d          = json_decode(file_get_contents('php://input'), true);
    $items      = $d['items']       ?? [];
    $metodoPago = $d['metodo_pago'] ?? 'EFECTIVO';
    $usuario    = currentUser();

    if (empty($items)) jsonError('El carrito está vacío.');

    // Validar stock antes de cualquier escritura
    foreach ($items as $item) {
        $id       = (int)($item['producto_id'] ?? 0);
        $cantidad = (int)($item['cantidad']    ?? 0);
        if ($id <= 0 || $cantidad <= 0) jsonError('Ítem inválido.');

        $stmt = $db->prepare("SELECT stock, nombre FROM productos WHERE id = ? AND estado = 1");
        $stmt->execute([$id]);
        $prod = $stmt->fetch();
        if (!$prod) jsonError("Producto ID $id no encontrado.");
        if ($prod['stock'] < $cantidad) {
            jsonError("Stock insuficiente para \"{$prod['nombre']}\". Disponible: {$prod['stock']}.");
        }
    }

    $db->beginTransaction();
    try {
        $total = 0;
        foreach ($items as $item) {
            $total += (float)$item['precio_unitario'] * (int)$item['cantidad'];
        }

        // Insertar venta
        $stmt = $db->prepare("INSERT INTO ventas (usuario_id, total, metodo_pago) VALUES (?, ?, ?)");
        $stmt->execute([$usuario['id'], $total, $metodoPago]);
        $ventaId = (int)$db->lastInsertId();

        // Insertar detalle y descontar stock
        $detStmt  = $db->prepare("INSERT INTO ventas_detalle (venta_id, producto_id, cantidad, precio_unitario, subtotal) VALUES (?, ?, ?, ?, ?)");
        $stockStmt = $db->prepare("UPDATE productos SET stock = stock - ? WHERE id = ?");

        foreach ($items as $item) {
            $sub = (float)$item['precio_unitario'] * (int)$item['cantidad'];
            $detStmt->execute([$ventaId, $item['producto_id'], $item['cantidad'], $item['precio_unitario'], $sub]);
            $stockStmt->execute([$item['cantidad'], $item['producto_id']]);
        }

        // Registrar en caja
        $cajaStmt = $db->prepare("INSERT INTO caja_movimientos (tipo, referencia, descripcion, monto, usuario_id) VALUES ('INGRESO', ?, ?, ?, ?)");
        $cajaStmt->execute(["VENTA-$ventaId", "Venta #$ventaId - $metodoPago", $total, $usuario['id']]);

        $db->commit();
        jsonSuccess(['venta_id' => $ventaId, 'total' => $total], 'Venta registrada correctamente.');
    } catch (Throwable $e) {
        $db->rollBack();
        jsonError('Error al procesar la venta: ' . $e->getMessage(), 500);
    }
}
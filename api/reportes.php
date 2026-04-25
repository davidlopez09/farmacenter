<?php
// api/reportes.php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/response.php';

requireLogin();
requireAdmin();
header('Content-Type: application/json');

$db   = Database::connect();
$tipo = $_GET['tipo']   ?? 'ventas'; // ventas | compras | devoluciones | caja
$filtro = $_GET['filtro'] ?? 'diario'; // diario | semanal | quincenal | mensual

[$desde, $hasta] = calcularRango($filtro);

// Permitir override manual
if (!empty($_GET['desde'])) $desde = $_GET['desde'];
if (!empty($_GET['hasta'])) $hasta = $_GET['hasta'];

switch ($tipo) {
    case 'ventas':
        reporteVentas($db, $desde, $hasta);
        break;
    case 'compras':
        reporteCompras($db, $desde, $hasta);
        break;
    case 'devoluciones':
        reporteDevoluciones($db, $desde, $hasta);
        break;
    case 'caja':
        reporteCaja($db, $desde, $hasta);
        break;
    default:
        jsonError('Tipo de reporte inválido.');
}

function calcularRango(string $filtro): array {
    $hoy = date('Y-m-d');
    return match($filtro) {
        'semanal'   => [date('Y-m-d', strtotime('-7 days')), $hoy],
        'quincenal' => [date('Y-m-d', strtotime('-15 days')), $hoy],
        'mensual'   => [date('Y-m-01'), $hoy],
        default     => [$hoy, $hoy], // diario
    };
}

function reporteVentas(PDO $db, string $desde, string $hasta): void {
    $stmt = $db->prepare("
        SELECT DATE(v.fecha) AS fecha,
               COUNT(v.id) AS num_ventas,
               SUM(v.total) AS total,
               v.metodo_pago
        FROM ventas v
        WHERE DATE(v.fecha) BETWEEN ? AND ?
        GROUP BY DATE(v.fecha), v.metodo_pago
        ORDER BY fecha DESC
    ");
    $stmt->execute([$desde, $hasta]);
    $resumen = $stmt->fetchAll();

    $totStmt = $db->prepare("SELECT COUNT(*) AS ventas, SUM(total) AS total FROM ventas WHERE DATE(fecha) BETWEEN ? AND ?");
    $totStmt->execute([$desde, $hasta]);
    $totales = $totStmt->fetch();

    jsonSuccess(['resumen' => $resumen, 'totales' => $totales, 'desde' => $desde, 'hasta' => $hasta]);
}

function reporteCompras(PDO $db, string $desde, string $hasta): void {
    $stmt = $db->prepare("
        SELECT DATE(c.fecha) AS fecha, COUNT(c.id) AS num_compras, SUM(c.total) AS total, u.nombre AS usuario
        FROM compras c JOIN usuarios u ON u.id = c.usuario_id
        WHERE DATE(c.fecha) BETWEEN ? AND ?
        GROUP BY DATE(c.fecha), u.nombre
        ORDER BY fecha DESC
    ");
    $stmt->execute([$desde, $hasta]);
    $resumen = $stmt->fetchAll();

    $totStmt = $db->prepare("SELECT COUNT(*) AS compras, SUM(total) AS total FROM compras WHERE DATE(fecha) BETWEEN ? AND ?");
    $totStmt->execute([$desde, $hasta]);
    jsonSuccess(['resumen' => $resumen, 'totales' => $totStmt->fetch(), 'desde' => $desde, 'hasta' => $hasta]);
}

function reporteDevoluciones(PDO $db, string $desde, string $hasta): void {
    $stmt = $db->prepare("
        SELECT DATE(d.fecha) AS fecha, COUNT(d.id) AS num_devoluciones, SUM(d.total) AS total
        FROM devoluciones d
        WHERE DATE(d.fecha) BETWEEN ? AND ?
        GROUP BY DATE(d.fecha)
        ORDER BY fecha DESC
    ");
    $stmt->execute([$desde, $hasta]);
    $resumen = $stmt->fetchAll();

    $totStmt = $db->prepare("SELECT COUNT(*) AS devoluciones, SUM(total) AS total FROM devoluciones WHERE DATE(fecha) BETWEEN ? AND ?");
    $totStmt->execute([$desde, $hasta]);
    jsonSuccess(['resumen' => $resumen, 'totales' => $totStmt->fetch(), 'desde' => $desde, 'hasta' => $hasta]);
}

function reporteCaja(PDO $db, string $desde, string $hasta): void {
    $stmt = $db->prepare("
        SELECT tipo, SUM(monto) AS total, COUNT(*) AS movimientos
        FROM caja_movimientos
        WHERE DATE(fecha) BETWEEN ? AND ?
        GROUP BY tipo
    ");
    $stmt->execute([$desde, $hasta]);
    $resumen = $stmt->fetchAll();

    $detStmt = $db->prepare("
        SELECT cm.*, u.nombre AS usuario
        FROM caja_movimientos cm JOIN usuarios u ON u.id = cm.usuario_id
        WHERE DATE(cm.fecha) BETWEEN ? AND ?
        ORDER BY cm.fecha DESC
        LIMIT 100
    ");
    $detStmt->execute([$desde, $hasta]);
    jsonSuccess(['resumen' => $resumen, 'movimientos' => $detStmt->fetchAll(), 'desde' => $desde, 'hasta' => $hasta]);
}
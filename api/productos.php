<?php
// api/productos.php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/response.php';

requireLogin();
header('Content-Type: application/json');

$db     = Database::connect();
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

switch ($method) {
    case 'GET':
        handleGet($db, $action);
        break;
    case 'POST':
        requireAdmin();
        handlePost($db);
        break;
    case 'PUT':
        requireAdmin();
        handlePut($db);
        break;
    case 'DELETE':
        requireAdmin();
        handleDelete($db);
        break;
    default:
        jsonError('Método no permitido.', 405);
}

// ─── GET ───────────────────────────────────────────────────────────────────────
function handleGet(PDO $db, string $action): void {
    if ($action === 'buscar') {
        $q    = trim($_GET['q'] ?? '');
        $tipo = $_GET['tipo'] ?? 'nombre'; // nombre | codigo

        if (empty($q)) jsonError('Parámetro de búsqueda requerido.');

        if ($tipo === 'codigo') {
            $sql  = "SELECT p.*, c.nombre AS categoria
                     FROM productos p
                     LEFT JOIN categorias c ON c.id = p.categoria_id
                     WHERE p.codigo_barras = ? AND p.estado = 1
                     LIMIT 1";
            $stmt = $db->prepare($sql);
            $stmt->execute([$q]);
        } else {
            $sql  = "SELECT p.*, c.nombre AS categoria
                     FROM productos p
                     LEFT JOIN categorias c ON c.id = p.categoria_id
                     WHERE p.nombre LIKE ? AND p.estado = 1
                     ORDER BY p.nombre
                     LIMIT 20";
            $stmt = $db->prepare($sql);
            $stmt->execute(["%$q%"]);
        }
        jsonSuccess($stmt->fetchAll());
    }

    if ($action === 'uno') {
        $id   = (int)($_GET['id'] ?? 0);
        $stmt = $db->prepare("SELECT p.*, c.nombre AS categoria FROM productos p LEFT JOIN categorias c ON c.id = p.categoria_id WHERE p.id = ?");
        $stmt->execute([$id]);
        $p = $stmt->fetch();
        if (!$p) jsonError('Producto no encontrado.', 404);
        jsonSuccess($p);
    }

    // Listar todos con filtros opcionales
    $pagina  = max(1, (int)($_GET['pagina'] ?? 1));
    $limit   = 20;
    $offset  = ($pagina - 1) * $limit;
    $search  = trim($_GET['search'] ?? '');
    $catId   = (int)($_GET['categoria'] ?? 0);

    $where   = ['p.estado = 1'];
    $params  = [];

    if ($search !== '') {
        $where[]  = '(p.nombre LIKE ? OR p.codigo_barras LIKE ?)';
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    if ($catId > 0) {
        $where[]  = 'p.categoria_id = ?';
        $params[] = $catId;
    }

    $whereStr = implode(' AND ', $where);

    $countStmt = $db->prepare("SELECT COUNT(*) FROM productos p WHERE $whereStr");
    $countStmt->execute($params);
    $total = (int)$countStmt->fetchColumn();

    $params[] = $limit;
    $params[] = $offset;
    $stmt     = $db->prepare("SELECT p.*, c.nombre AS categoria FROM productos p LEFT JOIN categorias c ON c.id = p.categoria_id WHERE $whereStr ORDER BY p.nombre LIMIT ? OFFSET ?");
    $stmt->execute($params);

    jsonSuccess([
        'productos' => $stmt->fetchAll(),
        'total'     => $total,
        'paginas'   => ceil($total / $limit),
        'pagina'    => $pagina,
    ]);
}

// ─── POST ──────────────────────────────────────────────────────────────────────
function handlePost(PDO $db): void {
    $d = json_decode(file_get_contents('php://input'), true);
    validarProducto($d);

    $sql  = "INSERT INTO productos (codigo_barras, nombre, descripcion, categoria_id, precio_compra, precio_venta, stock, stock_minimo)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $db->prepare($sql);
    $stmt->execute([
        $d['codigo_barras'] ?: null,
        $d['nombre'],
        $d['descripcion'] ?? null,
        $d['categoria_id'] ?: null,
        $d['precio_compra'],
        $d['precio_venta'],
        (int)($d['stock'] ?? 0),
        (int)($d['stock_minimo'] ?? 0),
    ]);

    jsonSuccess(['id' => $db->lastInsertId()], 'Producto creado correctamente.');
}

// ─── PUT ───────────────────────────────────────────────────────────────────────
function handlePut(PDO $db): void {
    $d  = json_decode(file_get_contents('php://input'), true);
    $id = (int)($d['id'] ?? 0);
    if (!$id) jsonError('ID requerido.');
    validarProducto($d);

    $sql  = "UPDATE productos SET codigo_barras=?, nombre=?, descripcion=?, categoria_id=?,
             precio_compra=?, precio_venta=?, stock=?, stock_minimo=? WHERE id=?";
    $stmt = $db->prepare($sql);
    $stmt->execute([
        $d['codigo_barras'] ?: null,
        $d['nombre'],
        $d['descripcion'] ?? null,
        $d['categoria_id'] ?: null,
        $d['precio_compra'],
        $d['precio_venta'],
        (int)$d['stock'],
        (int)$d['stock_minimo'],
        $id,
    ]);

    jsonSuccess(null, 'Producto actualizado correctamente.');
}

// ─── DELETE (soft) ─────────────────────────────────────────────────────────────
function handleDelete(PDO $db): void {
    $id   = (int)($_GET['id'] ?? 0);
    if (!$id) jsonError('ID requerido.');

    $stmt = $db->prepare("UPDATE productos SET estado = 0 WHERE id = ?");
    $stmt->execute([$id]);
    jsonSuccess(null, 'Producto eliminado correctamente.');
}

// ─── Validación ────────────────────────────────────────────────────────────────
function validarProducto(array $d): void {
    if (empty($d['nombre']))        jsonError('El nombre es requerido.');
    if (!isset($d['precio_compra']) || $d['precio_compra'] < 0) jsonError('Precio de compra inválido.');
    if (!isset($d['precio_venta'])  || $d['precio_venta'] < 0)  jsonError('Precio de venta inválido.');
}
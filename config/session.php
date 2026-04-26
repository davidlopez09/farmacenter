<?php
// config/session.php

date_default_timezone_set('America/Bogota');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function requireLogin(): void {
    if (empty($_SESSION['usuario_id'])) {
        header('Location: /index.php');
        exit;
    }
}

function requireAdmin(): void {
    requireLogin();
    if ($_SESSION['rol'] !== 'ADMIN') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Acceso denegado.']);
        exit;
    }
}

function isAdmin(): bool {
    return isset($_SESSION['rol']) && $_SESSION['rol'] === 'ADMIN';
}

function currentUser(): array {
    return [
        'id'     => $_SESSION['usuario_id'] ?? null,
        'nombre' => $_SESSION['nombre']     ?? '',
        'rol'    => $_SESSION['rol']        ?? '',
    ];
}
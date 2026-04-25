<?php
// config/response.php

function jsonResponse(bool $success, string $message = '', mixed $data = null, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json');
    $payload = ['success' => $success, 'message' => $message];
    if ($data !== null) $payload['data'] = $data;
    echo json_encode($payload);
    exit;
}

function jsonSuccess(mixed $data = null, string $message = 'OK'): void {
    jsonResponse(true, $message, $data);
}

function jsonError(string $message, int $code = 400): void {
    jsonResponse(false, $message, null, $code);
}
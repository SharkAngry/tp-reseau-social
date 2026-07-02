<?php
require __DIR__ . '/../config/db.php';

$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? '';

if (empty($authHeader) || !str_starts_with($authHeader, 'Bearer ')) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Token manquant']);
    exit;
}

$token = substr($authHeader, 7);

$stmt = $pdo->prepare('SELECT id, nom, role FROM admins WHERE session_token = ?');
$stmt->execute([$token]);
$currentAdmin = $stmt->fetch();

if (!$currentAdmin) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Session admin invalide']);
    exit;
}
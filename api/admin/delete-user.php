<?php
header('Content-Type: application/json');
require_once '../includes/admin-check.php';

$data = json_decode(file_get_contents('php://input'), true);
$userId = intval($data['id'] ?? 0);

if (!$userId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'ID manquant']);
    exit;
}

try {
    $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$userId]);
    echo json_encode(['success' => true, 'message' => 'Utilisateur supprimé']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Erreur serveur']);
}
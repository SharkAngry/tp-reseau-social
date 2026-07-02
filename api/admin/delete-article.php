<?php
header('Content-Type: application/json');
require_once '../includes/admin-check.php';

$data = json_decode(file_get_contents('php://input'), true);
$articleId = intval($data['id'] ?? 0);

if (!$articleId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'ID manquant']);
    exit;
}

try {
    $pdo->prepare("DELETE FROM articles WHERE id = ?")->execute([$articleId]);
    echo json_encode(['success' => true, 'message' => 'Article supprimé']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Erreur serveur']);
}
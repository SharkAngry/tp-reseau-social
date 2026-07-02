<?php
header('Content-Type: application/json');
require_once '../includes/admin-check.php';

try {
    $stmt = $pdo->query("SELECT a.id, a.description, a.image, a.created_at, u.nom, u.prenom
                          FROM articles a JOIN users u ON a.user_id = u.id
                          ORDER BY a.created_at DESC");
    echo json_encode(['success' => true, 'articles' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Erreur serveur']);
}
<?php
header('Content-Type: application/json');
require_once '../includes/admin-check.php';

try {
    $stmt = $pdo->query("SELECT id, nom, prenom, email, photo_profil, created_at FROM users ORDER BY created_at DESC");
    echo json_encode(['success' => true, 'users' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
} catch (PDOException $e) {
    http_response_code(500);
    error_log($e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Erreur serveur']);
}
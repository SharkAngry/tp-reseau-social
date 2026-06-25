<?php
require_once '../config/db.php';
require_once '../includes/session-check.php';

header("Content-Type: application/json");
$current_id = $currentUser['id'];

try {
    $sql = "SELECT u.id, u.nom, u.prenom, u.photo_profil AS avatar FROM friendships f
            JOIN users u ON f.sender_id = u.id
            WHERE f.receiver_id = :current_id AND f.status = 'pending'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':current_id' => $current_id]);
    echo json_encode(["success" => true, "invitations" => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
} catch (PDOException $e) {
    http_response_code(500);
    error_log('Erreur get-invitations: ' . $e->getMessage());
    echo json_encode(["success" => false, "error" => "Erreur serveur."]);
}
<?php
require_once '../config/db.php';
require_once '../includes/session-check.php';

header("Content-Type: application/json");
$current_id = $currentUser['id'];
$query_search = isset($_GET['query']) ? trim($_GET['query']) : '';

try {
    $base = "SELECT id, nom, prenom, photo_profil AS avatar FROM users
              WHERE id != :current_id
              AND id NOT IN (
                  SELECT receiver_id FROM friendships WHERE sender_id = :current_id
                  UNION
                  SELECT sender_id FROM friendships WHERE receiver_id = :current_id
              )";
    $params = [':current_id' => $current_id];

    if (!empty($query_search)) {
        $sql = $base . " AND (nom LIKE :q OR prenom LIKE :q) LIMIT 15";
        $params[':q'] = "%$query_search%";
    } else {
        $sql = $base . " ORDER BY id DESC LIMIT 10";
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    echo json_encode(["success" => true, "users" => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
} catch (PDOException $e) {
    http_response_code(500);
    error_log('Erreur search: ' . $e->getMessage());
    echo json_encode(["success" => false, "error" => "Erreur serveur."]);
}
<?php
// api/chat/get_messages.php

header("Content-Type: application/json");

require_once '../config/db.php';
require_once '../includes/session-check.php';

/** @var array $currentUser — injecté par session-check.php */
/** @var \PDO  $pdo         — injecté par db.php */
$sender_id   = $currentUser['id'];
$receiver_id = isset($_GET['receiver_id']) ? intval($_GET['receiver_id']) : null;

if (!$receiver_id) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Identifiant destinataire manquant"]);
    exit;
}

try {
    $query = $pdo->prepare("
        SELECT id, sender_id, receiver_id, contenu, image, created_at
        FROM messages
        WHERE (sender_id = ? AND receiver_id = ?)
           OR (sender_id = ? AND receiver_id = ?)
        ORDER BY created_at ASC
    ");
    $query->execute([$sender_id, $receiver_id, $receiver_id, $sender_id]);

    echo json_encode([
        "status"   => "success",
        "messages" => $query->fetchAll(PDO::FETCH_ASSOC)
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Erreur lors de la récupération des messages"]);
}
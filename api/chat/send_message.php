<?php
// api/chat/send_message.php

header("Content-Type: application/json");

require_once '../config/db.php';
require_once '../includes/session-check.php';

/** @var array $currentUser — injecté par session-check.php */
/** @var \PDO  $pdo         — injecté par db.php */
$sender_id   = $currentUser['id'];
$receiver_id = isset($_POST['receiver_id']) ? intval($_POST['receiver_id']) : null;
$contenu     = trim($_POST['contenu'] ?? '');
$image_name  = null;

if (!$receiver_id) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Destinataire manquant"]);
    exit;
}

// Gestion de l'image (optionnelle)
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $fileType = mime_content_type($_FILES['image']['tmp_name']);

    if (!in_array($fileType, $allowedTypes)) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Format d'image non autorisé"]);
        exit;
    }

    if ($_FILES['image']['size'] > 5 * 1024 * 1024) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Image trop volumineuse (max 5 Mo)"]);
        exit;
    }

    // Chemin corrigé : depuis api/chat/ on remonte 2 niveaux pour atteindre la racine
    $target_dir = __DIR__ . '/../../assets/images/uploads/';
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0755, true);
    }

    $extension  = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
    $image_name = 'msg_' . $sender_id . '_' . time() . '.' . $extension;
    move_uploaded_file($_FILES['image']['tmp_name'], $target_dir . $image_name);
}

if (empty($contenu) && !$image_name) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Le message ne peut pas être vide"]);
    exit;
}

try {
    $query = $pdo->prepare(
        "INSERT INTO messages (sender_id, receiver_id, contenu, image) VALUES (?, ?, ?, ?)"
    );
    $query->execute([$sender_id, $receiver_id, $contenu ?: null, $image_name]);

    echo json_encode(["status" => "success", "message" => "Message envoyé avec succès"]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Impossible d'enregistrer le message"]);
}
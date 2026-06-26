<?php
header('Content-Type: application/json; charset=UTF-8');
require '../config/db.php';
require '../includes/session-check.php';

$current_user_id = $currentUser['id'];
$description = trim($_POST['description'] ?? '');

if (empty($description)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Le texte de la publication ne peut pas être vide.']);
    exit;
}

$image_name = null;

if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    $fileType = mime_content_type($_FILES['image']['tmp_name']);

    if (!in_array($fileType, $allowedTypes)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => "Format d'image non supporté (JPG, PNG, GIF)."]);
        exit;
    }
    if ($_FILES['image']['size'] > 5 * 1024 * 1024) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Image trop volumineuse (max 5 Mo).']);
        exit;
    }
// Génération d'un nom unique pour l'image
    $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
    $image_name = 'post_' . $current_user_id . '_' . time() . '.' . $extension;
    $uploadDir = '../../assets/images/posts/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
    move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $image_name);
}

try {
    $stmt = $pdo->prepare("INSERT INTO articles (user_id, description, image) VALUES (?, ?, ?)");
    $stmt->execute([$current_user_id, $description, $image_name]);
    http_response_code(201);
    echo json_encode(['success' => true, 'message' => 'Publication partagée avec succès !']);
} catch (PDOException $e) {
    http_response_code(500);
    error_log('Erreur create-article: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => "Erreur lors de l'enregistrement."]);
}
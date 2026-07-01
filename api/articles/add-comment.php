<?php
header('Content-Type: application/json; charset=UTF-8');
require '../config/db.php';
require '../includes/session-check.php';
// Vérification de l'authentification
$current_user_id = $currentUser['id'];
$data = json_decode(file_get_contents('php://input'), true);
$article_id = isset($data['article_id']) ? intval($data['article_id']) : null;
$contenu = trim($data['contenu'] ?? '');

if (!$article_id || empty($contenu)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Données incomplètes.']);
    exit;
}
// Vérification de l'existence de l'article
try {
    $stmt = $pdo->prepare("INSERT INTO comments (article_id, user_id, contenu) VALUES (?, ?, ?)");
    $stmt->execute([$article_id, $current_user_id, $contenu]);
    $comment_id = $pdo->lastInsertId();

    $select = $pdo->prepare("SELECT c.id, c.contenu, c.created_at, u.nom, u.prenom, u.photo_profil
                              FROM comments c JOIN users u ON c.user_id = u.id WHERE c.id = ?");
    $select->execute([$comment_id]);

    http_response_code(201);
    echo json_encode(['success' => true, 'message' => 'Commentaire ajouté !', 'comment' => $select->fetch(PDO::FETCH_ASSOC)]);
} catch (PDOException $e) {
    http_response_code(500);
    error_log('Erreur add-comment: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => "Erreur lors de l'ajout."]);
}
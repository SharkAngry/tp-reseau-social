<?php
header('Content-Type: application/json; charset=UTF-8');
require '../config/db.php';
require '../includes/session-check.php';
// Vérification de l'authentification
$current_user_id = $currentUser['id'];
$data = json_decode(file_get_contents('php://input'), true);
$article_id = isset($data['article_id']) ? intval($data['article_id']) : null;
$type = $data['type'] ?? 'like';

if (!$article_id || !in_array($type, ['like', 'dislike'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Données invalides.']);
    exit;
}
// verification des articles existants
try {
    $stmt = $pdo->prepare("SELECT type FROM reactions WHERE user_id = ? AND article_id = ?");
    $stmt->execute([$current_user_id, $article_id]);
    $existing = $stmt->fetch();

    if ($existing) {
        if ($existing['type'] === $type) {
            $pdo->prepare("DELETE FROM reactions WHERE user_id = ? AND article_id = ?")
                ->execute([$current_user_id, $article_id]);
            $action = 'removed';
        } else {
            $pdo->prepare("UPDATE reactions SET type = ? WHERE user_id = ? AND article_id = ?")
                ->execute([$type, $current_user_id, $article_id]);
            $action = 'updated';
        }
    } else {
        $pdo->prepare("INSERT INTO reactions (user_id, article_id, type) VALUES (?, ?, ?)")
            ->execute([$current_user_id, $article_id, $type]);
        $action = 'added';
    }

    $likeStmt = $pdo->prepare("SELECT COUNT(*) FROM reactions WHERE article_id = ? AND type = 'like'");
    $likeStmt->execute([$article_id]);
    $dislikeStmt = $pdo->prepare("SELECT COUNT(*) FROM reactions WHERE article_id = ? AND type = 'dislike'");
    $dislikeStmt->execute([$article_id]);

    echo json_encode([
        'success' => true,
        'action' => $action,
        'type' => $type,
        'likes_count' => intval($likeStmt->fetchColumn()),
        'dislikes_count' => intval($dislikeStmt->fetchColumn())
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    error_log('Erreur like-article: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erreur serveur.']);
}
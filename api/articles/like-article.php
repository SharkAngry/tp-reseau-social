<?php
// api/articles/like-article.php

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods: POST');
require '../config/db.php';
// Récupération de l'ID utilisateur connecté via les en-têtes HTTP
$headers = getallheaders();
$current_user_id = isset($headers['X-User-Id']) ? intval($headers['X-User-Id']) : null;

if (!$current_user_id) {
    $current_user_id = 1; // Sécurité locale pour tes tests
}

// Récupérer le corps de la requête JSON
$data = json_decode(file_get_contents('php://input'), true);
$article_id = isset($data['article_id']) ? intval($data['article_id']) : null;

if (!$article_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID de l\'article manquant.']);
    exit;
}

try {
    // 1. Vérifier si une réaction existe déjà pour ce user sur cet article
    $stmt = $pdo->prepare("SELECT type FROM reactions WHERE user_id = ? AND article_id = ?");
    $stmt->execute([$current_user_id, $article_id]);
    $existing_reaction = $stmt->fetch();

    if ($existing_reaction) {
        if ($existing_reaction['type'] === 'like') {
            // Si c'est déjà un like, on le supprime (Bascule off)
            $deleteStmt = $pdo->prepare("DELETE FROM reactions WHERE user_id = ? AND article_id = ?");
            $deleteStmt->execute([$current_user_id, $article_id]);
            $action = 'removed';
        } else {
            // Si c'était un dislike, on le transforme en like
            $updateStmt = $pdo->prepare("UPDATE reactions SET type = 'like' WHERE user_id = ? AND article_id = ?");
            $updateStmt->execute([$current_user_id, $article_id]);
            $action = 'updated';
        }
    } else {
        // Aucune réaction, on insère un nouveau 'like'
        $insertStmt = $pdo->prepare("INSERT INTO reactions (user_id, article_id, type) VALUES (?, ?, 'like')");
        $insertStmt->execute([$current_user_id, $article_id]);
        $action = 'added';
    }

    // 2. Compter le nombre total de likes mis à jour pour cet article
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM reactions WHERE article_id = ? AND type = 'like'");
    $countStmt->execute([$article_id]);
    $likes_count = $countStmt->fetchColumn();

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'action' => $action,
        'likes_count' => intval($likes_count)
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode ([
        'success' => false,
        'message' => 'Erreur lors du traitement du like : ' . $e->getMessage()
    ]);
}
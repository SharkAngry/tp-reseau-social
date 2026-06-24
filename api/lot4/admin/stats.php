<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

// On remonte de deux niveaux pour atteindre le fichier de configuration centralisé
require_once '../config/db.php';

try {
    // 1. Compter le nombre total d'utilisateurs inscrits
    // Remplace $bdd par $pdo si nécessaire selon le choix de tes camarades
    $queryUsers = $bdd->query("SELECT COUNT(*) as total FROM users");
    $totalUsers = $queryUsers->fetch(PDO::FETCH_ASSOC)['total'];

    // 2. Compter le nombre total d'articles publiés
    $queryArticles = $bdd->query("SELECT COUNT(*) as total FROM articles");
    $totalArticles = $queryArticles->fetch(PDO::FETCH_ASSOC)['total'];

    // 3. Compter le nombre total de messages échangés
    $queryMessages = $bdd->query("SELECT COUNT(*) as total FROM messages");
    $totalMessages = $queryMessages->fetch(PDO::FETCH_ASSOC)['total'];

    // Renvoi des données en JSON
    echo json_encode([
        "status" => "success",
        "stats" => [
            "users" => $totalUsers,
            "articles" => $totalArticles,
            "messages" => $totalMessages
        ]
    ]);

} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => "Impossible de charger les statistiques : " . $e->getMessage()
    ]);
}
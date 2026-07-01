<?php
// Ce fichier recherche les utilisateurs disponibles pour ajouter en amis
// (utilisateurs avec lesquels il n'y a pas déjà de demande ou d'amitié)

require_once '../config/db.php';
require_once '../includes/session-check.php';

// On répond en JSON
header("Content-Type: application/json");
// ID de l'utilisateur actuellement connecté
$current_id = $currentUser['id'];
// Récupérer le paramètre de recherche s'il existe (ex: ?query=Jean)
$query_search = isset($_GET['query']) ? trim($_GET['query']) : '';

try {
    // Requête de base : récupérer tous les utilisateurs sauf :
    // - L'utilisateur lui-même
    // - Les utilisateurs avec lesquels il a déjà une demande d'ami ou une amitié
    $base = "SELECT id, nom, prenom, photo_profil AS avatar FROM users
              WHERE id != :current_id
              AND id NOT IN (
                  -- Exclure les utilisateurs pour lesquels UNE DEMANDE A ÉTÉ ENVOYÉE
                  SELECT receiver_id FROM friendships WHERE sender_id = :current_id
                  UNION
                  -- Exclure les utilisateurs AVEC LESQUELS IL Y A UNE DEMANDE OU UNE AMITIÉ
                  SELECT sender_id FROM friendships WHERE receiver_id = :current_id
              )";
    $params = [':current_id' => $current_id];

    // Si une recherche est fournie, filtrer par nom ou prénom
    if (!empty($query_search)) {
        $sql = $base . " AND (nom LIKE :q OR prenom LIKE :q) LIMIT 15";
        $params[':q'] = "%$query_search%"; // %q% = cherche q n'importe où dans le texte
    } else {
        // Sinon, afficher les utilisateurs récents (derniers 10)
        $sql = $base . " ORDER BY id DESC LIMIT 10";
    }

    $stmt = $pdo->prepare($sql);
    // Exécuter la requête avec les paramètres
    $stmt->execute($params);
    // Retourner les utilisateurs trouvés en JSON
    echo json_encode(["success" => true, "users" => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
} catch (PDOException $e) {
    http_response_code(500);
    error_log('Erreur search: ' . $e->getMessage());
    echo json_encode(["success" => false, "error" => "Erreur serveur."]);
}
<?php
// Ce fichier récupère la liste de tous les amis acceptés de l'utilisateur connecté

require_once '../config/db.php';
require_once '../includes/session-check.php';

// On répond en JSON
header("Content-Type: application/json");
// ID de l'utilisateur actuellement connecté
$current_id = $currentUser['id'];

try {
    // Récupérer tous les amis de l'utilisateur (status = 'accepted')
    // On cherche les amis qu'on ait envoyé la demande OU qu'on ait reçu la demande
    $sql = "SELECT u.id, u.nom, u.prenom, u.photo_profil AS avatar FROM friendships f
            JOIN users u ON (u.id = f.sender_id OR u.id = f.receiver_id)
            WHERE (f.sender_id = :current_id OR f.receiver_id = :current_id)
            AND f.status = 'accepted' AND u.id != :current_id";
    $stmt = $pdo->prepare($sql);
    // Exécuter la requête avec l'ID de l'utilisateur connecté
    $stmt->execute([':current_id' => $current_id]);
    // Retourner la liste des amis en JSON
    echo json_encode(["success" => true, "friends" => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
} catch (PDOException $e) {
    http_response_code(500);
    error_log('Erreur get-friends: ' . $e->getMessage());
    echo json_encode(["success" => false, "error" => "Erreur serveur."]);
}
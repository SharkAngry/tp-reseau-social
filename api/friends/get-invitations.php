<?php
// Ce fichier récupère toutes les demandes d'amis en attente de réponse (invitations reçues)

require_once '../config/db.php';
require_once '../includes/session-check.php';

// On répond en JSON
header("Content-Type: application/json");
// ID de l'utilisateur actuellement connecté
$current_id = $currentUser['id'];

try {
    // Récupérer les demandes d'amis en attente (status = 'pending')
    // On cherche seulement les demandes ÒÙ l'utilisateur est le RECEIVER (destinataire)
    $sql = "SELECT u.id, u.nom, u.prenom, u.photo_profil AS avatar FROM friendships f
            JOIN users u ON f.sender_id = u.id
            WHERE f.receiver_id = :current_id AND f.status = 'pending'";
    $stmt = $pdo->prepare($sql);
    // Exécuter la requête avec l'ID de l'utilisateur connecté
    $stmt->execute([':current_id' => $current_id]);
    // Retourner les invitations en attente en JSON
    echo json_encode(["success" => true, "invitations" => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
} catch (PDOException $e) {
    http_response_code(500);
    error_log('Erreur get-invitations: ' . $e->getMessage());
    echo json_encode(["success" => false, "error" => "Erreur serveur."]);
}
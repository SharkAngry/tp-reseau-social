<?php
// Ce fichier gère les actions sur les amis : envoyer demande, accepter, refuser ou supprimer un ami

require_once '../config/db.php';
require_once '../includes/session-check.php';

// On répond en JSON (format Web standard)
header("Content-Type: application/json");
// On récupère les données envoyées par le client et on les convertit en objet PHP
$data = json_decode(file_get_contents("php://input"));
// ID de l'utilisateur actuellement connecté
$current_id = $currentUser['id'];

// Vérifier que les paramètres action et target_id sont fournis
if (empty($data->action) || empty($data->target_id)) {
    // Erreur 400 = requête invalide
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "Requête incomplète."]);
    exit;
}

// Récupérer l'action (send, accept, decline, remove) et l'ID de la personne cible
$action = $data->action;
$target_id = intval($data->target_id);

// Empêcher l'utilisateur d'ajouter sa propre personne en ami
if ($target_id === $current_id) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "Action impossible sur soi-même."]);
    exit;
}

try {
    // ACTION : ENVOYER UNE DEMANDE D'AMI
    if ($action === 'send') {
        // Vérifier s'il y a déjà une relation entre ces deux personnes
        $check = $pdo->prepare("SELECT sender_id, status FROM friendships
                                 WHERE (sender_id = :current AND receiver_id = :target)
                                    OR (sender_id = :target AND receiver_id = :current)");
        $check->execute([':current' => $current_id, ':target' => $target_id]);
        $existing = $check->fetch();

        // Si déjà amis, on retourne un message positif
        if ($existing && $existing['status'] === 'accepted') {
            echo json_encode(["success" => true, "message" => "Vous êtes déjà amis."]);
            exit;
        }

        // Si l'autre personne avait déjà envoyé une demande, on l'accepte directement
        if ($existing && (int)$existing['sender_id'] === $target_id) {
            // Mettre à jour le statut à 'accepted' au lieu de créer un doublon
            $stmt = $pdo->prepare("UPDATE friendships SET status = 'accepted' WHERE sender_id = :target AND receiver_id = :current");
            $stmt->execute([':target' => $target_id, ':current' => $current_id]);
        } else {
            // Sinon, créer une nouvelle demande en attente (pending)
            $stmt = $pdo->prepare("INSERT INTO friendships (sender_id, receiver_id, status) VALUES (:current, :target, 'pending')
                                    ON DUPLICATE KEY UPDATE status = 'pending'");
            $stmt->execute([':current' => $current_id, ':target' => $target_id]);
        }

    // ACTION : ACCEPTER UNE DEMANDE D'AMI
    } elseif ($action === 'accept') {
        // Mettre le statut à 'accepted' pour la demande reçue
        $stmt = $pdo->prepare("UPDATE friendships SET status = 'accepted' WHERE sender_id = :target AND receiver_id = :current AND status = 'pending'");
        $stmt->execute([':target' => $target_id, ':current' => $current_id]);

    // ACTION : REFUSER UNE DEMANDE D'AMI
    } elseif ($action === 'decline') {
        // Supprimer la demande en attente (refuser = supprimer)
        $stmt = $pdo->prepare("DELETE FROM friendships WHERE sender_id = :target AND receiver_id = :current AND status = 'pending'");
        $stmt->execute([':target' => $target_id, ':current' => $current_id]);

    // ACTION : SUPPRIMER UN AMI
    } elseif ($action === 'remove') {
        // Supprimer l'amitié acceptée (fonctionne dans les deux sens de la relation)
        $stmt = $pdo->prepare("DELETE FROM friendships WHERE status = 'accepted'
                                AND ((sender_id = :current AND receiver_id = :target) OR (sender_id = :target AND receiver_id = :current))");
        $stmt->execute([':current' => $current_id, ':target' => $target_id]);

    // Gérer les actions inconnues
    } else {
        http_response_code(400);
        echo json_encode(["success" => false, "error" => "Action inconnue."]);
        exit;
    }

    // Vérifier si la demande d'ami a été trouvée et modifiée
    if ($stmt->rowCount() === 0 && in_array($action, ['accept', 'decline'])) {
        http_response_code(409);
        echo json_encode(["success" => false, "error" => "Demande introuvable ou déjà traitée."]);
        exit;
    }

    // L'action s'est déroulée avec succès
    echo json_encode(["success" => true, "message" => "Action validée."]);

// Gérer les erreurs de base de données
} catch (PDOException $e) {
    http_response_code(500); // Erreur 500 = erreur serveur
    error_log('Erreur friendships: ' . $e->getMessage());
    echo json_encode(["success" => false, "error" => "Erreur serveur."]);
}
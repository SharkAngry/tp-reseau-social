<?php
require_once '../config/db.php';
require_once '../includes/session-check.php';

header("Content-Type: application/json");
$data = json_decode(file_get_contents("php://input"));
$current_id = $currentUser['id'];

if (empty($data->action) || empty($data->target_id)) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "Requête incomplète."]);
    exit;
}

$action = $data->action;
$target_id = intval($data->target_id);

if ($target_id === $current_id) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "Action impossible sur soi-même."]);
    exit;
}

try {
    if ($action === 'send') {
        // Existe-t-il déjà une relation entre ces deux personnes (dans un sens ou l'autre) ?
        $check = $pdo->prepare("SELECT sender_id, status FROM friendships
                                 WHERE (sender_id = :current AND receiver_id = :target)
                                    OR (sender_id = :target AND receiver_id = :current)");
        $check->execute([':current' => $current_id, ':target' => $target_id]);
        $existing = $check->fetch();

        if ($existing && $existing['status'] === 'accepted') {
            echo json_encode(["success" => true, "message" => "Vous êtes déjà amis."]);
            exit;
        }

        if ($existing && (int)$existing['sender_id'] === $target_id) {
            // L'autre m'avait déjà invité -> on accepte directement, pas de doublon
            $stmt = $pdo->prepare("UPDATE friendships SET status = 'accepted' WHERE sender_id = :target AND receiver_id = :current");
            $stmt->execute([':target' => $target_id, ':current' => $current_id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO friendships (sender_id, receiver_id, status) VALUES (:current, :target, 'pending')
                                    ON DUPLICATE KEY UPDATE status = 'pending'");
            $stmt->execute([':current' => $current_id, ':target' => $target_id]);
        }

    } elseif ($action === 'accept') {
        $stmt = $pdo->prepare("UPDATE friendships SET status = 'accepted' WHERE sender_id = :target AND receiver_id = :current AND status = 'pending'");
        $stmt->execute([':target' => $target_id, ':current' => $current_id]);

    } elseif ($action === 'decline') {
        $stmt = $pdo->prepare("DELETE FROM friendships WHERE sender_id = :target AND receiver_id = :current AND status = 'pending'");
        $stmt->execute([':target' => $target_id, ':current' => $current_id]);

    } elseif ($action === 'remove') {
        $stmt = $pdo->prepare("DELETE FROM friendships WHERE status = 'accepted'
                                AND ((sender_id = :current AND receiver_id = :target) OR (sender_id = :target AND receiver_id = :current))");
        $stmt->execute([':current' => $current_id, ':target' => $target_id]);

    } else {
        http_response_code(400);
        echo json_encode(["success" => false, "error" => "Action inconnue."]);
        exit;
    }

    if ($stmt->rowCount() === 0 && in_array($action, ['accept', 'decline'])) {
        http_response_code(409);
        echo json_encode(["success" => false, "error" => "Demande introuvable ou déjà traitée."]);
        exit;
    }

    echo json_encode(["success" => true, "message" => "Action validée."]);

} catch (PDOException $e) {
    http_response_code(500);
    error_log('Erreur friendships: ' . $e->getMessage());
    echo json_encode(["success" => false, "error" => "Erreur serveur."]);
}
<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

require_once '../../config/db.php';

// Le JS devra envoyer l'ID de l'utilisateur connecté et celui de l'ami avec qui il discute
$sender_id = $_GET['sender_id'] ?? null;
$receiver_id = $_GET['receiver_id'] ?? null;

if (!$sender_id || !$receiver_id) {
    echo json_encode(["status" => "error", "message" => "Identifiants manquants"]);
    exit();
}

try {
    // On récupère tous les messages échangés entre ces deux utilisateurs, triés par date
    // Remplace $bdd par $pdo si c'est le nom choisi dans config/db.php
    $query = $pdo->prepare("
        SELECT * FROM messages 
        WHERE (sender_id = ? AND receiver_id = ?) 
           OR (sender_id = ? AND receiver_id = ?) 
        ORDER BY created_at ASC
    ");
    $query->execute([$sender_id, $receiver_id, $receiver_id, $sender_id]);
    $messages = $query->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "status" => "success",
        "messages" => $messages
    ]);

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Erreur lors de la récupération des messages"]);
}
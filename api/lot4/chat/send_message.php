<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

// 1. CORRECTION DU CHEMIN : Inclusion de la connexion de tes camarades
require_once '../../config/db.php'; 

// Validation des données requises
$sender_id = $_POST['sender_id'] ?? null; 
$receiver_id = $_POST['receiver_id'] ?? null;
$contenu = $_POST['contenu'] ?? null;
$image_name = null;

if (!$sender_id || !$receiver_id) {
    echo json_encode(["status" => "error", "message" => "Données manquantes"]);
    exit();
}

// Gestion de l'envoi de l'image (si présente)
if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
    // 2. CORRECTION DU CHEMIN : On remonte de 3 niveaux pour atteindre la racine puis assets/
    $target_dir = "../../../assets/images/uploads/";
    
    // Créer le dossier s'il n'existe pas
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
    $image_name = uniqid("msg_", true) . "." . $file_extension;
    $target_file = $target_dir . $image_name;
    
    // Déplacer l'image vers le dossier assets
    move_uploaded_file($_FILES['image']['tmp_name'], $target_file);
}

// Vérification qu'il y a au moins du texte OU une image
if (empty($contenu) && !$image_name) {
    echo json_encode(["status" => "error", "message" => "Le message ne peut pas être vide"]);
    exit();
}

// Insertion du message en base de données
try {
    // Note : si tes camarades utilisent $pdo au lieu de $bdd dans db.php, remplace juste $bdd par $pdo ci-dessous
    $query = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, contenu, image) VALUES (?, ?, ?, ?)");
    $query->execute([$sender_id, $receiver_id, $contenu, $image_name]);
    
    echo json_encode(["status" => "success", "message" => "Message envoyé avec succès"]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Impossible d'enregistrer le message : " . $e->getMessage()]);
}
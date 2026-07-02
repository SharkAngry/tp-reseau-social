<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

require_once '../config/db.php'; // On réutilise leur connexion DB centralisée

// Récupération des données reçues en JSON (car apiRequest envoie du JSON par défaut pour les objets)
$data = json_decode(file_get_contents("php://input"), true);

$email = $data['email'] ?? null;
$password = $data['password'] ?? null;

if (!$email || !$password) {
    echo json_encode(["status" => "error", "message" => "Veuillez remplir tous les champs"]);
    exit();
}

try {
    // Recherche de l'admin/modérateur dans la table `admins`
    // Remplace $bdd par $pdo si nécessaire selon leur fichier db.php
    $query = $pdo->prepare("SELECT * FROM admins WHERE email = ?");
    $query->execute([$email]);
    $admin = $query->fetch(PDO::FETCH_ASSOC);

    // Vérification du mot de passe (en production, utilise password_verify. 
    // Si vos tests utilisent du texte brut pour l'instant, fais juste $password === $admin['password'])
    if ($admin && password_verify($password, $admin['password'])) {
        
        // Génération d'un token de session unique
        $token = bin2hex(random_bytes(32));
        
        // Mise à jour du token en BDD
        $update = $pdo->prepare("UPDATE admins SET session_token = ? WHERE id = ?");
        $update->execute([$token, $admin['id']]);
        
        echo json_encode([
            "status" => "success",
            "token" => $token,
            "role" => $admin['role'], // Renvoie 'admin' ou 'moderateur'
            "nom" => $admin['nom']
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => "Identifiants administratifs incorrects"]);
    }

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Erreur serveur : " . $e->getMessage()]);
}
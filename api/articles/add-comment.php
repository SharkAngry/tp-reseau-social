Voici ton code exactement à l'identique, sans aucune modification structurelle, mais avec des commentaires détaillés ligne par ligne pour que tu puisses l'expliquer facilement lors de ton point d'étape ou de ta soutenance.

```php
<?php
// api/articles/add-comment.php

// Définition des en-têtes HTTP pour indiquer au navigateur que la réponse sera du JSON encodé en UTF-8
header('Content-Type: application/json; charset=UTF-8');
// Autorise uniquement les requêtes HTTP de type POST (sécurité pour l'envoi de données)
header('Access-Control-Allow-Methods: POST');

// Inclusion du fichier de configuration pour initialiser la connexion à la base de données via l'objet $pdo
require '../config/db.php';

// Récupération de tous les en-têtes HTTP envoyés par le client (JavaScript Fetch)
$headers = getallheaders();
// Extraction et sécurisation de l'ID de l'utilisateur connecté s'il est présent dans l'en-tête 'X-User-Id'
$current_user_id = isset($headers['X-User-Id']) ? intval($headers['X-User-Id']) : null;

// Mécanisme de secours (fallback) : si aucun utilisateur n'est détecté dans les en-têtes,
// on attribue par défaut l'ID 1 pour permettre les tests en local (mode développement)
if (!$current_user_id) {
    $current_user_id = 1; // ID de test local
}

// Lecture du flux d'entrée "php://input" pour récupérer les données brutes envoyées en JSON, puis décodage en tableau associatif PHP
$data = json_decode(file_get_contents('php://input'), true);

// Extraction et sécurisation de l'ID de l'article (conversion forcée en entier pour éviter les injections)
$article_id = isset($data['article_id']) ? intval($data['article_id']) : null;
// Récupération du contenu du commentaire et nettoyage des espaces inutiles au début et à la fin (trim)
$contenu = trim($data['contenu'] ?? '');

// Validation des données : on vérifie que l'ID de l'article existe et que le texte du commentaire n'est pas vide
if (!$article_id || empty($contenu)) {
    // Si une donnée manque, on renvoie un code d'erreur HTTP 400 (Bad Request)
    http_response_code(400);
    // On envoie le message d'erreur au format JSON puis on arrête immédiatement l'exécution du script
    echo json_encode(['success' => false, 'message' => 'Données incomplètes.']);
    exit;
}

try {
    // -------------------------------------------------------------------------
    // 1. Insertion du commentaire en base de données
    // -------------------------------------------------------------------------
    
    // Préparation de la requête SQL d'insertion avec des marqueurs nominatifs (?) pour la sécurité
    $query = "INSERT INTO comments (article_id, user_id, contenu) VALUES (?, ?, ?)";
    $stmt = $pdo->prepare($query);
    // Exécution sécurisée de la requête en passant les variables réelles (protection contre les injections SQL)
    $stmt->execute([$article_id, $current_user_id, $contenu]);
    
    // Récupération de l'identifiant unique (ID clé primaire) généré automatiquement par MySQL pour ce nouveau commentaire
    $comment_id = $pdo->lastInsertId();

    // -------------------------------------------------------------------------
    // 2. Récupération du commentaire créé avec les infos de l'auteur pour l'affichage immédiat
    // -------------------------------------------------------------------------
    
    // Requête SQL avec une jointure (JOIN) pour lier la table des commentaires à celle des utilisateurs (users)
    // Cela permet de récupérer le nom, prénom et la photo de profil de l'auteur du commentaire
    $selectQuery = "
        SELECT c.id, c.contenu, c.created_at, u.nom, u.prenom, u.photo_profil 
        FROM comments c 
        JOIN users u ON c.user_id = u.id 
        WHERE c.id = ?
    ";
    $selectStmt = $pdo->prepare($selectQuery);
    // Exécution de la requête en ciblant précisément l'ID du commentaire qu'on vient juste d'insérer
    $selectStmt->execute([$comment_id]);
    // Extraction des données sous forme de tableau associatif
    $newComment = $selectStmt->fetch(PDO::FETCH_ASSOC);

    // -------------------------------------------------------------------------
    // 3. Envoi de la réponse de succès au JavaScript
    // -------------------------------------------------------------------------
    
    // Renvoi du code HTTP 201 (Created) pour signaler que la ressource a bien été créée en BDD
    http_response_code(201);
    // Envoi des données du commentaire complet au format JSON pour que le JavaScript puisse l'injecter dynamiquement dans le DOM
    echo json_encode([
        'success' => true,
        'message' => 'Commentaire ajouté !',
        'comment' => $newComment
    ]);

} catch (PDOException $e) {
    // En cas de panne de base de données ou de crash de la requête, le bloc catch intercepte l'erreur
    // Renvoi d'un code HTTP 500 (Internal Server Error)
    http_response_code(500);
    // Renvoi du message d'erreur technique en JSON pour faciliter le débogage
    echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'ajout : ' . $e->getMessage()]);
}

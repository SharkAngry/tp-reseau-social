<?php
header('Content-Type: application/json');
require '../config/db.php';

$data = json_decode(file_get_contents('php://input'), true);

$email = trim($data['email'] ?? '');
$password = $data['password'] ?? '';

if (empty($email) || empty($password)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Email et mot de passe requis']);
    exit;
}

$stmt = $pdo->prepare('SELECT id, nom, prenom, email, password, photo_profil FROM users WHERE email = ?');
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Email ou mot de passe incorrect']);
    exit;
}

$token = bin2hex(random_bytes(32));

$stmt = $pdo->prepare('UPDATE users SET session_token = ? WHERE id = ?');
$stmt->execute([$token, $user['id']]);

echo json_encode([
    'success' => true,
    'token' => $token,
    'user' => [
        'id' => $user['id'],
        'nom' => $user['nom'],
        'prenom' => $user['prenom'],
        'email' => $user['email'],
        'photo_profil' => $user['photo_profil']
    ]
]);
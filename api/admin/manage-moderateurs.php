<?php
header('Content-Type: application/json');
require_once '../includes/admin-check.php';

if ($currentAdmin['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Accès réservé aux administrateurs']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = $pdo->query("SELECT id, nom, email, role, created_at FROM admins ORDER BY created_at DESC");
    echo json_encode(['success' => true, 'moderateurs' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? '';

try {
    if ($action === 'add') {
        $nom = trim($data['nom'] ?? '');
        $email = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';
        $role = in_array($data['role'] ?? '', ['admin', 'moderateur']) ? $data['role'] : 'moderateur';

        if (!$nom || !$email || !$password) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Champs incomplets']);
            exit;
        }

        $stmt = $pdo->prepare("INSERT INTO admins (nom, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nom, $email, password_hash($password, PASSWORD_BCRYPT), $role]);
        echo json_encode(['success' => true, 'message' => 'Compte créé']);

    } elseif ($action === 'delete') {
        $id = intval($data['id'] ?? 0);
        if ($id === $currentAdmin['id']) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Impossible de se supprimer soi-même']);
            exit;
        }
        $pdo->prepare("DELETE FROM admins WHERE id = ?")->execute([$id]);
        echo json_encode(['success' => true, 'message' => 'Compte supprimé']);

    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Action inconnue']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    error_log($e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Erreur serveur']);
}
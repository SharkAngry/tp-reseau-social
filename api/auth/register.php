<?php
// api/auth/register.php

header('Content-Type: application/json');
require '../config/db.php';

$data = json_decode(file_get_contents('php://input'), true);

$nom    = trim($data['nom']    ?? '');
$prenom = trim($data['prenom'] ?? '');
$email  = trim($data['email']  ?? '');
$password = $data['password']  ?? '';

if (empty($nom) || empty($prenom) || empty($email) || empty($password)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Tous les champs sont obligatoires']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => "Format de l'adresse email invalide"]);
    exit;
}

$stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
$stmt->execute([$email]);
if ($stmt->fetch()) {
    http_response_code(409);
    echo json_encode(['success' => false, 'message' => 'Cet email est déjà utilisé par un autre compte']);
    exit;
}

$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

$stmt = $pdo->prepare('INSERT INTO users (nom, prenom, email, password) VALUES (?, ?, ?, ?)');
$stmt->execute([$nom, $prenom, $email, $hashedPassword]);

// Envoi email de bienvenue (PHPMailer)
require '../config/mail-config.php';
require '../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

try {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host       = MAIL_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = MAIL_USERNAME;
    $mail->Password   = MAIL_PASSWORD;
    $mail->SMTPSecure = 'tls';
    $mail->Port       = MAIL_PORT;
    $mail->CharSet    = 'UTF-8';

    $mail->setFrom('noreply@reseausocial.com', 'Réseau Social');
    $mail->addAddress($email);
    $mail->isHTML(true);
    $mail->Subject = 'Bienvenue sur votre Réseau Social !';
    $mail->Body = "
    <div style='font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, Helvetica, Arial, sans-serif;
                 background-color: #f0f2f5; padding: 40px 20px; text-align: center;'>
        <div style='max-width: 550px; margin: 0 auto; background-color: #ffffff;
                    border-radius: 8px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.1);'>
            <div style='background-color: #1877f2; padding: 25px; text-align: center;'>
                <h1 style='color: #ffffff; margin: 0; font-size: 24px; font-weight: bold;'>Connexion Réseau</h1>
            </div>
            <div style='padding: 30px; text-align: left; color: #1c1e21;'>
                <h2 style='font-size: 20px; margin-top: 0;'>Bienvenue, " . htmlspecialchars($prenom) . " ! 👋</h2>
                <p style='font-size: 15px; line-height: 1.5; color: #606770;'>
                    Votre compte a été créé avec succès. Vous faites désormais partie de notre communauté universitaire.
                </p>
                <p style='font-size: 15px; line-height: 1.5; color: #606770;'>
                    Vous pouvez dès maintenant vous connecter pour compléter votre profil,
                    ajouter des amis et partager vos premières publications.
                </p>
                <div style='text-align: center; margin: 30px 0 15px 0;'>
                    <a href='http://localhost/tp-reseau-social/index.html#login'
                       style='background-color: #42b72a; color: #ffffff; text-decoration: none;
                              padding: 12px 35px; font-size: 16px; font-weight: bold;
                              border-radius: 6px; display: inline-block;'>
                        Accéder à mon espace
                    </a>
                </div>
            </div>
            <div style='background-color: #f5f6f7; padding: 15px; text-align: center;
                        font-size: 12px; color: #8d949e; border-top: 1px solid #e5e5e5;'>
                Ceci est un message automatique, merci de ne pas y répondre.<br>
                &copy; 2026 Projet Final PHP &amp; AJAX. Tous droits réservés.
            </div>
        </div>
    </div>";

    $mail->send();
} catch (Exception $e) {
    // On log sans bloquer la réponse
    error_log('Erreur envoi email confirmation: ' . $mail->ErrorInfo);
}

echo json_encode(['success' => true, 'message' => 'Inscription réussie ! Un e-mail de confirmation vous a été envoyé.']);
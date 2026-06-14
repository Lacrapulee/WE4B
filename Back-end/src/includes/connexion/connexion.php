<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../db.php';

$response = ['success' => false, 'message' => 'Une erreur est survenue'];

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    $response['message'] = "Méthode non autorisée";
    header('Content-Type: application/json');
    http_response_code(405);
    exit;
}

$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

if (empty($email) || empty($password)) {
    $erreurs = "Champs manquants";
    http_response_code(400);
} else {
    try {
        $stmt = $pdo->prepare("SELECT id, email, password, is_admin FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            http_response_code(404);
            $erreurs = "Utilisateur introuvable";
        } elseif (!password_verify($password, $user['password'])) {
            http_response_code(400);
            $erreurs = "Mot de passe incorrect";
        } else {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['is_admin'] = $user['is_admin'];

            $response['success'] = true;
            $response['message'] = "Connexion réussie";
            $response['user_id'] = $user['id'];
            $response['is_admin'] = (bool)$user['is_admin'];
        }
    } catch (PDOException $e) {
        http_response_code(500);
        $erreurs = "Erreur SQL : " . $e->getMessage();
    }
}
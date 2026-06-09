<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../db.php';

$response = ['success' => false, 'message' => 'Une erreur est survenue'];

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    $response['message'] = "Méthode non autorisée";
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

if (empty($email) || empty($password)) {
    $response['message'] = "Champs manquants";
} else {
    try {
        $stmt = $pdo->prepare("SELECT id, email, password, is_admin FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $response['message'] = "Utilisateur introuvable";
        } elseif (!password_verify($password, $user['password'])) {
            $response['message'] = "Mot de passe incorrect";
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
        $response['message'] = "Erreur SQL : " . $e->getMessage();
    }
}

header('Content-Type: application/json');
echo json_encode($response);
exit;
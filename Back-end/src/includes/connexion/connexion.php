<?php

require_once __DIR__ . '/../db.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

$email = isset($_POST['email']) ? $_POST['email'] : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

if (empty($email) || empty($password)) {
    die("Champs manquants");
}

try {
    $stmt = $pdo->prepare("SELECT id, email, password, is_admin FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        die("Utilisateur introuvable");
    }

    if (!password_verify($password, $user['password'])) {
        die("Mot de passe incorrect");
    }

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['is_admin'] = $user['is_admin'];

    header("Location: /index.php");
    exit;

} catch (PDOException $e) {
    die("Erreur SQL : " . $e->getMessage());
}
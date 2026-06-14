<?php
// On s'assure que la session est active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Pas de require_once db.php ici (le routeur s'en charge déjà)

$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

if (empty($email) || empty($password)) {
    $erreurs = "Champs manquants";
} else {
    try {
        // Recherche de l'utilisateur
        $stmt = $pdo->prepare("SELECT id, email, password, is_admin FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $erreurs = "Utilisateur introuvable";
        } elseif (!password_verify($password, $user['password'])) {
            $erreurs = "Mot de passe incorrect";
        } else {
            // =======================================================
            // L'ÉCRITURE CRUCIALE EN SESSION
            // =======================================================
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email']   = $user['email'];
            $_SESSION['is_admin'] = $user['is_admin'];
            
            // On force PHP à enregistrer immédiatement les données sur le disque du conteneur
            session_write_close();
            
            // On réactive la session pour que le routeur puisse continuer à travailler avec
            session_start();
        }
    } catch (PDOException $e) {
        $erreurs = "Erreur SQL : " . $e->getMessage();
    }
}

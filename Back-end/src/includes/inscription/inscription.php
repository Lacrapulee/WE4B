<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../db.php';


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    $nom = $_POST['nom'] ?? null;
    $prenom = $_POST['prenom'] ?? null;
    $telephone = $_POST['telephone'] ?? null;
    $date_naissance = $_POST['date_naissance'] ?? null;
    $adresse = $_POST['adresse_postale'] ?? null;

    if (empty($email) || empty($password) || empty($confirm)) {
        $erreurs = "Champs obligatoires manquants";
        http_response_code(400);
    } elseif ($password !== $confirm) {
        $erreurs = "Les mots de passe ne correspondent pas";
        http_response_code(400);
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
            $stmt->execute(['email' => $email]);

            if ($stmt->fetch()) {
                $response['message'] = "Cet email est déjà utilisé par un autre compte.";
            } else {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $pdo->prepare("
                    INSERT INTO users 
                    (email, password, nom, prenom, telephone, date_naissance, adresse_postale)
                    VALUES 
                    (:email, :password, :nom, :prenom, :telephone, :date_naissance, :adresse)
                ");

                $stmt->execute([
                    'email' => $email,
                    'password' => $hashedPassword,
                    'nom' => $nom,
                    'prenom' => $prenom,
                    'telephone' => $telephone,
                    'date_naissance' => $date_naissance ?: null,
                    'adresse' => $adresse
                ]);
                
                $id = $pdo->lastInsertId();

                $_SESSION['user_id'] = $id;
                $_SESSION['email'] = $email;

                $response['success'] = true;
                $response['message'] = "Bienvenue ! Redirection en cours...";
            }
        } catch (PDOException $e) {
            $erreurs = "Erreur SQL : " . $e->getMessage();
        }
    }
}
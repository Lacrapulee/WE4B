<?php
require_once __DIR__ . '/../db.php';

// Sécurité : Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: ../connexion');
    exit;
}

// 2. VÉRIFICATION DE SÉCURITÉ (Owner ou Admin)
$isOwner = ($_SESSION['user_id'] == $_GET['id']);
$isAdmin = (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1);
$user_id = $_GET['id'];
$success = false;
$error = null;

if (!$isOwner && !$isAdmin) {
    // Redirection si l'utilisateur n'a pas le droit d'être ici
    header('Location: /routeur.php?action=user&id=' . $_SESSION['user_id']);
    exit();
}

// --- PARTIE 1 : TRAITEMENT DE LA MISE À JOUR ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');
    $adresse_postale = trim($_POST['adresse_postale'] ?? '');

    if (!empty($nom) && !empty($prenom)) {
        $stmt = $pdo->prepare("UPDATE users SET nom = ?, prenom = ?, email = ?, telephone = ?, adresse_postale = ? WHERE id = ?");
        if ($stmt->execute([$nom, $prenom, $email, $telephone, $adresse_postale, $user_id])) {
            $success = true;
        } else {
            $error = "Erreur lors de la mise à jour.";
        }
    } else {
        $error = "Le nom et le prénom sont obligatoires.";
    }
    
}

// --- PARTIE 2 : RÉCUPÉRATION DES INFOS ACTUELLES ---
$stmt = $pdo->prepare("SELECT nom, prenom, email, telephone, adresse_postale FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
<?php
// includes/item/edit_item.php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../articles_functions.php';

// 1. Vérification de base : l'utilisateur est-il connecté ?
if (!isset($_SESSION['user_id'])) {
    header('Location: /routeur.php?action=auth');
    exit();
}

$productId = $_GET['id'] ?? $_POST['article_id'] ?? null;
$product = getAnnonceById($pdo, $productId);

if (!$product) {
    die("Article introuvable.");
}

// 2. VÉRIFICATION DE SÉCURITÉ (Owner ou Admin)
$isOwner = ($_SESSION['user_id'] == $product['vendeur_id']);
$isAdmin = (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1);

if (!$isOwner && !$isAdmin) {
    // Redirection si l'utilisateur n'a pas le droit d'être ici
    header('Location: /routeur.php?action=item&id=' . $productId);
    exit();
}

// 3. TRAITEMENT DU FORMULAIRE (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_article'])) {
    $titre = $_POST['titre'];
    $description = $_POST['description'];
    $prix = $_POST['prix'];
    $categorie_id = $_POST['categorie_id'];

    // Ici, vous devriez appeler une fonction de mise à jour dans articles_functions.php
    // Exemple : updateAnnonce($pdo, $productId, $titre, $description, $prix, $categorie_id);
    
    // Pour cet exemple, on simule la mise à jour :
    $stmt = $pdo->prepare("UPDATE articles SET titre = ?, description = ?, prix = ?, categorie_id = ? WHERE id = ?");
    $stmt->execute([$titre, $description, $prix, $categorie_id, $productId]);

    // Redirection après succès
    header('Location: /routeur.php?action=item&id=' . $productId);
    exit();
}
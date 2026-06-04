<?php
// includes/item.php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../articles_functions.php';

// 1. Récupération des données
$productId = $_GET['id'] ?? null;
$product = getAnnonceById($pdo, $productId);

$isAdmin = (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1);

// 2. Traitement des erreurs et données liées
if (!$product || $product['statut'] !== 'en_ligne') {
    $errorMessage = "Cet article n'existe pas ou n'est plus disponible.";
} else {
    $errorMessage = null;
    
    // Récupération des images
    $allImages = getAllImagesByAnnonceId($pdo, $product['id']);
    if (empty($allImages)) {
        $allImages = ['default.png'];
    }
    
    // Récupération des annonces similaires
    $similarAds = getAnnoncesSimilaires($pdo, $product['categorie_id'], $product['id']);

    // Récupération du vendeur pour voir si c'est le même que l'utilisateur connecté
    if ($product['vendeur_id']) {
        $isOwner = isset($_SESSION['user_id']) && $_SESSION['user_id'] == $product['vendeur_id'];
    } else {
        $isOwner = false;
    }
}



<?php
// includes/item/item.php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../articles_functions.php';

// 1. Récupération et validation de l'ID 
$productId = (int)($_GET['id'] ?? 0);

if ($productId === 0) {
    http_response_code(400);
    echo json_encode(['error' => 'ID de l\'article manquant ou invalide']);
    exit();
}

// 2. Récupération du produit
$product = getAnnonceById($pdo, $productId);

// Sécurité API : Si le produit n'existe pas ou n'est pas en ligne, on coupe direct en 404
if (!$product || $product['statut'] !== 'en_ligne') {
    http_response_code(404);
    echo json_encode(['error' => "Cet article n'existe pas ou n'est plus disponible."]);
    exit();
}

// 3. Récupération des données liées si le produit existe
$isAdmin = (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1);

// Récupération des images
$allImages = getAllImagesByAnnonceId($pdo, $product['id']);
if (empty($allImages)) {
    $allImages = ['default.png'];
}

// Récupération des annonces similaires
$similarAds = getAnnoncesSimilaires($pdo, $product['categorie_id'], $product['id']);

// Vérification de la propriété
$isOwner = false;
if ($product['vendeur_id']) {
    $isOwner = isset($_SESSION['user_id']) && $_SESSION['user_id'] == $product['vendeur_id'];
}
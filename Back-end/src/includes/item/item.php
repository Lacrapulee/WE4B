<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../articles_functions.php';

$productId = $_GET['id'] ?? null;
$product = getAnnonceById($pdo, $productId);
$results = []; // ← initialise toujours $results
$isAdmin = (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1);

if (!$product) {
    $errorMessage = "Cet article n'existe pas ou n'est plus disponible.";
} else {
    $errorMessage = null;
    $allImages = getAllImagesByAnnonceId($pdo, $product['id']);
    $similarAds = getAnnoncesSimilaires($pdo, $product['categorie_id'], $product['id']);
    if (!empty($similarAds)) {
        $similarIds = array_column($similarAds, 'id');
        $similarImages = getImagesByAnnonceIds($pdo, $similarIds);
        foreach ($similarAds as &$ad) {
            $ad['image'] = $similarImages[$ad['id']] ?? null;
        }
        unset($ad);
    }
    
    $isOwner = isset($_SESSION['user_id']) && $_SESSION['user_id'] == $product['vendeur_id'];

    $results = [
        'item'      => $product,
        'images'    => $allImages,
        'similarAds'=> $similarAds,
        'isOwner'   => (bool)$isOwner,
        'isAdmin'   => (bool)$isAdmin
    ];
}

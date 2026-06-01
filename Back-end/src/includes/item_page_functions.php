<?php

function buildItemPageViewData($pdo, $productId) {
    $viewData = [
        'statusCode' => 200,
        'errorMessage' => null,
        'title' => 'Article',
        'product' => null,
        'imageName' => 'default.png',
        'similarAds' => [],
        'sellerDisplayName' => 'Vendeur',
        'priceLabel' => null,
    ];

    if (!$productId) {
        $viewData['statusCode'] = 400;
        $viewData['errorMessage'] = 'Erreur: ID de l\'article manquant.';
        return $viewData;
    }

    $product = getAnnonceById($pdo, $productId);

    if (!$product) {
        $viewData['statusCode'] = 404;
        $viewData['errorMessage'] = 'Erreur: Article non trouvé.';
        return $viewData;
    }

    $similarAds = getAnnoncesSimilaires($pdo, (int) $product['categorie_id'], $product['id']);
    $similarIds = array_map(function ($ad) {
        return $ad['id'];
    }, $similarAds);
    $similarImages = getImagesByAnnonceIds($pdo, $similarIds);

    foreach ($similarAds as &$similarAd) {
        $similarAd['image_name'] = $similarImages[$similarAd['id']] ?? 'default.png';
        $similarAd['price_label'] = number_format((float) $similarAd['prix'], 2, ',', ' ') . ' EUR';
    }
    unset($similarAd);

    $sellerDisplayName = trim(($product['vendeur_prenom'] ?? '') . ' ' . ($product['vendeur_nom'] ?? ''));
    if ($sellerDisplayName === '') {
        $sellerDisplayName = 'Vendeur';
    }

    $viewData['product'] = $product;
    $viewData['title'] = $product['titre'];
    $viewData['imageName'] = getImageByAnnonceId($pdo, $product['id']) ?: 'default.png';
    $viewData['similarAds'] = $similarAds;
    $viewData['sellerDisplayName'] = $sellerDisplayName;
    $viewData['priceLabel'] = number_format((float) $product['prix'], 2, ',', ' ') . ' EUR';

    return $viewData;
}
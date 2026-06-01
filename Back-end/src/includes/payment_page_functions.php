<?php

require_once __DIR__ . '/sales_functions.php';

function buildPaymentPageViewData($pdo, $articleId, $requestMethod, $postData) {
    $viewData = [
        'statusCode' => 200,
        'title' => 'Paiement',
        'errorMessage' => null,
        'successMessage' => null,
        'orderReference' => null,
        'product' => null,
        'imageName' => 'default.png',
        'priceLabel' => null,
        'buyerName' => '',
        'buyerEmail' => '',
    ];

    if (!isset($_SESSION['user_id'])) {
        $viewData['statusCode'] = 401;
        $viewData['errorMessage'] = 'Vous devez être connecté pour acheter un article.';
        return $viewData;
    }

    if (!$articleId) {
        $viewData['statusCode'] = 400;
        $viewData['errorMessage'] = 'Aucun article selectionne pour le paiement.';
        return $viewData;
    }

    $product = getAnnonceById($pdo, $articleId);
    if (!$product) {
        $viewData['statusCode'] = 404;
        $viewData['errorMessage'] = 'Article introuvable.';
        return $viewData;
    }

    // If the article exists but is not available for sale, return a clearer message
    $currentStatut = $product['statut'] ?? '';
    if ($currentStatut !== 'en_ligne') {
        $viewData['statusCode'] = 409; // Conflict
        $viewData['errorMessage'] = 'Article indisponible (statut: ' . htmlspecialchars($currentStatut) . ').';
        return $viewData;
    }

    $viewData['product'] = $product;
    $viewData['title'] = 'Paiement - ' . $product['titre'];
    $viewData['imageName'] = getImageByAnnonceId($pdo, $product['id']) ?: 'default.png';
    $viewData['priceLabel'] = number_format((float) $product['prix'], 2, ',', ' ') . ' EUR';

    if ($requestMethod !== 'POST' || !isset($postData['confirm_payment'])) {
        return $viewData;
    }

    $buyerName = trim((string) ($postData['buyer_name'] ?? ''));
    $buyerEmail = trim((string) ($postData['buyer_email'] ?? ''));

    // Require login so we can record acheteur_id for mes_commandes
    $buyerId = $_SESSION['user_id'] ?? null;
    if (!$buyerId) {
        $viewData['statusCode'] = 401;
        $viewData['errorMessage'] = 'Vous devez être connecté pour effectuer un achat.';
        return $viewData;
    }

    $viewData['buyerName'] = $buyerName;
    $viewData['buyerEmail'] = $buyerEmail;

    if ($buyerName === '' || $buyerEmail === '') {
        $viewData['statusCode'] = 422;
        $viewData['errorMessage'] = 'Merci de remplir ton nom et ton email pour valider le paiement.';
        return $viewData;
    }

    if (!filter_var($buyerEmail, FILTER_VALIDATE_EMAIL)) {
        $viewData['statusCode'] = 422;
        $viewData['errorMessage'] = 'Adresse email invalide.';
        return $viewData;
    }

    // Re-fetch product to ensure we have the latest status before attempting the sale
    $product = getAnnonceById($pdo, $articleId);
    if (!$product) {
        $viewData['statusCode'] = 404;
        $viewData['errorMessage'] = 'Article introuvable au moment de la validation.';
        return $viewData;
    }
    if (($product['statut'] ?? '') !== 'en_ligne') {
        $viewData['statusCode'] = 409;
        $viewData['errorMessage'] = 'Impossible de valider le paiement : article non disponible (statut: ' . htmlspecialchars($product['statut'] ?? '') . ').';
        return $viewData;
    }

    $saleResult = processDirectSale($pdo, $product, $buyerId, $buyerName, $buyerEmail);
    if (!$saleResult['ok']) {
        $viewData['statusCode'] = 409;
        $viewData['errorMessage'] = $saleResult['error'];
        return $viewData;
    }

    $viewData['orderReference'] = $saleResult['reference'];
    $viewData['successMessage'] = 'Paiement valide. Ta commande est confirmee.';

    return $viewData;
}

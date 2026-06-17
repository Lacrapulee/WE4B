<?php
include_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../articles_functions.php';
require_once __DIR__ . '/../favoris_functions.php';

// On récupère tous les filtres depuis l'URL (GET)
$filters = [
    'search'    => $_GET['search'] ?? '',
    'categorie' => $_GET['categorie'] ?? '',
    'ville'     => $_GET['ville'] ?? '',
    'prix_min'  => $_GET['prix_min'] ?? '',
    'prix_max'  => $_GET['prix_max'] ?? '',
    'distance'  => $_GET['distance'] ?? '',
    'tri'       => $_GET['tri'] ?? 'date_recent',
    'index'     => $_GET['index'] ?? 0
];

$results = getAnnonceRechercheAvancee($pdo, $filters);
$categories = getCategories($pdo);

if (!empty($results)) {
    $articleIds = array_column($results, 'id');
    $images = getImagesByAnnonceIds($pdo, $articleIds);
    
    $user_id = $_SESSION['user_id'] ?? null;
    $favoris_ids = [];
    if ($user_id) {
        $inQuery = implode(',', array_map('intval', $articleIds));
        $favoris_stmt = $pdo->prepare("SELECT article_id FROM favoris WHERE user_id = ? AND article_id IN ($inQuery)");        $favoris_stmt->execute([$user_id]);
        $favoris_ids = $favoris_stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    }

    foreach ($results as &$article) {
        $article['image'] = $images[$article['id']] ?? null;
        $article['isFavoris'] = in_array($article['id'], $favoris_ids);
    }
    unset($article);
}


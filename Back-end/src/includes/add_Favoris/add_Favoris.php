<?php 
require_once __DIR__ . '/../favoris_functions.php';
require_once __DIR__ . '/../db.php';

$result = null;
$error = null;
$user_id = $_SESSION['user_id'] ?? null;
$article_id = $_POST['article_id'] ?? null;

if (!$user_id) {
    http_response_code(403);
    $error = 'Non connecté';
} elseif (!$article_id) {
    http_response_code(400);
    $error = 'Article manquant';
} elseif (addFavoris($pdo, $user_id, $article_id)) {
    $result = true;
    $error = 'Article ajouté aux favoris';
    http_response_code(200);
} else {
    http_response_code(500);
    $error = 'Erreur lors de l\'ajout aux favoris';
}
?>
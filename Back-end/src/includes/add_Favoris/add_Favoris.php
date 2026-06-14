<?php 

require_once __DIR__ . '/../favoris_functions.php';
require_once __DIR__ . '/../db.php';

$response = ['success' => false, 'message' => ''];
$error = null;

$user_id = $_SESSION['user_id'] ?? null;
$article_id = $_POST['article_id'] ?? null;

if ($article_id && $user_id) {

    if ($_SESSION['user_id'] == $user_id) {

        if (addFavoris($pdo, $user_id, $article_id)) {
            $response['success'] = true;
            $response['message'] = 'Article ajouté aux favoris';    

        } else {
            $response['message'] = 'erreur lors de l\'ajout aux favoris';
            $error = 'Erreur lors de l\'ajout aux favoris';
        }
    } else {
        $response['message'] = 'Vous n\'avez pas la permission d\'ajouter cet article aux favoris';
        $error = 'Vous n\'avez pas la permission d\'ajouter cet article aux favoris';
    }

}

?>
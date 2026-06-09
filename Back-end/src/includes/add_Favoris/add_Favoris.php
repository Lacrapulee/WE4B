<?php 

include __DIR__ . '../includes/favoris_function.php';
include_once __DIR__ . '/../db.php';

$response = ['success' => false, 'message' => ''];
$error = null;

if (isset($_POST['article_id']) && isset($_POST['user_id'])) {

    if ($_SESSION['user_id'] == $_POST['user_id']) {
        $article_id = $_POST['article_id'];
        $user_id = $_POST['user_id'];

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
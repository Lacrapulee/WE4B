<?php 

include __DIR__ . '../includes/favoris_function.php';
include_once __DIR__ . '/../db.php';

$result = null;
$error = null;

if (isset($_POST['article_id']) && isset($_POST['user_id'])) {

    if ($_SESSION['user_id'] == $_POST['user_id']) {
        $article_id = $_POST['article_id'];
        $user_id = $_POST['user_id'];
        
        if (addFavoris($pdo, $user_id, $article_id)) {
            $result = true;
            $error = 'Article ajouté aux favoris';    
            http_response_code(200);
        } else {
            http_response_code(500);
            $error = 'Erreur lors de l\'ajout aux favoris';
        }
    } else {
        http_response_code(403);
        $error = 'Vous n\'avez pas la permission d\'ajouter cet article aux favoris';
    }

}


?>
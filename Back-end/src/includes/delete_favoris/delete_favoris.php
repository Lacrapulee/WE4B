<?php 

include '../favoris_functions.php';
include '../db.php';
if (isset($_POST['id_user']) && isset($_POST['article_id'])) {
    $id_user = $_POST['id_user'];
    $id_favoris = $_POST['article_id'];

    $result = removeFavoris($pdo, $id_user, $id_favoris);


}

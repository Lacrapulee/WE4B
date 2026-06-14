<?php 

require_once __DIR__ . '/../favoris_functions.php';
require_once __DIR__ . '/../db.php';

$id_user = $_POST['id_user'] ?? $_SESSION['user_id'] ?? null;
$id_favoris = $_POST['article_id'] ?? null;

if ($id_user && $id_favoris) {
    if ($_SESSION['user_id'] == $id_user) {
        $result = removeFavoris($pdo, $id_user, $id_favoris);
    }
}

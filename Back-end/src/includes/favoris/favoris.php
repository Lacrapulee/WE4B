<?php
/**
 * Logique pour la page des favoris
 */
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../articles_functions.php';
require_once __DIR__ . '/../favoris_functions.php';

$user_id = $_SESSION['user_id'] ?? null;

// Récupérer les favoris de l'utilisateur
$favoris = getFavorisUtilisateur($pdo, $_SESSION['user_id']);

// Récupérer les images pour les favoris
$articleIds = array_column($favoris, 'id');
$images = getImagesByAnnonceIds($pdo, $articleIds);
?>

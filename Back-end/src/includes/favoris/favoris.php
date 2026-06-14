<?php
/**
 * Logique pour la page des favoris
 */
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../articles_functions.php';
require_once __DIR__ . '/../favoris_functions.php';

$user_id = $_SESSION['user_id'] ?? null;

// Récupérer les favoris de l'utilisateur
if ($user_id) {
    $favoris = getFavorisUtilisateur($pdo, $user_id) ?? [];  // ← forcer []
    $articleIds = array_column($favoris, 'id');
    $images = !empty($articleIds) 
        ? (getImagesByAnnonceIds($pdo, $articleIds) ?? [])   // ← forcer []
        : [];
} else {
    $favoris = [];
    $images = [];
}
?>

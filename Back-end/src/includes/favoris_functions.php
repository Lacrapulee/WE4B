<?php

/**
 * Ajouter un article aux favoris
 */
function addFavoris($pdo, $user_id, $article_id) {
    $stmt = $pdo->prepare("INSERT IGNORE INTO favoris (user_id, article_id) VALUES (?, ?)");
    return $stmt->execute([$user_id, $article_id]);
}

/**
 * Retirer un article des favoris
 */
function removeFavoris($pdo, $user_id, $article_id) {
    $stmt = $pdo->prepare("DELETE FROM favoris WHERE user_id = ? AND article_id = ?");
    return $stmt->execute([$user_id, $article_id]);
}

/**
 * Vérifier si un article est en favoris
 */
function isFavoris($pdo, $user_id, $article_id) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM favoris WHERE user_id = ? AND article_id = ?");
    $stmt->execute([$user_id, $article_id]);
    return (int) $stmt->fetchColumn() > 0;
}

/**
 * Récupérer tous les favoris d'un utilisateur avec les détails des articles
 */
function getFavorisUtilisateur($pdo, $user_id) {
    $stmt = $pdo->prepare(
        "SELECT a.*, c.nom as categorie_nom, f.ajoute_le
         FROM favoris f
         INNER JOIN articles a ON f.article_id = a.id
         LEFT JOIN categories c ON a.categorie_id = c.id
         WHERE f.user_id = ?
         ORDER BY f.ajoute_le DESC"
    );
    $stmt->execute([$user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Compter le nombre de favoris d'un utilisateur
 */
function countFavoris($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM favoris WHERE user_id = ?");
    $stmt->execute([$user_id]);
    return (int) $stmt->fetchColumn();
}
?>

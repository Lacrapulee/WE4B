<?php
require_once __DIR__ . '/../db.php';
$itemIdToDelete = $_GET['id'] ?? null;
if (!isset($_SESSION['user_id'])) {
    die("Action non autorisée.");
}
if ($_SESSION['is_admin'] == 1) {
    // Les admins peuvent supprimer n'importe quelle annonce
} else {
    // Les utilisateurs normaux ne peuvent supprimer que leurs propres annonces
    $stmt = $pdo->prepare("SELECT vendeur_id FROM articles WHERE id = ?");
    $stmt->execute([$itemIdToDelete]);
    $itemOwnerId = $stmt->fetchColumn();
    
    if ($itemOwnerId != $_SESSION['user_id']) {
        die("Action non autorisée.");
    }
}
if ($itemIdToDelete) {
    
    try {
        $stmt = $pdo->prepare("DELETE FROM articles WHERE id = ?");
        $stmt->execute([$itemIdToDelete]);


    } catch (PDOException $e) {
        die("Erreur lors de la suppression de l'annonce : " . $e->getMessage());
    }

} else {
    die("Action non autorisée.");
}
?>
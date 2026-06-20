<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../mongo.php';

$itemIdToDelete = $_GET['id'] ?? null;

if (!isset($_SESSION['user_id'])) {
    $error = "Action non autorisée.";
    return;
}

$isAdmin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;

if (!$isAdmin) {
    // Les utilisateurs normaux ne peuvent supprimer que leurs propres annonces
    $stmt = $pdo->prepare("SELECT vendeur_id FROM articles WHERE id = ?");
    $stmt->execute([$itemIdToDelete]);
    $itemOwnerId = $stmt->fetchColumn();
    
    if ($itemOwnerId != $_SESSION['user_id']) {
        $error = "Action non autorisée.";
        return;
    }
}

if ($itemIdToDelete) {
    try {
        // 1. Récupérer les IDs Mongo des images associées
        $stmtImg = $pdo->prepare("SELECT url_image FROM article_images WHERE article_id = ?");
        $stmtImg->execute([$itemIdToDelete]);
        $images = $stmtImg->fetchAll(PDO::FETCH_COLUMN);

        // 2. Récupérer l'ID de l'image par défaut pour ne pas la supprimer de MongoDB
        $defaultImageId = null;
        if (isset($imageCollection)) {
            $defaultImage = $imageCollection->findOne(['is_default' => true]);
            if ($defaultImage) {
                $defaultImageId = (string)$defaultImage['_id'];
            }
        }

        // 3. Supprimer les images spécifiques de MongoDB
        if (!empty($images) && isset($imageCollection)) {
            foreach ($images as $imgId) {
                if ($imgId !== $defaultImageId && preg_match('/^[0-9a-fA-F]{24}$/', $imgId)) {
                    $imageCollection->deleteOne(['_id' => new MongoDB\BSON\ObjectId($imgId)]);
                }
            }
        }

        // 4. Supprimer l'article de MySQL (CASCADE supprimera les lignes dans article_images et article_attributs_valeurs)
        $stmt = $pdo->prepare("DELETE FROM articles WHERE id = ?");
        $stmt->execute([$itemIdToDelete]);

        $result = true;
    } catch (Exception $e) {
        $error = "Erreur lors de la suppression de l'annonce : " . $e->getMessage();
    }
} else {
    $error = "Action non autorisée.";
}
?>
<?php
require_once __DIR__ . '/db.php';

// Vérification de la méthode d'envoi et de la connexion utilisateur
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $expediteur_id = $_SESSION['user_id'] ?? null;
    
    $article_id    = !empty($_POST['article_id']) ? $_POST['article_id'] : null;
    $dest_id       = $_POST['destinataire_id'] ?? null;
    $note          = isset($_POST['note']) ? intval($_POST['note']) : 0;
    $commentaire   = isset($_POST['commentaire']) ? trim($_POST['commentaire']) : '';

    // --- SÉCURITÉ ---
    
    if (!$expediteur_id) {
        header("Location: /routeur.php?action=auth&error=login_required");
        exit();
    }

    if (!$dest_id) {
        header("Location: /");
        exit();
    }

    // Un utilisateur ne peut pas se noter lui-même
    if ($expediteur_id === $dest_id) {
        header("Location: /routeur.php?action=user&id=$dest_id&error=self_note");
        exit();
    }

    // Validation de la note
    if ($note < 1 || $note > 5) {
        header("Location: /routeur.php?action=avis&vendeur_id=$dest_id&error=invalid_note");
        exit();
    }

    // Validation du commentaire
    if (trim($commentaire) === '') {
        header("Location: /routeur.php?action=avis&vendeur_id=$dest_id&error=empty_comment");
        exit();
    }

    // Vérifier que le destinataire existe
    $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ?");
    $stmt->execute([$dest_id]);
    if (!$stmt->fetch()) {
        header("Location: /routeur.php?action=avis&vendeur_id=$dest_id&error=user_not_found");
        exit();
    }

    // Vérifier qu'il n'existe pas déjà un avis du même expéditeur vers ce destinataire
    $stmt = $pdo->prepare("SELECT id FROM avis WHERE expediteur_id = ? AND destinataire_id = ?");
    $stmt->execute([$expediteur_id, $dest_id]);
    if ($stmt->fetch()) {
        header("Location: /routeur.php?action=avis&vendeur_id=$dest_id&error=already_reviewed");
        exit();
    }

    // --- INSERTION EN BASE DE DONNÉES ---

    try {
        $pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_general_ci");

        $stmt = $pdo->prepare("
            INSERT INTO avis (article_id, expediteur_id, destinataire_id, note, commentaire, date_avis) 
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            $article_id, 
            $expediteur_id, 
            $dest_id, 
            $note, 
            $commentaire
        ]);

        header("Location: /routeur.php?action=user&id=$dest_id&status=success");
        exit();

    } catch (PDOException $e) {
        error_log("Erreur lors de l'ajout d'un avis : " . $e->getMessage());
        
        $error_code = 'db_error';
        if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
            $error_code = 'already_reviewed';
        } elseif (strpos($e->getMessage(), 'foreign key') !== false) {
            $error_code = 'user_not_found';
        }
        
        header("Location: /routeur.php?action=avis&vendeur_id=$dest_id&error=$error_code");
        exit();
    }

} else {
    header("Location: /");
    exit();
}
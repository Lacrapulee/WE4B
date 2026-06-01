<?php
$imagesByCommande = [];

require_once __DIR__ . '/../articles_functions.php';

$userId = $_SESSION['user_id'] ?? null;
$commandes = [];

if ($userId) {
    try {
        $stmt = $pdo->prepare(
            "SELECT v.*, a.titre, a.vendeur_id
             FROM ventes v
             JOIN articles a ON v.article_id = a.id
             WHERE v.acheteur_id = ?
             ORDER BY v.created_at DESC"
        );
        $stmt->execute([$userId]);
        $commandes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($commandes)) {
            $userEmailStmt = $pdo->prepare('SELECT email FROM users WHERE id = ?');
            $userEmailStmt->execute([$userId]);
            $userEmail = $userEmailStmt->fetchColumn();

            if ($userEmail) {
                $stmt = $pdo->prepare(
                    "SELECT v.*, a.titre, a.vendeur_id
                     FROM ventes v
                     JOIN articles a ON v.article_id = a.id
                     WHERE v.acheteur_email = ?
                     ORDER BY v.created_at DESC"
                );
                $stmt->execute([$userEmail]);
                $commandes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        }

        foreach ($commandes as $commande) {
            $imagesByCommande[$commande['article_id']] = getImageByAnnonceId($pdo, $commande['article_id']) ?: 'default.png';
        }
    } catch (PDOException $e) {
        if ($e->getCode() === '42S22') {
            $userEmailStmt = $pdo->prepare('SELECT email FROM users WHERE id = ?');
            $userEmailStmt->execute([$userId]);
            $userEmail = $userEmailStmt->fetchColumn();

            if ($userEmail) {
                try {
                    $stmt = $pdo->prepare(
                        "SELECT v.*, a.titre, a.vendeur_id
                         FROM ventes v
                         JOIN articles a ON v.article_id = a.id
                         WHERE v.acheteur_email = ?
                         ORDER BY v.created_at DESC"
                    );
                    $stmt->execute([$userEmail]);
                    $commandes = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($commandes as $commande) {
                        $imagesByCommande[$commande['article_id']] = getImageByAnnonceId($pdo, $commande['article_id']) ?: 'default.png';
                    }
                } catch (PDOException $fallbackException) {
                    error_log('mes_commandes fallback failed: ' . $fallbackException->getMessage());
                }
            }
        } else {
            error_log('mes_commandes query error: ' . $e->getMessage());
        }
    }
}

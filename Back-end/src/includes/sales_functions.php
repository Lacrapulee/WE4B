<?php

function processDirectSale($pdo, $article, $buyerId, $buyerName, $buyerEmail) {
    $result = [
        'ok' => false,
        'reference' => null,
        'error' => null,
    ];

    $articleId = (string) ($article['id'] ?? '');
    $sellerId = (string) ($article['vendeur_id'] ?? '');
    $amount = (float) ($article['prix'] ?? 0);

    if ($articleId === '' || $sellerId === '') {
        $result['error'] = 'Article invalide pour la vente.';
        return $result;
    }

    $reference = 'CMD-' . strtoupper(bin2hex(random_bytes(4)));

    try {
        $pdo->beginTransaction();

        // 1. Marquer l'article comme vendu
        $update = $pdo->prepare("UPDATE articles SET statut = 'vendu' WHERE id = ? AND statut = 'en_ligne'");
        $update->execute([$articleId]);

        if ($update->rowCount() !== 1) {
            $pdo->rollBack();
            $result['error'] = 'Cet article vient d\'être vendu ou n\'est plus disponible.';
            return $result;
        }

        // 2. Insérer la vente avec l'ID de l'acheteur et le statut 'paye'
        // Vérifie bien que tes colonnes acheteur_id et statut existent en DB
        $insert = $pdo->prepare(
            "INSERT INTO ventes (reference, article_id, vendeur_id, acheteur_id, acheteur_nom, acheteur_email, montant, statut, statut_paiement)
             VALUES (?, ?, ?, ?, ?, ?, ?, 'paye', 'valide')"
        );
        
        $insert->execute([
            $reference, 
            $articleId, 
            $sellerId, 
            $buyerId, // L'ID de l'utilisateur connecté
            $buyerName, 
            $buyerEmail, 
            $amount
        ]);

        $pdo->commit();

        $result['ok'] = true;
        $result['reference'] = $reference;
        return $result;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('Erreur processDirectSale : ' . $e->getMessage());
        $result['error'] = 'Erreur technique pendant la validation du paiement.';
        return $result;
    }
}
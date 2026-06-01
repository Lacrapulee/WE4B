<?php

/**
 * Récupère ou crée une conversation entre deux utilisateurs pour un article (optionnel)
 * @param PDO $pdo
 * @param int|null $article_id (null si contact direct au vendeur)
 * @param string $acheteur_id
 * @param string $vendeur_id
 * @return string ID de la conversation
 */
function getOrCreateConversation(PDO $pdo, ?int $article_id, string $acheteur_id, string $vendeur_id): string {
    try {
        // Chercher une conversation existante
        if ($article_id) {
            $stmt = $pdo->prepare("
                SELECT id FROM conversations 
                WHERE article_id = ? AND acheteur_id = ? AND vendeur_id = ?
            ");
            $stmt->execute([$article_id, $acheteur_id, $vendeur_id]);
        } else {
            $stmt = $pdo->prepare("
                SELECT id FROM conversations 
                WHERE article_id IS NULL AND acheteur_id = ? AND vendeur_id = ?
            ");
            $stmt->execute([$acheteur_id, $vendeur_id]);
        }
        $conversation = $stmt->fetch();

        if ($conversation) {
            return $conversation['id'];
        }

        // Créer une nouvelle conversation
        $conversation_id = bin2hex(random_bytes(18));
        $stmt = $pdo->prepare("
            INSERT INTO conversations (id, article_id, acheteur_id, vendeur_id)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$conversation_id, $article_id, $acheteur_id, $vendeur_id]);

        return $conversation_id;
    } catch (Exception $e) {
        error_log('getOrCreateConversation error: ' . $e->getMessage());
        return '';
    }
}

/**
 * Envoie un message dans une conversation
 * @param PDO $pdo
 * @param string $conversation_id
 * @param string $expediteur_id
 * @param string $contenu
 * @return bool
 */
function sendMessage(PDO $pdo, string $conversation_id, string $expediteur_id, string $contenu): bool {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO messages (conversation_id, expediteur_id, contenu, lu)
            VALUES (?, ?, ?, 0)
        ");
        return $stmt->execute([$conversation_id, $expediteur_id, $contenu]);
    } catch (Exception $e) {
        error_log('sendMessage error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Récupère tous les messages d'une conversation
 * @param PDO $pdo
 * @param string $conversation_id
 * @param string $user_id
 * @return array
 */
function getConversationMessages(PDO $pdo, string $conversation_id, string $user_id): array {
    try {
        $stmt = $pdo->prepare("
            SELECT m.*, 
                   u.prenom, u.nom,
                   CASE WHEN m.expediteur_id = ? THEN 'sent' ELSE 'received' END as position
            FROM messages m
            JOIN users u ON m.expediteur_id = u.id
            WHERE m.conversation_id = ?
            ORDER BY m.date_envoi ASC
        ");
        $stmt->execute([$user_id, $conversation_id]);
        
        // Marquer les messages comme lus
        $updateStmt = $pdo->prepare("
            UPDATE messages 
            SET lu = 1 
            WHERE conversation_id = ? AND expediteur_id != ?
        ");
        $updateStmt->execute([$conversation_id, $user_id]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?? [];
    } catch (Exception $e) {
        error_log('getConversationMessages error: ' . $e->getMessage());
        return [];
    }
}

/**
 * Récupère les conversations d'un utilisateur
 * @param PDO $pdo
 * @param string $user_id
 * @return array
 */
function getUserConversations(PDO $pdo, string $user_id): array {
    try {
        $stmt = $pdo->prepare("
            SELECT 
                c.id,
                c.article_id,
                c.created_at,
                a.titre as article_titre,
                CASE 
                    WHEN c.acheteur_id = ? THEN CONCAT(u_v.prenom, ' ', u_v.nom)
                    ELSE CONCAT(u_a.prenom, ' ', u_a.nom)
                END as autre_utilisateur_nom,
                CASE 
                    WHEN c.acheteur_id = ? THEN u_v.id
                    ELSE u_a.id
                END as autre_utilisateur_id,
                (SELECT COUNT(*) FROM messages WHERE conversation_id = c.id AND lu = 0 AND expediteur_id != ?) as non_lus,
                (SELECT contenu FROM messages WHERE conversation_id = c.id ORDER BY date_envoi DESC LIMIT 1) as dernier_message,
                (SELECT date_envoi FROM messages WHERE conversation_id = c.id ORDER BY date_envoi DESC LIMIT 1) as date_dernier_message
            FROM conversations c
            LEFT JOIN articles a ON c.article_id = a.id
            JOIN users u_v ON c.vendeur_id = u_v.id
            JOIN users u_a ON c.acheteur_id = u_a.id
            WHERE c.acheteur_id = ? OR c.vendeur_id = ?
            ORDER BY date_dernier_message DESC, c.created_at DESC
        ");
        $stmt->execute([$user_id, $user_id, $user_id, $user_id, $user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?? [];
    } catch (Exception $e) {
        error_log('getUserConversations error: ' . $e->getMessage());
        return [];
    }
}

/**
 * Récupère les détails d'une conversation
 * @param PDO $pdo
 * @param string $conversation_id
 * @return array|null
 */
function getConversationDetails(PDO $pdo, string $conversation_id): ?array {
    try {
        $stmt = $pdo->prepare("
            SELECT 
                c.*,
                a.titre as article_titre,
                a.prix as article_prix,
                u_v.prenom as vendeur_prenom,
                u_v.nom as vendeur_nom,
                u_v.email as vendeur_email,
                u_v.telephone as vendeur_telephone,
                u_a.prenom as acheteur_prenom,
                u_a.nom as acheteur_nom,
                u_a.email as acheteur_email
            FROM conversations c
            LEFT JOIN articles a ON c.article_id = a.id
            JOIN users u_v ON c.vendeur_id = u_v.id
            JOIN users u_a ON c.acheteur_id = u_a.id
            WHERE c.id = ?
        ");
        $stmt->execute([$conversation_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result === false ? null : $result;
    } catch (Exception $e) {
        error_log('getConversationDetails error: ' . $e->getMessage());
        return null;
    }
}

/**
 * Compte les messages non lus d'un utilisateur
 * @param PDO $pdo
 * @param string $user_id
 * @return int
 */
function countUnreadMessages(PDO $pdo, string $user_id): int {
    try {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as total
            FROM messages m
            JOIN conversations c ON m.conversation_id = c.id
            WHERE m.lu = 0 
            AND m.expediteur_id != ?
            AND (c.acheteur_id = ? OR c.vendeur_id = ?)
        ");
        $stmt->execute([$user_id, $user_id, $user_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($result['total'] ?? 0);
    } catch (Exception $e) {
        error_log('countUnreadMessages error: ' . $e->getMessage());
        return 0;
    }
}
?>

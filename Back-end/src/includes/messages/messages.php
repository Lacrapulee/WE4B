<?php

require_once __DIR__ . '/../messages_functions.php';

// Traiter l'envoi d'un message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    if (!isset($_SESSION['user_id'])) {
        header('Location: /routeur.php?action=connexion');
        exit;
    }

    $conversation_id = $_POST['conversation_id'] ?? '';
    $contenu = trim($_POST['contenu'] ?? '');

    if (empty($conversation_id) || empty($contenu)) {
        $_SESSION['error'] = 'Erreur: message vide ou conversation invalide';
        header('Location: /routeur.php?action=messages&id=' . $conversation_id);
        exit;
    }

    // Vérifier que l'utilisateur fait partie de cette conversation
    $stmt = $pdo->prepare("SELECT * FROM conversations WHERE id = ? AND (acheteur_id = ? OR vendeur_id = ?)");
    $stmt->execute([$conversation_id, $_SESSION['user_id'], $_SESSION['user_id']]);
    $conversation = $stmt->fetch();

    if (!$conversation) {
        $_SESSION['error'] = 'Erreur: accès non autorisé à cette conversation';
        header('Location: /routeur.php?action=messages');
        exit;
    }

    if (sendMessage($pdo, $conversation_id, $_SESSION['user_id'], $contenu)) {
        $_SESSION['success'] = 'Message envoyé';
    } else {
        $_SESSION['error'] = 'Erreur lors de l\'envoi du message';
    }

    header('Location: /routeur.php?action=messages&id=' . $conversation_id);
    exit;
}

// Créer une nouvelle conversation et envoyer le premier message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact_vendeur'])) {
    if (!isset($_SESSION['user_id'])) {
        header('Location: /routeur.php?action=connexion');
        exit;
    }

    $article_id = (int)($_POST['article_id'] ?? 0);
    $vendor_id = $_POST['vendor_id'] ?? ''; // Contact direct au vendeur depuis son profil
    $contenu = trim($_POST['contenu'] ?? '');

    if (empty($contenu)) {
        $_SESSION['error'] = 'Erreur: message vide';
        header('Location: /routeur.php?action=catalogue');
        exit;
    }

    // Déterminer le vendeur et l'article
    if ($article_id) {
        // Contact via un article
        $stmt = $pdo->prepare("SELECT vendeur_id FROM articles WHERE id = ?");
        $stmt->execute([$article_id]);
        $article = $stmt->fetch();

        if (!$article) {
            $_SESSION['error'] = 'Article non trouvé';
            header('Location: /routeur.php?action=catalogue');
            exit;
        }

        $vendeur_id = $article['vendeur_id'];
        $redirect_location = '/routeur.php?action=item&id=' . $article_id;
    } elseif ($vendor_id) {
        // Contact direct au vendeur
        $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ?");
        $stmt->execute([$vendor_id]);
        $user = $stmt->fetch();

        if (!$user) {
            $_SESSION['error'] = 'Vendeur non trouvé';
            header('Location: /routeur.php?action=catalogue');
            exit;
        }

        $vendeur_id = $vendor_id;
        $article_id = null;
        $redirect_location = '/routeur.php?action=user&id=' . $vendor_id;
    } else {
        $_SESSION['error'] = 'Erreur: article ou vendeur invalide';
        header('Location: /routeur.php?action=catalogue');
        exit;
    }

    // Ne pas permettre au vendeur de se contacter lui-même
    if ($vendeur_id === $_SESSION['user_id']) {
        $_SESSION['error'] = 'Vous ne pouvez pas vous contacter vous-même';
        header('Location: ' . $redirect_location);
        exit;
    }

    $conversation_id = getOrCreateConversation($pdo, $article_id, $_SESSION['user_id'], $vendeur_id);

    // Envoyer le premier message
    if (sendMessage($pdo, $conversation_id, $_SESSION['user_id'], $contenu)) {
        $_SESSION['success'] = 'Conversation créée et message envoyé';
        header('Location: /routeur.php?action=messages&id=' . $conversation_id);
    } else {
        $_SESSION['error'] = 'Erreur lors de la création de la conversation';
        header('Location: ' . $redirect_location);
    }
    exit;
}
?>

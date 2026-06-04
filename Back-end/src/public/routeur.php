<?php

session_start(); 
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/favoris_functions.php';

function ensureVentesSchema(PDO $pdo): void {
    try {
        $columnCheck = $pdo->prepare(
            "SELECT COUNT(*)
             FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'ventes'
               AND COLUMN_NAME = 'acheteur_id'"
        );
        $columnCheck->execute();

        if ((int) $columnCheck->fetchColumn() === 0) {
            $pdo->exec("ALTER TABLE ventes ADD COLUMN acheteur_id char(36) DEFAULT NULL AFTER vendeur_id");
        }
    } catch (Throwable $e) {
        error_log('ensureVentesSchema failed: ' . $e->getMessage());
    }
}

ensureVentesSchema($pdo);

$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($action) {
        case 'favoris_ajax':
            if (!isset($_SESSION['user_id'])) {
                http_response_code(401);
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Unauthorized']);
                exit();
            }

            $articleId = (int) ($_POST['article_id'] ?? 0);
            $favorisAction = $_POST['action'] ?? '';

            if (!$articleId) {
                http_response_code(400);
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Invalid article_id']);
                exit();
            }

            header('Content-Type: application/json');

            switch ($favorisAction) {
                case 'add':
                    addFavoris($pdo, $_SESSION['user_id'], $articleId);
                    echo json_encode(['success' => true, 'message' => 'Article ajouté aux favoris']);
                    break;

                case 'remove':
                    removeFavoris($pdo, $_SESSION['user_id'], $articleId);
                    echo json_encode(['success' => true, 'message' => 'Article retiré des favoris']);
                    break;

                case 'check':
                    $isFavoris = isFavoris($pdo, $_SESSION['user_id'], $articleId);
                    echo json_encode(['is_favoris' => $isFavoris]);
                    break;

                default:
                    http_response_code(400);
                    echo json_encode(['error' => 'Invalid action']);
            }
            exit();
        
        case 'connexion':
            require_once __DIR__ . '/../includes/connexion/connexion.php';
            break;
        case 'delete_item':
            require_once __DIR__ . '/../includes/delete_item/delete_item.php';
            header('Location: /routeur.php?action=catalogue');
            break;
        case 'delete_user':
            require_once __DIR__ . '/../includes/delete_user/delete_user.php';
            header('Location: /routeur.php?action=catalogue');
            break;
        case 'inscription':
            require_once __DIR__ . '/../includes/inscription/inscription.php';
            break;
        case 'post':  
            require_once __DIR__ . '/../includes/post/post.php';
            header('Location: /routeur.php?action=item&id=' . $nouvelArticleId); // Redirige vers la page de l'article nouvellement créé
            break;
        case 'edit_profile':
            require_once __DIR__ . '/../includes/edit_profile/edit_profile.php';
            header('Location: /routeur.php?action=user&id=' . $user_id); // Redirige vers la page de profil après modification
            break;
        case 'paiement':
            // Traite le paiement puis affiche le résultat sur la page de paiement
            require_once __DIR__ . '/../includes/paiement/paiement.php';
            require_once __DIR__ . '/../templates/header.php';
            require_once __DIR__ . '/../templates/paiement/index.php';
            require_once __DIR__ . '/../templates/footer.php';
            break;
        case 'edit_item':
            require_once __DIR__ . '/../includes/edit_item/edit_item.php'; // Logique de mise à jour de l'article
            header('Location: /routeur.php?action=item&id=' . $_POST['article_id']); // Redirige vers la page de l'article après modification
            break;
        case 'avis':
            require_once __DIR__ . '/../includes/avis.php';
            break;
        case 'messages':
            require_once __DIR__ . '/../includes/messages/messages.php';
            break;

        case 'valider_reception':
            $venteId = $_POST['vente_id'];
            $stmt = $pdo->prepare("UPDATE ventes SET statut = 'recu' WHERE id = ?");
            $stmt->execute([$venteId]);
            header('Location: /routeur.php?action=mes_commandes&success=recu');
            exit();
            break;
        case 'valider_reception':
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['vente_id'])) {
                $venteId = $_POST['vente_id'];
                
                $stmt = $pdo->prepare("UPDATE ventes SET statut = 'recu' WHERE id = ? AND acheteur_id = ?");
                $stmt->execute([$venteId, $_SESSION['user_id']]);
                
                // Redirection vers le formulaire d'avis
                $check = $pdo->prepare("SELECT vendeur_id, article_id FROM ventes WHERE id = ?");
                $check->execute([$venteId]);
                $info = $check->fetch();
                
                header("Location: /routeur.php?action=avis&vendeur_id=".$info['vendeur_id']."&article_id=".$info['article_id']);
                exit();
            }
            break;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    switch ($action) {
        case 'user':
            require_once __DIR__ . '/../includes/user/user.php';
            include __DIR__ . '/../templates/user/index.php';
            break;
        case 'avis':
            require_once __DIR__ . '/../templates/avis/index.php';
            break;
        case 'avis_form':
            // Alias rétrocompatible pour les anciens liens
            require_once __DIR__ . '/../templates/avis/index.php';
            break;
        case 'myarticle':
            if (isset($_SESSION['user_id'])) {
                header('Location: /routeur.php?action=user&id=' . $_SESSION['user_id']);
            } else {
                header('Location: /routeur.php?action=auth');
            }
            exit();
        case 'catalogue':
            require_once __DIR__ . '/../includes/catalogue/catalogue.php';
            require_once __DIR__ . '/../templates/catalogue/index.php';
            break;

        case 'favoris':
            require_once __DIR__ . '/../includes/favoris/favoris.php';
            require_once __DIR__ . '/../templates/favoris/index.php';
            break;

        case 'deconnexion':
            session_destroy();
            header('Location: /routeur.php?action=catalogue');
            exit();
            break;

        case 'edit_profile':
            require_once __DIR__ . '/../includes/edit_profile/edit_profile.php';
            
            include __DIR__ . '/../templates/header.php';
            include __DIR__ . '/../templates/edit_profile/index.php'; // Affiche la vue
            include __DIR__ . '/../templates/footer.php';
            break;
        // ... dans ton switch ($action) au niveau du GET
        case 'item':
            // 1. Charger la logique (Définit les variables $product, $allImages, etc.)
            require_once __DIR__ . '/../includes/item/item.php';
            
            // 2. Charger les éléments de vue
            require_once __DIR__ . '/../templates/header.php'; // Ton header Tailwind
            require_once __DIR__ . '/../templates/items/index.php'; // Le contenu qu'on vient de créer
            require_once __DIR__ . '/../templates/footer.php';
            break;

        case 'auth':
            require_once __DIR__ . '/../templates/header.php'; // Ton header Tailwind
            require_once __DIR__ . '/../templates/connexion/index.php'; // Le contenu de la page de connexion
            require_once __DIR__ . '/../templates/footer.php';
            break;

        case 'inscription':
            require_once __DIR__ . '/../templates/header.php'; // Ton header Tailwind
            require_once __DIR__ . '/../templates/inscription/index.php'; // Le contenu de la page d'inscription
            require_once __DIR__ . '/../templates/footer.php';
            break;
        
        case 'post':
            if (!isset($_SESSION['user_id'])) {
                header('Location: /routeur.php?action=auth');
                exit();
            }
            require_once __DIR__ . '/../templates/header.php'; // Ton header Tailwind
            require_once __DIR__ . '/../templates/post/index.php'; // Le contenu de la page de publication d'annonce
            require_once __DIR__ . '/../templates/footer.php';
            break;

        case 'paiement':
            if (!isset($_SESSION['user_id'])) {
                header('Location: /routeur.php?action=auth');
                exit();
            }
            require_once __DIR__ . '/../includes/paiement/paiement.php';        
            
            // Logique de paiement (à implémenter)
            require_once __DIR__ . '/../templates/header.php'; // Ton header Tailwind
            require_once __DIR__ . '/../templates/paiement/index.php'; // Le contenu de la page de paiement
            require_once __DIR__ . '/../templates/footer.php';
            break;
        case 'mes_commandes':
            // Affiche les commandes de l'utilisateur connecté
            if (!isset($_SESSION['user_id'])) {
                header('Location: /routeur.php?action=auth');
                exit();
            }
            require_once __DIR__ . '/../includes/db.php';
            require_once __DIR__ . '/../templates/header.php';
            require_once __DIR__ . '/../templates/mes_commandes/index.php';
            require_once __DIR__ . '/../templates/footer.php';
            break;
        case 'edit_item':
            require_once __DIR__ . '/../includes/item/item.php'; // Récupère les données de l'article
            require_once __DIR__ . '/../templates/header.php'; // Ton header Tailwind
            require_once __DIR__ . '/../templates/edit_item/index.php'; // Le contenu de la page d'édition d'article
            require_once __DIR__ . '/../templates/footer.php';
            break;
        case 'messages':
            if (!isset($_SESSION['user_id'])) {
                header('Location: /routeur.php?action=connexion');
                exit;
            }
            require_once __DIR__ . '/../templates/header.php';
            require_once __DIR__ . '/../templates/messages/index.php';
            require_once __DIR__ . '/../templates/footer.php';
            break;
    }
}



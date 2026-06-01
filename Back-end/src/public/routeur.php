<?php

session_start(); 

// En-têtes CORS pour communiquer avec Angular
header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

// Gérer la requête de pré-vérification (Preflight) des navigateurs
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

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

// ==========================================
// MÉTHODE POST : TRAITEMENT DES ACTIONS
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($action) {
        case 'favoris_ajax':
            if (!isset($_SESSION['user_id'])) {
                http_response_code(401);
                echo json_encode(['error' => 'Unauthorized']);
                exit();
            }

            $articleId = (int) ($_POST['article_id'] ?? 0);
            $favorisAction = $_POST['action'] ?? '';

            if (!$articleId) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid article_id']);
                exit();
            }

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
            // À modifier plus tard pour renvoyer du JSON
            break;

        case 'delete_item':
            require_once __DIR__ . '/../includes/delete_item/delete_item.php';
            echo json_encode(['success' => true, 'message' => 'Article supprimé']);
            exit();

        case 'delete_user':
            require_once __DIR__ . '/../includes/delete_user/delete_user.php';
            echo json_encode(['success' => true, 'message' => 'Utilisateur supprimé']);
            exit();

        case 'inscription':
            require_once __DIR__ . '/../includes/inscription/inscription.php';
            break;

        case 'post':  
            require_once __DIR__ . '/../includes/post/post.php';
            echo json_encode(['success' => true, 'article_id' => $nouvelArticleId]);
            exit();

        case 'edit_profile':
            require_once __DIR__ . '/../includes/edit_profile/edit_profile.php';
            echo json_encode(['success' => true, 'user_id' => $user_id]);
            exit();

        case 'paiement':
            require_once __DIR__ . '/../includes/paiement/paiement.php';
            echo json_encode(['success' => true, 'message' => 'Paiement traité']);
            exit();

        case 'edit_item':
            require_once __DIR__ . '/../includes/edit_item/edit_item.php';
            echo json_encode(['success' => true, 'article_id' => $_POST['article_id']]);
            exit();

        case 'avis':
            require_once __DIR__ . '/../includes/avis.php';
            break;

        case 'messages':
            require_once __DIR__ . '/../includes/messages/messages.php';
            break;

        case 'valider_reception':
            if (isset($_POST['vente_id'])) {
                $venteId = $_POST['vente_id'];
                $stmt = $pdo->prepare("UPDATE ventes SET statut = 'recu' WHERE id = ? AND acheteur_id = ?");
                $stmt->execute([$venteId, $_SESSION['user_id']]);
                
                $check = $pdo->prepare("SELECT vendeur_id, article_id FROM ventes WHERE id = ?");
                $check->execute([$venteId]);
                $info = $check->fetch();
                
                echo json_encode([
                    'success' => true, 
                    'vendeur_id' => $info['vendeur_id'], 
                    'article_id' => $info['article_id']
                ]);
                exit();
            }
            break;
    }
}

// ==========================================
// MÉTHODE GET : RÉCUPÉRATION DES DONNÉES
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    switch ($action) {

        // ICI TEST POUR L'API CATALOGUE
        case 'catalogue':
            // 1. On charge la logique (qui définit $results et $categories)
            require_once __DIR__ . '/../includes/catalogue/catalogue.php';
            
            // 2. On package le tout proprement dans un tableau associatif
            $response = [
                'articles'   => $results ?? [],
                'categories' => $categories ?? []
            ];

            // 3. On l'envoie à Angular
            http_response_code(200); // Statut HTTP OK
            echo json_encode($response);
            exit(); // On coupe pour ne rien charger d'autre
        
        case 'item':
            require_once __DIR__ . '/../includes/item/item.php';
            // À nettoyer ensuite : echo json_encode($product);
            break;

        case 'user':
            require_once __DIR__ . '/../includes/user/user.php';
            break;

        case 'messages':
            if (!isset($_SESSION['user_id'])) {
                http_response_code(401);
                echo json_encode(['error' => 'Unauthorized']);
                exit();
            }
            // À nettoyer ensuite
            break;
            
        default:
            http_response_code(404);
            echo json_encode(['error' => 'Endpoint not found']);
            exit();
    }
}
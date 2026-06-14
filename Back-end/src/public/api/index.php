<?php

// 1. CONFIGURATION DE LA SESSION (Avant session_start)
ini_set('session.cookie_samesite', 'Lax'); 
ini_set('session.cookie_secure', '0'); 
ini_set('session.cookie_httponly', '1'); 

session_start(); 

// 2. EN-TÊTES CORS
header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Credentials: true"); 
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// 3. INCLUSIONS ET INITIALISATION
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../includes/db.php'; 
require_once __DIR__ . '/../../includes/mongo.php';

use MongoDB\BSON\UTCDateTime; // Import pour gérer les dates MongoDB

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';
$inputData = json_decode(file_get_contents('php://input'), true) ?? [];

// 4. VÉRIFICATION DE L'AUTHENTIFICATION
$actionsPubliques = [
    'connexion',
    'inscription',
    'catalogue',
    'item'
];

if (!in_array($action, $actionsPubliques)) {
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401); 
        echo json_encode([
            'success' => false, 
            'error' => 'Authentification requise. Veuillez vous connecter.'
        ]);
        // --- LOG EN CAS D'ÉCHEC D'AUTHENTIFICATION ---
        include __DIR__ . '/../../includes/save_log.php';
        exit(); 
    }
}

// ==========================================
// ROUTAGE DE L'API
// ==========================================

switch ($method) {

    case 'GET':
        switch ($action) {
            case 'conversations':
                require_once __DIR__ . '/../../includes/conversations/conversations.php';
                $response = [
                    'messages'      => $messages ?? []
                ];
                http_response_code(200);
                echo json_encode($response);
                break;
            case 'messages':
                require_once __DIR__ . '/../../includes/messages/messages.php';
                $response = [
                    'messages' => $messages ?? []
                ];
                http_response_code(200);
                echo json_encode($response); 
                break;
                
            case 'catalogue':
                require_once __DIR__ . '/../../includes/catalogue/catalogue.php';
                $response = [
                    'articles'   => $results ?? [],
                    'categories' => $categories ?? []
                ];
                http_response_code(200);
                echo json_encode($response);
                break;

           case 'item':
                require_once __DIR__ . '/../../includes/item/item.php';
                $response = [
                    'item' => [
                        'id'          => (int)$product['id'],
                        'titre'       => (string)$product['titre'],
                        'prix'        => (float)$product['prix'],
                        'description' => (string)($product['description'] ?? ''),
                        'statut'      => (string)($product['statut'] ?? ''),
                        'categorie'   => (int)($product['categorie_id'] ?? 0),
                        'vendeur_id'  => (string)($product['vendeur_id'] ?? '')
                    ],
                    'images'     => $allImages ?? ['default.png'],
                    'similarAds' => $similarAds ?? [],
                    'isOwner'    => (bool)$isOwner,
                    'isAdmin'    => (bool)$isAdmin
                ];
                http_response_code(200);
                echo json_encode($response);
                break;

            case 'user':
                require_once __DIR__ . '/../../includes/user/user.php';
                $response = [
                    'user' => [
                        'id'              => $user['id'],
                        'nom'             => $user['nom'],
                        'prenom'          => $user['prenom'],
                        'telephone'       => $user['telephone'],
                        'email'           => $user['email'],
                        'adresse_postale' => $user['adresse_postale'],
                        'created_at'      => $user['created_at']
                    ],
                    'articles' => $articles ?? [],
                    'reviews'  => $reviews ?? [],
                    'isOwner'  => (bool)$is_owner,
                    'isAdmin'  => (bool)$isAdmin
                ];
                http_response_code(200);
                echo json_encode($response);
                break;

            case 'favoris':
                require_once __DIR__ . '/../../includes/favoris/favoris.php';
                $response = [
                    'favoris' => $favoris ?? [],
                    'images'  => $images ?? []
                ];
                http_response_code(200);
                echo json_encode($response);
                break;
                
            default:
                http_response_code(404);
                echo json_encode(['error' => 'Endpoint GET introuvable']);
        }
        break;

    case 'POST':
        switch ($action) {
            case 'post_message':
                include __DIR__ . '/../../includes/post_message/post_message.php'; 
                
                http_response_code(201); 
                echo json_encode(['success' => empty($erreurs), 'message' => $erreurs ?? 'Message envoyé']);
                break;

            case 'connexion':
                $_POST['email'] = $inputData['email'] ?? ''; 
                $_POST['password'] = $inputData['password'] ?? '';
                include __DIR__ . '/../../includes/connexion/connexion.php'; 
                
                http_response_code(200);
                echo json_encode([
                    'success' => empty($erreurs), 
                    'message' => $erreurs ?? 'Connexion réussie', 
                    'user_id' => $_SESSION['user_id'] ?? null 
                ]);
                break;

            case 'inscription':
                $_POST['email'] = $inputData['email'] ?? '';
                $_POST['password'] = $inputData['password'] ?? '';
                $_POST['nom'] = $inputData['nom'] ?? '';
                $_POST['prenom'] = $inputData['prenom'] ?? '';
                $_POST['confirm_password'] = $inputData['confirm_password'] ?? '';
                $_POST['telephone'] = $inputData['telephone'] ?? null;
                $_POST['date_naissance'] = $inputData['date_naissance'] ?? null;
                $_POST['adresse_postale'] = $inputData['adresse_postale'] ?? null;
                include __DIR__ . '/../../includes/inscription/inscription.php'; 
                
                http_response_code(201); 
                echo json_encode(['success' => empty($erreurs), 'message' => $erreurs ?? 'Utilisateur créé']);
                break;

            case 'post_item':
                $_POST['vendeur_id'] = $_SESSION['user_id'] ?? null; 
                include __DIR__ . '/../../includes/post/post.php'; 
                
                if (empty($erreurs) && !empty($nouvelArticleId)) {
                    http_response_code(201);
                    echo json_encode(['success' => true, 'article_id' => $nouvelArticleId, 'message' => 'Article publié avec succès']);
                } else {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'errors' => $erreurs]);
                }
                break;

            case 'favoris':
                $_POST['article_id'] = $inputData['article_id'] ?? null;
                $_POST['user_id'] = $inputData['user_id'] ?? null;
                include __DIR__ . '/../../includes/add_Favoris/add_Favoris.php'; 
                
                http_response_code(200);
                echo json_encode([
                    'success' => empty($erreurs), 
                    'errors' => $erreurs ?? [], 
                    'message' => $response['message'] ?? ''
                ]);
                break;

            default:
                http_response_code(404);
                echo json_encode(['error' => 'Endpoint POST introuvable']);
        }
        break;

    case 'PUT':
        switch ($action) {
            case 'edit_profile':
                if($_SESSION['user_id'] != ($inputData['id'] ?? 0) && !($_SESSION['is_admin'] ?? false)) {
                    http_response_code(403); 
                    echo json_encode(['success' => false, 'error' => 'Vous n\'avez pas la permission de modifier ce profil']);
                    include __DIR__ . '/save_log.php'; // On log l'erreur 403
                    exit();
                }
                $_POST = $inputData; 
                include __DIR__ . '/../../includes/edit_profile/edit_profile.php'; 
                
                http_response_code(200);
                echo json_encode(['success' => true, 'message' => 'Profil mis à jour']);
                break;

            case 'edit_item':
                if($_SESSION['user_id'] != ($inputData['vendeur_id'] ?? 0) && !($_SESSION['is_admin'] ?? false)) {
                    http_response_code(403); 
                    echo json_encode(['success' => false, 'error' => 'Vous n\'avez pas la permission de modifier cet article']);
                    include __DIR__ . '/save_log.php'; // On log l'erreur 403
                    exit();
                }
                $_POST = $inputData;
                include __DIR__ . '/../../includes/edit_item/edit_item.php';
                http_response_code(200);
                echo json_encode(['success' => true, 'message' => 'Article modifié']);
                break;

            default:
                http_response_code(404);
                echo json_encode(['error' => 'Endpoint PUT introuvable']);
        }
        break;

    case 'DELETE':
        switch ($action) {
            case 'delete_item':
                if($_SESSION['user_id'] != ($inputData['vendeur_id'] ?? 0)) {
                    http_response_code(403); 
                    echo json_encode(['success' => false, 'error' => 'Vous n\'avez pas la permission de supprimer cet article']);
                    include __DIR__ . '/save_log.php'; // On log l'erreur 403
                    exit();
                }                
                $_GET['id'] = $inputData['id'] ?? null; 
                include __DIR__ . '/../../includes/delete_item/delete_item.php';
                http_response_code(200);
                echo json_encode(['success' => true, 'message' => 'Article supprimé']);
                break;

            case 'delete_user':
                $_GET['id'] = $inputData['id'] ?? null; 
                include __DIR__ . '/../../includes/delete_user/delete_user.php';
                http_response_code(200);
                echo json_encode(['success' => true, 'message' => 'Compte supprimé']);
                break;

            case 'favoris':
                $_POST['article_id'] = $inputData['article_id'] ?? null;
                $_POST['id_user'] = $inputData['user_id'] ?? null;
                include __DIR__ . '/../../includes/delete_favoris/delete_favoris.php';
                http_response_code(200);
                if (isset($result) && $result) {
                    echo json_encode(['success' => true, 'message' => 'Retiré des favoris']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Erreur lors de la suppression des favoris']);
                }
                break;

            default:
                http_response_code(404);
                echo json_encode(['error' => 'Endpoint DELETE introuvable']);
        }
        break;

    default:
        http_response_code(405); 
        echo json_encode(['error' => 'Méthode HTTP non supportée']);
        break;
}

// ==========================================
// 5. ENREGISTREMENT DU LOG DANS MONGO DB
// ==========================================
include __DIR__ . '/../../includes/save_log.php';

exit();
<?php

// 1. CONFIGURATION DE LA SESSION (Avant session_start)
ini_set('session.cookie_samesite', 'Lax'); 
ini_set('session.cookie_secure', '0'); 
ini_set('session.cookie_httponly', '1'); 
ini_set('session.cookie_domain', 'localhost');
session_name('MYAPP_SESSION');

session_start(); 

// DEBUG - à supprimer après
error_log('ACTION: ' . ($_GET['action'] ?? 'none'));
error_log('SESSION USER_ID: ' . ($_SESSION['user_id'] ?? 'NOT SET'));
error_log('SESSION CONTENT: ' . json_encode($_SESSION));

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
                $path = __DIR__ . '/../../includes/favoris/favoris.php';
                if (!file_exists($path)) {
                    echo json_encode(['error' => 'fichier introuvable: ' . $path]);
                    break;
                }
                require_once $path;
                
                // Nettoyer les champs binaires non-UTF8
                $favorisClean = array_map(function($item) {
                    unset($item['coordonnees']);
                    return $item;
                }, $favoris ?? []);
                
                echo json_encode([
                    'favoris' => $favorisClean,
                    'images'  => $images ?? []
                ]);
                break;

            case 'check_auth':
                if (isset($_SESSION['user_id'])) {
                    http_response_code(200);
                    echo json_encode(['isLoggedIn' => true, 'user_id' => $_SESSION['user_id']]);
                } else {
                    http_response_code(200);
                    echo json_encode(['isLoggedIn' => false]);
                }
                break;

            case 'mes_commandes':
                require_once __DIR__ . '/../../includes/mes_commandes/mes_commandes.php';
                http_response_code(200);
                echo json_encode([
                    'commandes' => $commandes ?? [],
                    'images' => $imagesByCommande ?? []
                ]);
                break;

            case 'paiement':
                $_GET['id'] = $_GET['id'] ?? null;
                require_once __DIR__ . '/../../includes/paiement/paiement.php';
                http_response_code(200);
                // Supprimer le champ binaire non sérialisable
                if (isset($viewData['product']['coordonnees'])) {
                    unset($viewData['product']['coordonnees']);
                }
                echo json_encode($viewData);
                break;

            case 'logout':
                session_unset();
                session_destroy();
                http_response_code(200);
                echo json_encode(['success' => true, 'message' => 'Déconnecté']);
                break;
                
            default:
                http_response_code(404);
                echo json_encode(['error' => 'Endpoint GET introuvable']);
        }
        break;

    case 'POST':
        switch ($action) {
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

            case 'paiement':
                $_POST = $inputData;
                $_POST['confirm_payment'] = true;
                $_SERVER['REQUEST_METHOD'] = 'POST';
                require_once __DIR__ . '/../../includes/paiement/paiement.php';
                if ($viewData['statusCode'] === 200 && !empty($viewData['successMessage'])) {
                    http_response_code(200);
                    echo json_encode(['success' => true, 'reference' => $viewData['orderReference'], 'message' => $viewData['successMessage']]);
                } else {
                    http_response_code($viewData['statusCode']);
                    echo json_encode(['success' => false, 'error' => $viewData['errorMessage']]);
                }
                break;

            case 'avis':
                $dest_id = $inputData['destinataire_id'] ?? null;
                $note = intval($inputData['note'] ?? 0);
                $commentaire = trim($inputData['commentaire'] ?? '');
                $article_id = $inputData['article_id'] ?? null;
                
                if (!$dest_id || $note < 1 || $note > 5 || $commentaire === '') {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'error' => 'Données invalides']);
                    break;
                }
                
                if ($_SESSION['user_id'] == $dest_id) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'error' => 'Vous ne pouvez pas vous noter vous-même']);
                    break;
                }
                
                $stmt = $pdo->prepare("SELECT id FROM avis WHERE expediteur_id = ? AND destinataire_id = ?");
                $stmt->execute([$_SESSION['user_id'], $dest_id]);
                if ($stmt->fetch()) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'error' => 'Vous avez déjà laissé un avis']);
                    break;
                }
                
                $stmt = $pdo->prepare("INSERT INTO avis (article_id, expediteur_id, destinataire_id, note, commentaire, date_avis) VALUES (?, ?, ?, ?, ?, NOW())");
                $stmt->execute([$article_id, $_SESSION['user_id'], $dest_id, $note, $commentaire]);
                http_response_code(200);
                echo json_encode(['success' => true, 'message' => 'Avis ajouté avec succès']);
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

                $titre = $inputData['titre'] ?? $product['titre'];
                $description = $inputData['description'] ?? $product['description'];
                $prix = $inputData['prix'] ?? $product['prix'];
                $categorie_id = $inputData['categorie_id'] ?? $product['categorie_id'];
                $statut = $inputData['statut'] ?? $product['statut'];

                $stmt = $pdo->prepare("UPDATE articles SET titre = ?, description = ?, prix = ?, categorie_id = ?, statut = ? WHERE id = ?");
                $stmt->execute([$titre, $description, $prix, $categorie_id, $statut, $productId]);

                http_response_code(200);
                echo json_encode(['success' => true, 'message' => 'Article modifié']);
                break;

            case 'commande_recue':
                $vente_id = $inputData['vente_id'] ?? null;
                if ($vente_id && isset($_SESSION['user_id'])) {
                    $stmt = $pdo->prepare("UPDATE ventes SET statut = 'recu' WHERE id = ? AND acheteur_id = ?");
                    $stmt->execute([$vente_id, $_SESSION['user_id']]);
                    http_response_code(200);
                    echo json_encode(['success' => true, 'message' => 'Commande marquée comme reçue']);
                } else {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'error' => 'Erreur lors de la mise à jour']);
                }
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
                        exit();
                    }                
                $_GET['id'] = $inputData['id'] ?? null; //id de l'article à supprimer
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
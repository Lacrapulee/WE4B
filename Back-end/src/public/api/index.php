<?php

// 1. CONFIGURATION DE LA SESSION (Avant session_start)
ini_set('session.cookie_samesite', 'Lax'); 
ini_set('session.cookie_secure', '0'); 
ini_set('session.cookie_httponly', '1'); 
ini_set('session.cookie_domain', 'localhost');
session_name('MYAPP_SESSION');

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
    'item',
    'items',
    'user',
    'get_image',
    'check_auth'
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
                    'message'       => $message ?? '',
                    'result'        => $result ?? []
                ];
                echo json_encode($response);
                break;
            case 'unread_count':
                require_once __DIR__ . '/../../includes/unread_count/unread_count.php';
                $response = [
                    'message' => $message ?? '',
                    'result'  => ['unread_count' => $unread_count ?? 0]
                ];
                http_response_code(200);
                echo json_encode($response);
                break;
            case 'messages':
                require_once __DIR__ . '/../../includes/messages/messages.php';
                $response = [
                    'message' => $message ?? '',
                    'result'  => $chat_history ?? []
                ];
                echo json_encode($response); 
                break;
                
            case 'catalogue':
                require_once __DIR__ . '/../../includes/catalogue/catalogue.php';
                $results = [
                            "annonces" => $results, 
                            "categories" => $categories
                ];

                $response = [    
                'result'   => $results ?? [],
                'message' => 'Récupération réussie'
                ];

                http_response_code(200);
                echo json_encode($response);
                break;

           case 'items':
                require_once __DIR__ . '/../../includes/item/item.php';
                $message = $errorMessage ?? 'Récupération réussie';
                $response = [
                    'result'  => $results ?? [],
                    'message' => $message
                ];
                http_response_code(200);
                echo json_encode($response);
                break;

            case 'user':
                require_once __DIR__ . '/../../includes/user/user.php';
                $response = [
                    'result'  => $results ?? [],
                    'message' => 'Récupération réussie'
                ];
                http_response_code(200);
                echo json_encode($response);
                break;

            case 'favoris':
                $path = __DIR__ . '/../../includes/favoris/favoris.php';  // ← ligne manquante
                if (!file_exists($path)) {
                    echo json_encode(['error' => 'fichier introuvable: ' . $path]);
                    break;
                }
                require_once $path;
                
                $favorisClean = array_map(function($item) {
                    unset($item['coordonnees']);
                    return $item;
                }, $favoris ?? []);
                
                echo json_encode([
                    'result' => [
                        'favoris' => $favorisClean,
                        'images'  => $images ?? []
                    ],
                    'message' => 'Récupération réussie'
                ]);
                break;

            case 'check_auth':
                if (isset($_SESSION['user_id'])) {
                    http_response_code(200);
                    echo json_encode([
                        'isLoggedIn' => true,
                        'user_id' => $_SESSION['user_id'],
                        'is_admin' => isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1
                    ]);
                } else {
                    http_response_code(200);
                    echo json_encode(['isLoggedIn' => false]);
                }
                break;

            case 'mes_commandes':
                require_once __DIR__ . '/../../includes/mes_commandes/mes_commandes.php';
                $response = [
                    'result'  => [
                        'commandes' => $commandes ?? [],
                        'images' => $imagesByCommande ?? []
                    ],
                    'message' => 'Récupération réussie'
                ];
                http_response_code(200);
                echo json_encode($response);
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

            case 'get_image':
                $id = $_GET['id'] ?? null;
                if (!$id || !$imageCollection){
                    http_response_code(404);
                    echo json_decode(['error'=> 'Image introuvable']);
                    break;
                }
            try {
                //On récupère le document par son ObjectId
                $image = $imageCollection->findOne(['_id' => new MongoDB\BSON\ObjectId($id)]);
                if (!$image) {
                    http_response_code(404);
                    exit();
                }
                // On renvoie le binaire avec le bon type MIME
                header("Content-Type: " . $image['mime_type']);
                echo $image['data']->getData();
                exit();
            }    catch (Exception $e){
                    http_response_code(500);
                    exit();
            }
                break;

            case 'admin_users':
                if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
                    http_response_code(403);
                    echo json_encode(['success' => false, 'error' => 'Accès interdit.']);
                    exit();
                }
                $stmt = $pdo->prepare("SELECT id, nom, prenom, email, telephone, is_admin, created_at FROM users WHERE email NOT LIKE 'supprime_%'");
                $stmt->execute();
                $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode(['success' => true, 'result' => $users]);
                break;

            case 'admin_items':
                if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
                    http_response_code(403);
                    echo json_encode(['success' => false, 'error' => 'Accès interdit.']);
                    exit();
                }
                $stmt = $pdo->prepare("
                    SELECT a.id, a.titre, a.prix, a.statut, a.created_at, c.nom AS categorie_nom, u.prenom AS vendeur_prenom, u.nom AS vendeur_nom 
                    FROM articles a
                    LEFT JOIN categories c ON a.categorie_id = c.id
                    LEFT JOIN users u ON a.vendeur_id = u.id
                    ORDER BY a.created_at DESC
                ");
                $stmt->execute();
                $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode(['success' => true, 'result' => $items]);
                break;

            case 'unread_count':
                require_once __DIR__ . '/../../includes/unread_count/unread_count.php';
                echo json_encode([
                    'success' => true,
                    'result'  => $unread_count ?? 0,
                    'message' => $message ?? ''
                ]);
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
                
                $response = [
                    'message'       => $message ?? '',
                    'result'        => $result ?? []
                ];
                echo json_encode($response);
                break;

            case 'connexion':
                $_POST['email'] = $inputData['email'] ?? ''; 
                $_POST['password'] = $inputData['password'] ?? '';
                include __DIR__ . '/../../includes/connexion/connexion.php'; 
                
                echo json_encode([
                    'message' => $erreurs ?? 'Connexion réussie', 
                    'result' => $_SESSION['user_id'] ?? null,
                    'is_admin' => isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1
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
                
                echo json_encode(['message' => $erreurs ?? 'Utilisateur créé',
                'result' => $_SESSION['user_id'] ?? null] );
                break;

            case 'post_item':
                $_POST['vendeur_id'] = $_SESSION['user_id'] ?? null; 
                include __DIR__ . '/../../includes/post/post.php'; 
                
                echo json_encode(['message' => $erreurs ?? 'Article publié avec succès', 'result' => $nouvelArticleId ?? null]);
              
                break;

            case 'favoris':
            $_POST['article_id'] = $inputData['article_id'] ?? null;
            $_POST['user_id'] = $inputData['user_id'] ?? null;
            include __DIR__ . '/../../includes/add_Favoris/add_Favoris.php'; 
            
            echo json_encode([
                'success' => $result === true,  // ← manquant !
                'errors' => $error ?? null, 
                'message' => $error ?? ''
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

            case 'upload_image':
                if (!isset($_FILES['image']) || !$imageCollection) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Aucune image reçue']);
                    break;
                }

                $file = $_FILES['image'];
                if ($file['error'] !== UPLOAD_ERR_OK) {
                    http_response_code(500);
                    echo json_encode(['error' => 'Erreur lors de l\'upload']);
                    break;
                }

                try {
                    // Préparation du binaire pour MongoDB
                    $binary = new MongoDB\BSON\Binary(file_get_contents($file['tmp_name']), MongoDB\BSON\Binary::TYPE_GENERIC);

                    $insertResult = $imageCollection->insertOne([
                        'filename'   => $file['name'],
                        'mime_type'  => $file['type'],
                        'size'       => $file['size'],
                        'data'       => $binary,
                        'uploaded_at'=> new MongoDB\BSON\UTCDateTime(),
                        'user_id'    => $_SESSION['user_id'] ?? null
                    ]);

                    echo json_encode([
                        'success' => true,
                        'id'      => (string)$insertResult->getInsertedId(),
                        'message' => 'Image stockée avec succès dans MongoDB'
                    ]);
                } catch (Exception $e) {
                    http_response_code(500);
                    echo json_encode(['error' => $e->getMessage()]);
                }
                break;

            case 'admin_run_dashboard':
                if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
                    http_response_code(403);
                    echo json_encode(['success' => false, 'error' => 'Accès interdit.']);
                    exit();
                }
                $pythonScript = '/var/www/html/dashboard/generate_chart.py';
                $output = shell_exec("python3 $pythonScript 2>&1");
                $success = (trim($output) === "Success");
                echo json_encode([
                    'success' => $success,
                    'message' => $success ? 'Dashboard mis à jour' : 'Erreur de génération du graphique',
                    'error' => !$success ? $output : null
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
                $_POST = $inputData;
                $_GET['id'] = $inputData['id'] ?? ($_GET['id'] ?? null);
                $_SERVER['REQUEST_METHOD'] = 'POST';
                include __DIR__ . '/../../includes/edit_profile/edit_profile.php'; 
                echo json_encode(['success' => $success, 'result' => $result, 'message' => $error ?? 'Profil mis à jour avec succès']);
                break;

            case 'edit_item':
                $productId = $_GET['id'] ?? null;
                if (!$productId) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'ID de l\'annonce manquant']);
                    exit();
                }

                $stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ?");
                $stmt->execute([$productId]);
                $product = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$product) {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'message' => 'Annonce non trouvée']);
                    exit();
                }

                $isOwner = (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $product['vendeur_id']);
                $isAdmin = (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1);

                if (!$isOwner && !$isAdmin) {
                    http_response_code(403); 
                    echo json_encode(['success' => false, 'message' => 'Vous n\'avez pas la permission de modifier cet article']);
                    include __DIR__ . '/../../includes/save_log.php'; // On log l'erreur 403
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
                echo json_encode(['success' => true, 'message' => 'Article modifié avec succès']);
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
                $_GET['id'] = $inputData['id'] ?? null;
                $isAdmin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
                $isOwner = false;
                if ($_GET['id']) {
                    $stmtOwner = $pdo->prepare("SELECT vendeur_id FROM articles WHERE id = ?");
                    $stmtOwner->execute([$_GET['id']]);
                    $vendeurId = $stmtOwner->fetchColumn();
                    $isOwner = (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $vendeurId);
                }
                
                if (!$isAdmin && !$isOwner) {
                    http_response_code(403); 
                    echo json_encode(['success' => false, 'error' => 'Vous n\'avez pas la permission de supprimer cet article']);
                    include __DIR__ . '/save_log.php'; // On log l'erreur 403
                    exit();
                }                
                include __DIR__ . '/../../includes/delete_item/delete_item.php';
                http_response_code(200);
                echo json_encode(['success' => isset($result) && $result === true, 'result' => $result, 'message' => $error ?? 'Article supprimé']);
                break;

            case 'delete_user':
                $_GET['id'] = $inputData['id'] ?? null; 
                include __DIR__ . '/../../includes/delete_user/delete_user.php';
                http_response_code(200);
                echo json_encode(['success' => isset($result) && $result === true, 'result' => $result, 'message' => $error ?? 'Compte supprimé']);
                break;

            case 'favoris':
                $_POST['article_id'] = $inputData['article_id'] ?? null;
                $_POST['id_user'] = $inputData['user_id'] ?? null;
                include __DIR__ . '/../../includes/delete_favoris/delete_favoris.php';
                if (isset($result) && $result) {
                    http_response_code(200);
                    echo json_encode(['success' => true, 'message' => 'Retiré des favoris']);
                } else {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => $error ?? 'Erreur lors de la suppression']);
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
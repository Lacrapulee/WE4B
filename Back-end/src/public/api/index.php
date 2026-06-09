<?php

// 1. CONFIGURATION DE LA SESSION (À faire AVANT session_start)
// En local sur localhost, on n'utilise pas 'None' pour éviter de casser le HTTP
ini_set('session.cookie_samesite', 'Lax'); 
ini_set('session.cookie_secure', '0'); // On reste en '0' car on est en http:// en local
ini_set('session.cookie_httponly', '1'); // Sécurité : empêche le JavaScript d'accéder au cookie

session_start(); 

// 2. EN-TÊTES CORS (Indispensables pour Angular)
header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Credentials: true"); // Autorise l'échange du cookie de session
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");
// Gestion du Preflight (requête OPTIONS automatique du navigateur)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// 2. INCLUSIONS ET INITIALISATION
require_once __DIR__ . '/../../includes/db.php'; // Fournit la variable $pdo

// 3. RÉCUPÉRATION DES DONNÉES ENTRANTES (JSON OU QUERY)
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

// Récupérer le corps de la requête (Body) envoyé par Angular (JSON décodé en tableau PHP)
$inputData = json_decode(file_get_contents('php://input'), true) ?? [];

// 4. VÉRIFICATION DE L'AUTHENTIFICATION

// Liste des actions accessibles TOUT LE MONDE (sans être connecté)
$actionsPubliques = [
    'connexion',
    'inscription',
    'catalogue',
    'item', 
    'user'
];

// Si l'action demandée n'est pas dans la liste publique, on vérifie la session
if (!in_array($action, $actionsPubliques)) {
    
    // Si la session de l'utilisateur n'existe pas
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401); // 401 = Unauthorized (Non autorisé)
        echo json_encode([
            'success' => false, 
            'error' => 'Authentification requise. Veuillez vous connecter.'
        ]);
        exit(); // On arrête immédiatement le script ici !
    }
}
// ==========================================
// ROUTAGE DE L'API SELON LE VERBE HTTP
// ==========================================

switch ($method) {

    // ------------------------------------------
    //  MÉTHODE GET : Récupération de données (CRUD: Read)
    // ------------------------------------------
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
            // 1. On inclut la logique. Si ça échoue (400 ou 404), le script s'arrêtera à l'intérieur d'item.php
            require_once __DIR__ . '/../../includes/item/item.php';
            
            // 2. Si on arrive ici, c'est que le produit existe ! On prépare la réponse JSON pour Angular
            $response = [
                'item' => [
                    'id'          => (int)$product['id'],
                    'titre'       => (string)$product['titre'],
                    'prix'        => (float)$product['prix'],
                    // Décommente les lignes suivantes une par une pour trouver celle qui bloque :
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
                // Logique profil...
                require_once __DIR__ . '/../../includes/user/user.php';
                // ==========================================
                // CONSTRUCTION ET ENVOI DE LA RÉPONSE JSON
                // ==========================================
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
                
            default:
                http_response_code(404);
                echo json_encode(['error' => 'Endpoint GET introuvable']);
        }
        break;

    // ------------------------------------------
    //  MÉTHODE POST : Création de ressources / Actions spécifiques (CRUD: Create)
    // ------------------------------------------
    case 'POST':
        switch ($action) {
            case 'connexion':
                // Utiliser $inputData['email'] et $inputData['password'] envoyés par Angular
                // Logique de vérification...
                $_POST['email'] = $inputData['email']; 
                $_POST['password'] = $inputData['password'];
                include __DIR__ . '/../../includes/connection/connection.php'; // Ce fichier doit vérifier les identifiants et créer $_SESSION['user_id'] si c'est bon
                http_response_code(200);
                echo json_encode(['success' => empty($erreurs), 'message' => $erreurs ?? 'Connexion réussie', 'user_id' => $_SESSION['user_id']]);
                break;

            case 'inscription':
                // Utiliser $inputData['email'], $inputData['password'], etc. envoyés par Angular
                // Logique de création de compte...
                $_POST['email'] = $inputData['email'];
                $_POST['password'] = $inputData['password'];
                $_POST['nom'] = $inputData['nom'] ?? '';
                $_POST['prenom'] = $inputData['prenom'] ?? '';
                $_POST['confirm_password'] = $inputData['confirm_password'] ?? '';
                $_POST['telephone'] = $inputData['telephone'] ?? null;
                $_POST['date_naissance'] = $inputData['date_naissance'] ?? null;
                $_POST['adresse_postale'] = $inputData['adresse_postale'] ?? null;
                include __DIR__ . '/../../includes/inscription/inscription.php'; // Ce fichier doit créer le compte et éventuellement connecter l'utilisateur
                http_response_code(201); // 201 = Created
                echo json_encode(['success' => empty($erreurs), 'message' => $erreurs ?? 'Utilisateur créé']);
                break;

            case 'post_item':
                // Ajouter un article (Données dans $inputData)
                $_POST['titre'] = $inputData['titre'] ?? '';
                $_POST['description'] = $inputData['description'] ?? '';
                $_POST['prix'] = $inputData['prix'] ?? 0;
                $_POST['categorie_id'] = $inputData['categorie_id'] ?? 1;
                $_POST['addresse'] = $inputData['addresse'] ?? '';
                $_POST['ville_nom'] = $inputData['ville_nom'] ?? '';
                $_POST['code_postal'] = $inputData['code_postal'] ?? '';
                
                $_POST['vendeur_id'] = $inputData['user_id']; // L'ID de l'utilisateur connecté
                include __DIR__ . '/../../includes/post/post.php'; // Ce fichier doit créer l'article et gérer les images
                http_response_code(201);
                echo json_encode(['success' => empty($erreurs), 'errors' => $erreurs ?? [], 'article_id' => 123]);
                break;

            case 'favoris':
                // Ajouter aux favoris
                http_response_code(200);
                echo json_encode(['success' => true, 'message' => 'Ajouté aux favoris']);
                break;

            default:
                http_response_code(404);
                echo json_encode(['error' => 'Endpoint POST introuvable']);
        }
        break;

    // ------------------------------------------
    //  MÉTHODE PUT : Modification de ressources (CRUD: Update)
    // ------------------------------------------
    case 'PUT':
        switch ($action) {
            case 'edit_profile':
                // Les modifications Angular seront dans $inputData
                http_response_code(200);
                echo json_encode(['success' => true, 'message' => 'Profil mis à jour']);
                break;

            case 'edit_item':
                http_response_code(200);
                echo json_encode(['success' => true, 'message' => 'Article modifié']);
                break;

            case 'valider_reception':
                http_response_code(200);
                echo json_encode(['success' => true, 'statut' => 'recu']);
                break;

            default:
                http_response_code(404);
                echo json_encode(['error' => 'Endpoint PUT introuvable']);
        }
        break;

    // ------------------------------------------
    //  MÉTHODE DELETE : Suppression de ressources (CRUD: Delete)
    // ------------------------------------------
    case 'DELETE':
        switch ($action) {
            case 'delete_item':
                $id = (int)($_GET['id'] ?? 0); // On passe souvent l'ID dans l'URL en DELETE
                http_response_code(200);
                echo json_encode(['success' => true, 'message' => 'Article supprimé']);
                break;

            case 'delete_user':
                http_response_code(200);
                echo json_encode(['success' => true, 'message' => 'Compte supprimé']);
                break;

            case 'favoris':
                // Retirer des favoris
                http_response_code(200);
                echo json_encode(['success' => true, 'message' => 'Retiré des favoris']);
                break;

            default:
                http_response_code(404);
                echo json_encode(['error' => 'Endpoint DELETE introuvable']);
        }
        break;

    // ------------------------------------------
    //  VERBE NON GÉRÉ
    // ------------------------------------------
    default:
        http_response_code(405); // 405 = Method Not Allowed
        echo json_encode(['error' => 'Méthode HTTP non supportée']);
        break;
}

exit(); // Sécurité pour empêcher tout affichage parasite
<?php

// 1. DÉMARRAGE DE LA SESSION & SÉCURITÉ
session_start(); 

// En-têtes CORS indispensables pour communiquer avec Angular
header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

// Gestion du Preflight (requête OPTIONS automatique du navigateur)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// 2. INCLUSIONS ET INITIALISATION
require_once __DIR__ . '/../includes/db.php'; // Fournit la variable $pdo
// require_once __DIR__ . '/../includes/fonctions.php'; 

// 3. RÉCUPÉRATION DES DONNÉES ENTRANTES (JSON OU QUERY)
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

// Récupérer le corps de la requête (Body) envoyé par Angular (JSON décodé en tableau PHP)
$inputData = json_decode(file_get_contents('php://input'), true) ?? [];


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
                // require_once __DIR__ . '/../includes/catalogue/catalogue.php';
                $response = [
                    'articles'   => $results ?? [],
                    'categories' => $categories ?? []
                ];
                http_response_code(200);
                echo json_encode($response);
                break;

            case 'item':
                $id = (int)($_GET['id'] ?? 0);
                // Logique pour récupérer UN article précis...
                http_response_code(200);
                echo json_encode(['item' => []]);
                break;

            case 'profile':
                if (!isset($_SESSION['user_id'])) {
                    http_response_code(401);
                    echo json_encode(['error' => 'Non autorisé']);
                    exit();
                }
                // Logique profil...
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
                $_SESSION['user_id'] = $user['id']; // Exemple
                http_response_code(200);
                echo json_encode(['success' => true, 'message' => 'Connexion réussie']);
                break;

            case 'inscription':
                http_response_code(201); // 201 = Created
                echo json_encode(['success' => true, 'message' => 'Utilisateur créé']);
                break;

            case 'post_item':
                // Ajouter un article (Données dans $inputData)
                http_response_code(201);
                echo json_encode(['success' => true, 'article_id' => 123]);
                break;

            case 'favoris':
                // Ajouter aux favoris
                http_theme_code(200);
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
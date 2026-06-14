<?php
// 1. ON ACTIVE LE TAMPON DE SORTIE (Évite les erreurs "Headers already sent")
ob_start();

// 2. CONFIGURATION ET DÉMARRAGE DE LA SESSION
ini_set('session.cookie_samesite', 'Lax'); 
ini_set('session.cookie_secure', '0'); 
ini_set('session.cookie_httponly', '0'); 

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/db.php';


$action = $_GET['action'] ?? 'auth'; // La page de base par défaut est 'auth' (connexion)

// ==========================================
// VÉRIFICATION GLOBALE DE LA SÉCURITÉ ADMIN
// ==========================================
// Liste des pages accessibles sans être admin connecté
$pagesPubliques = ['auth', 'connexion'];

if (!in_array($action, $pagesPubliques)) {
    // Si pas connecté OU connecté mais pas administrateur -> DECONNEXION ET RETOUR FORMULAIRE
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
        session_destroy();
        header('Location: /routeur.php?action=auth&error=access_denied');
        exit();
    }
}

// ==========================================
// GESTION DES REQUÊTES POST (Formulaires)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($action) {
        case 'connexion':
            require_once __DIR__ . '/../includes/connexion/connexion.php';
            // Le fichier connexion.php remplit la session. On vérifie le résultat ici :
            if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1) {
                header('Location: /routeur.php?action=dashboard');
            } else {
                // Si un utilisateur non-admin essaie de se connecter ici, on le rejette
                session_destroy();
                header('Location: /routeur.php?action=auth&error=not_admin');
            }
            exit();

        case 'edit_profile':
            require_once __DIR__ . '/../includes/edit_profile/edit_profile.php';
            header('Location: /routeur.php?action=user&success=profile_updated');
            exit();

        case 'edit_item':
            require_once __DIR__ . '/../includes/edit_item/edit_item.php';
            header('Location: /routeur.php?action=item&id=' . (int)($_POST['article_id'] ?? 0));
            exit();

        default:
            http_response_code(444);
            header('Location: /routeur.php?action=auth');
            exit();
    }
}

// ==========================================
// GESTION DES REQUÊTES GET (Affichage)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    switch ($action) {
        case 'auth':
            // Empêche un admin déjà connecté de revenir sur la page de connexion
            if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1) {
                header('Location: /routeur.php?action=dashboard');
                exit();
            }
            require_once __DIR__ . '/../templates/header.php';
            require_once __DIR__ . '/../templates/connexion/index.php';
            require_once __DIR__ . '/../templates/footer.php';
            break;

        case 'dashboard':
            // Génération des graphiques Python (Mosaïque MongoDB / MySQL)
            $pythonScript = '/var/www/html/dashboard/generate_chart.py';
            $output = shell_exec("python3 $pythonScript 2>&1");
            $dashboardError = (trim($output) !== "Success") ? "Erreur stats : " . $output : null;

            require_once __DIR__ . '/../templates/header.php';
            require_once __DIR__ . '/../templates/dashboard/index.php';
            require_once __DIR__ . '/../templates/footer.php';
            break;

        case 'catalogue':
            // Recherche et liste globale
            require_once __DIR__ . '/../includes/catalogue/catalogue.php';
            require_once __DIR__ . '/../templates/header.php';
            require_once __DIR__ . '/../templates/catalogue/index.php';
            require_once __DIR__ . '/../templates/footer.php';
            break;

        case 'item':
            require_once __DIR__ . '/../includes/item/item.php';
            require_once __DIR__ . '/../templates/header.php';
            require_once __DIR__ . '/../templates/items/index.php';
            require_once __DIR__ . '/../templates/footer.php';
            break;

        case 'user':
            require_once __DIR__ . '/../includes/user/user.php';
            require_once __DIR__ . '/../templates/header.php';
            require_once __DIR__ . '/../templates/user/index.php';
            require_once __DIR__ . '/../templates/footer.php';
            break;

        case 'edit_profile':
            require_once __DIR__ . '/../includes/edit_profile/edit_profile.php';
            require_once __DIR__ . '/../templates/header.php';
            require_once __DIR__ . '/../templates/edit_profile/index.php';
            require_once __DIR__ . '/../templates/footer.php';
            break;

        case 'edit_item':
            require_once __DIR__ . '/../includes/item/item.php'; 
            require_once __DIR__ . '/../templates/header.php';
            require_once __DIR__ . '/../templates/edit_item/index.php';
            require_once __DIR__ . '/../templates/footer.php';
            break;

        case 'deconnexion':
            session_destroy();
            header('Location: /routeur.php?action=auth');
            exit();

        default:
            header('Location: /routeur.php?action=auth');
            exit();
    }
}
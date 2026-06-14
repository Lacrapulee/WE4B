<?php
require_once __DIR__ . '/../db.php';
include_once __DIR__ . '/../tools.php';

$erreurs = null;
$succes = [];
$nouvelArticleId = null;

// Allow execution if seller ID is provided (either from session or manual API pass)
$vendeur_id = $_POST['vendeur_id'] ?? ($_SESSION['user_id'] ?? null);

if (!$vendeur_id) {
    $erreurs = "Utilisateur non connecté.";
    http_response_code(401);
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Récupération des données du formulaire
    $titre = $_POST['titre'] ?? '';
    $description = $_POST['description'] ?? '';
    $prix = $_POST['prix'] ?? 0;
    $categorie_id = $_POST['categorie_id'] ?? 1;
    // Note: old code used 'addresse', the form uses 'coordonnees'
    $addresse = $_POST['addresse'] ?? ($_POST['coordonnees'] ?? '');
    $ville_nom = $_POST['ville_nom'] ?? '';
    $code_postal = $_POST['code_postal'] ?? '';
    
    $dossierCible = __DIR__ . "/../../public/assets/img/";
    $autorise = ['jpg', 'jpeg', 'png', 'webp'];
    
    $nombreDeFichiers = isset($_FILES['ma_super_image']['name']) ? count((array)$_FILES['ma_super_image']['name']) : 0;
    
    if ($nombreDeFichiers === 0) {
        $erreurs = "Aucune image sélectionnée.";
        http_response_code(400);
    }

    // --- TRAITEMENT DES IMAGES ---
    for ($i = 0; $i < $nombreDeFichiers; $i++) {
        if ($_FILES['ma_super_image']['error'][$i] === UPLOAD_ERR_OK) {
            $nomFichierOriginal = $_FILES['ma_super_image']['name'][$i];
            $cheminTemporaire = $_FILES['ma_super_image']['tmp_name'][$i];
            $infosFichier = pathinfo($nomFichierOriginal);
            $extension = strtolower($infosFichier['extension']);
            
            if (in_array($extension, $autorise) && getimagesize($cheminTemporaire)) {
                $nomSecurise = bin2hex(random_bytes(8)) . "." . $extension;
                $cheminFinal = $dossierCible . $nomSecurise;

                if (move_uploaded_file($cheminTemporaire, $cheminFinal)) {
                    array_push($succes, [$nomSecurise, $i + 1]); // On stocke le nom de l'image et son ordre
                } else {
                    $erreurs = "Erreur serveur pour " . htmlspecialchars($nomFichierOriginal);
                    http_response_code(500);
                }
            } else {
                $erreurs = "Fichier invalide ou non autorisé : " . htmlspecialchars($nomFichierOriginal);
                http_response_code(400);
            }
        } elseif ($_FILES['ma_super_image']['error'][$i] !== UPLOAD_ERR_NO_FILE) {
            $erreurs = "Erreur de téléchargement pour l'image " . ($i + 1);
            http_response_code(400);
        }
    }

    // --- INSERTION EN BASE DE DONNÉES ---
    if (empty($erreurs) && !empty($succes)) {
        
        $coordonnees = getCoordinates($addresse, $ville_nom, $code_postal);

        // On crée l'article avec toutes les nouvelles colonnes
        require_once __DIR__ . '/../articles_functions.php'; // Ensure functions are loaded
        $nouvelArticleId = addItem($pdo, $vendeur_id, $categorie_id, $titre, $description, $prix, $coordonnees, $ville_nom, $code_postal);
        
        // On lie les images
        foreach ($succes as $image) {
            addImage($pdo, $nouvelArticleId, $image[0], $image[1]); // $image[0] = nom de l'image, $image[1] = ordre
        }
        http_response_code(201);
    } elseif (!empty($erreurs)) {
        // Nettoyage des images orphelines si échec
        foreach ($succes as $imageOrpheline) {
            @unlink($dossierCible . $imageOrpheline[0]);
        }
    }
} else {
    http_response_code(405);
    $erreurs = "Méthode non autorisée.";
}
?>
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
    
    $images = $_POST['images'] ?? [];
    if (!is_array($images)) {
        $images = [$images];
    }
    
    if (empty($images)) {
        // Au lieu de retourner une erreur, on récupère l'image par défaut depuis MongoDB
        global $imageCollection;
        if (isset($imageCollection)) {
            $defaultImage = $imageCollection->findOne(['is_default' => true]);
            if ($defaultImage) {
                $images = [(string) $defaultImage['_id']];
            } else {
                $erreurs = "Aucune image sélectionnée et aucune image par défaut trouvée.";
                http_response_code(400);
            }
        } else {
            $erreurs = "Aucune image sélectionnée et impossible de se connecter à MongoDB.";
            http_response_code(400);
        }
    }

    // --- TRAITEMENT DES IMAGES ---
    foreach ($images as $i => $imageId) {
        if (!empty($imageId)) {
             array_push($succes, [$imageId, $i + 1]);
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
            addImage($pdo, $nouvelArticleId, $image[0], $image[1]); // $image[0] = id de l'image (mongo), $image[1] = ordre
        }
        http_response_code(201);
    }
} else {
    http_response_code(405);
    $erreurs = "Méthode non autorisée.";
}
?>
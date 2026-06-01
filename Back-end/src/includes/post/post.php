<?php
require_once __DIR__ . '/../db.php';
include_once __DIR__ . '/../tools.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /routeur.php?action=auth');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['ma_super_image'])) {
    
    // Récupération des données du formulaire
    $titre = $_POST['titre'] ?? '';
    $description = $_POST['description'] ?? '';
    $prix = $_POST['prix'] ?? 0;
    $categorie_id = $_POST['categorie_id'] ?? 1;
    $addresse = $_POST['addresse'] ?? '';
    $ville_nom = $_POST['ville_nom'] ?? '';
    $code_postal = $_POST['code_postal'] ?? '';
    
    $vendeur_id = $_SESSION['user_id']; // L'ID de l'utilisateur connecté
    
    $dossierCible = __DIR__ . "/../../public/assets/img/";
    $autorise = ['jpg', 'jpeg', 'png', 'webp'];
    $nombreDeFichiers = count($_FILES['ma_super_image']['name']);
    $succes = [];
    $erreurs = [];

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
                    $erreurs[] = "Erreur serveur pour " . htmlspecialchars($nomFichierOriginal);
                }
            } else {
                $erreurs[] = "Fichier invalide ou non autorisé : " . htmlspecialchars($nomFichierOriginal);
            }
        } elseif ($_FILES['ma_super_image']['error'][$i] !== UPLOAD_ERR_NO_FILE) {
            $erreurs[] = "Erreur de téléchargement pour l'image " . ($i + 1);
        }
    }

    // --- INSERTION EN BASE DE DONNÉES ---
    if (empty($erreurs) && !empty($succes)) {
        
    $coordonnees = getCoordinates($addresse, $ville_nom, $code_postal);

        // On crée l'article avec toutes les nouvelles colonnes
        $nouvelArticleId = addItem($pdo, $vendeur_id, $categorie_id, $titre, $description, $prix, $coordonnees, $ville_nom, $code_postal);
        // On lie les images
        foreach ($succes as $image) {
            addImage($pdo, $nouvelArticleId, $image[0], $image[1]); // $image[0] = nom de l'image, $image[1] = ordre
        }
        
        
    } elseif (!empty($erreurs)) {
        echo "<div style='color:red;'><strong>Erreurs :</strong><ul>";
        foreach ($erreurs as $erreur) { echo "<li>$erreur</li>"; }
        echo "</ul></div>";
        
        // Nettoyage des images orphelines si échec
        foreach ($succes as $imageOrpheline) {
            @unlink($dossierCible . $imageOrpheline);
        }
    }
}
?>
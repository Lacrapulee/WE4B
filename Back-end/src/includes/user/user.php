<?php
require_once __DIR__ . '/../db.php';

$profile_id = $_GET['id'] ?? null;
if (!$profile_id) { die("Utilisateur non trouvé."); }

//Chercher les infos du compte
$stmt = $pdo->prepare("SELECT id, nom, prenom, telephone, email, created_at, adresse_postale FROM users WHERE id = ?");
$stmt->execute([$profile_id]);
$user = $stmt->fetch();

if (!$user) { die("Ce profil n'existe pas."); }

// Vérifier si c'est le propriétaire ou admin qui regarde
$is_owner = (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $profile_id);
$isAdmin = (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1);

$stmt = $pdo->prepare("SELECT * FROM articles WHERE vendeur_id = ? ORDER BY created_at DESC");
$stmt->execute([$profile_id]);
$articles = $stmt->fetchAll();

foreach ($articles as &$article) {
    $stmt = $pdo->prepare("SELECT url_image FROM article_images WHERE article_id = ? AND est_principale = 1 LIMIT 1");
    $stmt->execute([$article['id']]);
    $image = $stmt->fetch();
    $article['image'] = $image ? $image['url_image'] : 'default.png';
}

$stmt = $pdo->prepare("
    SELECT 
        a.note, 
        a.commentaire, 
        a.date_avis,
        u.prenom AS auteur_prenom,
        u.nom AS auteur_nom,
        art.titre AS article_titre
    FROM avis a
    JOIN users u ON a.expediteur_id = u.id
    LEFT JOIN articles art ON a.article_id = art.id
    WHERE a.destinataire_id = ?
    ORDER BY a.date_avis DESC
");
$stmt->execute([$profile_id]);
$reviews = $stmt->fetchAll();

// Variables pour la vue
$nom = $user['nom'];
$prenom = $user['prenom'];
$telephone = $user['telephone'];
$email = $user['email'];
$adresse_postale = $user['adresse_postale'];
<?php
require_once __DIR__ . '/../db.php';

$profile_id = $_GET['id'] ?? null;

if (!$profile_id) {
    echo "Erreur : Pas d'ID spécifié.";
    exit();
}

// 1. Infos utilisateur
$stmt = $pdo->prepare("SELECT id, nom, prenom, telephone, email, created_at, adresse_postale FROM users WHERE id = ?");
$stmt->execute([$profile_id]);
$user = $stmt->fetch();

if (!$user) {
    echo "Erreur : Utilisateur introuvable.";
    exit();
}

// 2. Droits
$is_owner = false;
if (isset($_SESSION['user_id'])) {
    $is_owner = ($_SESSION['user_id'] == $profile_id);
}
$isAdmin = false;
if (isset($_SESSION['is_admin'])) {
    $isAdmin = ($_SESSION['is_admin'] == 1);
}

// 3. Récupération des articles (Sans la colonne géométrique brute qui fait planter)
$stmt = $pdo->prepare("
    SELECT id, vendeur_id, categorie_id, titre, description, prix, statut, ville_nom, code_postal, created_at 
    FROM articles 
    WHERE vendeur_id = ? 
    ORDER BY created_at DESC
");
$stmt->execute([$profile_id]);
$articles = $stmt->fetchAll();

// Récupération des images pour chaque article
foreach ($articles as &$article) {
    $stmt = $pdo->prepare("SELECT url_image FROM article_images WHERE article_id = ? AND est_principale = 1 LIMIT 1");
    $stmt->execute([$article['id']]);
    $image = $stmt->fetch();
    $article['image'] = $image ? $image['url_image'] : null;
}

// 4. Récupération des avis
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

// 5. Variables pour la vue
$nom = $user['nom'];
$prenom = $user['prenom'];
$telephone = $user['telephone'];
$email = $user['email'];
$adresse_postale = $user['adresse_postale'];

// 6. Préparation des résultats pour l'API
$results = [
    'user' => $user ?? [],
    'articles' => $articles ?? [],
    'reviews'  => $reviews ?? [],
    'isOwner'  => (bool)$is_owner,
    'isAdmin'  => (bool)$isAdmin
];
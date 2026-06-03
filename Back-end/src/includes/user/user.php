<?php
// includes/user/user.php
require_once __DIR__ . '/../db.php';

// 1. Récupération et validation de l'ID passé dans l'URL par Angular
$profile_id = $_GET['id'] ?? null;

if (!$profile_id) {
    http_response_code(400);
    echo json_encode(['error' => "ID de l'utilisateur manquant ou invalide."]);
    exit();
}

// 2. Chercher les infos du compte
$stmt = $pdo->prepare("SELECT id, nom, prenom, telephone, email, created_at, adresse_postale FROM users WHERE id = ?");
$stmt->execute([$profile_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    http_response_code(404);
    echo json_encode(['error' => "Ce profil n'existe pas."]);
    exit();
}

// 3. Vérifier les droits (propriétaire ou admin)
$is_owner = (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $profile_id);
$isAdmin = (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1);

// 4. Récupérer les articles mis en vente par cet utilisateur
$stmt = $pdo->prepare("SELECT * FROM articles WHERE vendeur_id = ? ORDER BY created_at DESC");
$stmt->execute([$profile_id]);
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($articles as &$article) {
    $stmt = $pdo->prepare("SELECT url_image FROM article_images WHERE article_id = ? AND est_principale = 1 LIMIT 1");
    $stmt->execute([$article['id']]);
    $image = $stmt->fetch(PDO::FETCH_ASSOC);
    $article['image'] = $image ? $image['url_image'] : 'default.png';
}

// 5. Récupérer les avis reçus par cet utilisateur
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
$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);


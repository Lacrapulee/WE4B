<?php
header("Access-Control-Allow-Origin: http://localhost:4200");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$sql_host = 'db';
$sql_db   = 'WE4ADB';
$sql_user = 'root';
$sql_pass = 'rootpassword';
$charset  = 'utf8mb4';

$dsn = "mysql:host=$sql_host;dbname=$sql_db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $sql_user, $sql_pass, $options);
} catch (\PDOException $e) {
    echo json_encode(["error" => "Erreur de connexion SQL : " . $e->getMessage()]);
    exit();
}

$id       = $_GET['id'] ?? '';
$search   = $_GET['search'] ?? '';
$categorie = $_GET['categorie'] ?? '';
$ville    = $_GET['ville'] ?? '';
$prix_min = $_GET['prix_min'] ?? '';
$prix_max = $_GET['prix_max'] ?? '';
$tri      = $_GET['tri'] ?? 'date_recent';

if (!empty($id)) {
    try {
        $stmt = $pdo->prepare(
            "SELECT
                a.id,
                a.vendeur_id,
                a.categorie_id,
                a.titre,
                a.description,
                a.prix,
                a.statut,
                a.ville_nom,
                a.code_postal,
                c.nom AS categorie_nom,
                COALESCE(ai.url_image, 'default.png') AS image,
                0 AS isFavoris
             FROM articles a
             LEFT JOIN categories c ON a.categorie_id = c.id
             LEFT JOIN article_images ai ON ai.article_id = a.id AND ai.est_principale = 1
             WHERE a.id = ? AND a.statut = 'en_ligne'
             LIMIT 1"
        );
        $stmt->execute([$id]);
        $article = $stmt->fetch();

        if (!$article) {
            http_response_code(404);
            echo json_encode(["error" => "Article introuvable"]);
            exit();
        }

        echo json_encode($article);
        exit();
    } catch (Exception $e) {
        echo json_encode(["error" => "Erreur SQL: " . $e->getMessage()]);
        exit();
    }
}

$sql = "SELECT
        a.id,
        a.vendeur_id,
        a.categorie_id,
        a.titre,
        a.description,
        a.prix,
        a.statut,
        a.ville_nom,
        a.code_postal,
        c.nom AS categorie_nom,
        COALESCE(ai.url_image, 'default.png') AS image,
        0 AS isFavoris
    FROM articles a
    LEFT JOIN categories c ON a.categorie_id = c.id
    LEFT JOIN article_images ai ON ai.article_id = a.id AND ai.est_principale = 1
    WHERE a.statut = 'en_ligne'";

$params = [];

if (!empty($search)) {
    $sql .= " AND (a.titre LIKE ? OR a.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($categorie)) {
    $sql .= " AND a.categorie_id = ?";
    $params[] = $categorie;
}

if (!empty($ville)) {
    $sql .= " AND a.ville_nom LIKE ?";
    $params[] = "%$ville%";
}

if (!empty($prix_min)) {
    $sql .= " AND a.prix >= ?";
    $params[] = $prix_min;
}

if (!empty($prix_max)) {
    $sql .= " AND a.prix <= ?";
    $params[] = $prix_max;
}

switch ($tri) {
    case 'prix_min':
        $sql .= " ORDER BY a.prix ASC";
        break;
    case 'prix_max':
        $sql .= " ORDER BY a.prix DESC";
        break;
    case 'date_ancien':
        $sql .= " ORDER BY a.id ASC";
        break;
    case 'date_recent':
    default:
        $sql .= " ORDER BY a.id DESC";
        break;
}

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $articles = $stmt->fetchAll();

    echo json_encode($articles);
    exit();
} catch (Exception $e) {
    echo json_encode(["error" => "Erreur SQL: " . $e->getMessage()]);
    exit();
}
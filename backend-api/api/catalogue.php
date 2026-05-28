<?php
// =========================================================================
// 1. CONFIGURATION DES HEADERS (Indispensable pour Angular)
// =========================================================================
// Autorise Angular (port 4200) à interroger cette API
header("Access-Control-Allow-Origin: http://localhost:4200");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Gestion du protocole CORS (Angular envoie parfois une requête OPTIONS avant le GET)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// =========================================================================
// 2. CONNEXIONS AUX BASES DE DONNÉES (SQL & MONGO)
// =========================================================================
$sql_host = 'db'; // Nom du service MySQL dans docker-compose
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

// Connexion MongoDB via l'extension native PHP
// (Nécessite d'avoir installé le driver mongodb via compose ou docker)
try {
    // Version simplifiée sans librairie externe (si le driver PHP-Mongo est installé)
    $mongoClient = new MongoDB\Driver\Manager("mongodb://root:example@localhost:27017");
} catch (Exception $e) {
    // On ne bloque pas l'application si les logs foirent, mais on garde une trace
    $mongoError = $e->getMessage();
}


// =========================================================================
// 3. RECUPERATION DES FILTRES (Depuis Angular via la méthode GET)
// =========================================================================
$search    = $_GET['search'] ?? '';
$categorie = $_GET['categorie'] ?? '';
$ville     = $_GET['ville'] ?? '';
$prix_min  = $_GET['prix_min'] ?? '';
$prix_max  = $_GET['prix_max'] ?? '';
$tri       = $_GET['tri'] ?? 'date_recent';


// =========================================================================
// 4. CONSTRUCTION DE LA REQUÊTE SQL DYNAMIQUE
// =========================================================================
$sql = "SELECT a.*, c.nom as categorie_nom 
        FROM articles a 
        LEFT JOIN categories c ON a.categorie_id = c.id 
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

// Gestion du Tri
switch ($tri) {
    case 'prix_min':
        $sql .= " ORDER BY a.prix ASC";
        break;
    case 'prix_max':
        $sql .= " ORDER BY a.prix DESC";
        break;
    case 'date_ancien':
        $sql .= " ORDER BY a.id ASC"; // Supposant que l'ID suit l'ordre chronologique
        break;
    case 'date_recent':
    default:
        $sql .= " ORDER BY a.id DESC";
        break;
}

// Exécution de la requête SQL
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$articles = $stmt->fetchAll();


// =========================================================================
// 5. SI40 : ENREGISTREMENT DU LOG DANS MONGODB (Asynchrone/Silencieux)
// =========================================================================
if (isset($mongoClient)) {
    // On prépare le document JSON demandé par l'énoncé SI40
    $logDocument = [
        '_id' => new MongoDB\BSON\ObjectId,
        'date' => date('Y-m-d H:i:s'),
        'action' => 'CONSULTATION_CATALOGUE',
        'criteres_recherche' => [
            'mots_cles' => $search,
            'categorie_id' => $categorie,
            'ville' => $ville,
            'prix_range' => ['min' => $prix_min, 'max' => $prix_max]
        ],
        'nombre_resultats_trouves' => count($articles)
    ];

    // On injecte le document dans la base "lecoincarre" et la collection "logs"
    $bulk = new MongoDB\Driver\BulkWrite;
    $bulk->insert($logDocument);
    
    try {
        $mongoClient->executeBulkWrite('lecoincarre.logs', $bulk);
    } catch (Exception $e) {
        // En prod, on logguerait l'erreur dans un fichier php_error.log
    }
}


// =========================================================================
// 6. ENVOI DE LA REPONSE EN JSON À ANGULAR
// =========================================================================
echo json_encode($articles);
exit();
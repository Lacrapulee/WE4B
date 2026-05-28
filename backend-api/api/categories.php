<?php
// =========================================================================
// Endpoint REST: categories.php
// Retourne la liste des catégories en JSON (avec CORS)
// =========================================================================
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

try {
    $stmt = $pdo->query("SELECT id, nom FROM categories ORDER BY nom ASC");
    $cats = $stmt->fetchAll();
    echo json_encode($cats);
    exit();
} catch (Exception $e) {
    echo json_encode(["error" => "Erreur SQL: " . $e->getMessage()]);
    exit();
}

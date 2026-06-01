<?php
include '../includes/db.php';
require_once '../includes/articles_functions.php';
require_once '../includes/favoris_functions.php';

// On récupère tous les filtres depuis l'URL (GET)
$filters = [
    'search'    => $_GET['search'] ?? '',
    'categorie' => $_GET['categorie'] ?? '',
    'ville'     => $_GET['ville'] ?? '',
    'prix_min'  => $_GET['prix_min'] ?? '',
    'prix_max'  => $_GET['prix_max'] ?? '',
    'distance'  => $_GET['distance'] ?? '',
    'tri'       => $_GET['tri'] ?? 'date_recent'
];

$results = getAnnonceRechercheAvancee($pdo, $filters);
$categories = getCategories($pdo);

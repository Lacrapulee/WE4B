<?php

use MongoDB\Client;
use MongoDB\BSON\UTCDateTime;

// On initialise la variable à null pour éviter les erreurs "Undefined variable" plus tard
$logCollection = null;
$imageCollection = null;

try {
    // 2. Connexion
    $mongoClient = new Client("mongodb://root:mongopassword@mongodb:27017");
    
    // 3. Sélection de la base de données et de la collection
    $db = $mongoClient->selectDatabase('WE4ADB_logs');
    $logCollection = $db->selectCollection('api_history');
    $imageCollection = $db->selectCollection('images');
} catch (\Exception $e) {
    // En production, il vaut mieux écrire ça dans un fichier de log classique (error_log)
    // pour éviter d'afficher tes identifiants ou des détails techniques aux utilisateurs de l'API.
    error_log("Erreur MongoDB : " . $e->getMessage());
}
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include __DIR__.'/../mongo.php';

if (!isset($_SESSION['user_id'])){
    http_response_code(401);
    $message = 'Connexion requise.';
    $unread_count = 0;
    exit();
}

$current_user_id = intval($_SESSION['user_id']);

try {
    $collection = $mongoClient->WE4BDB->messages;

    $unread_count = $collection->countDocuments([
        'receiver_id' => $current_user_id,
        'lu' => ['$ne' => true]
    ]);

    $message = 'Comptage réussi.';
} catch (Exception $e) {
    http_response_code(500);
    $message = 'Erreur lors du comptage : ' . $e->getMessage();
    $unread_count = 0;
}
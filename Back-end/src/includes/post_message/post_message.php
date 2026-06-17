<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include __DIR__ . '/../mongo.php'; // Contient $mongoClient

// 1. SÉCURITÉ : L'utilisateur doit être connecté
if (!isset($_SESSION['user_id'])){
    http_response_code(401);
    $message = 'Connexion requise.';
    exit();
}
// S'assure que $inputData est récupéré si le routeur ne l'a pas transmis proprement
if (!isset($inputData) || empty($inputData)) {
    $inputData = json_decode(file_get_contents('php://input'), true) ?? [];
}

// Validation des données reçues
$receiver_id = isset($inputData['receiverId']) ? $inputData['receiverId'] : null;
$content = isset($inputData['message']) ? trim($inputData['message']) : null;
$sender_id = $_SESSION['user_id']; // L'expéditeur (id1) est TOUJOURS l'user connecté

if (!$receiver_id || empty($content)) {
    http_response_code(400);
    $message =  'Données manquantes (id2 ou content).';
    exit();
}

try {
    // 3. SÉLECTION DE LA COLLECTION
    $collection = $mongoClient->WE4ADB->messages;

    // 4. PRÉPARATION DU DOCUMENT MONGO
    $newMessage = [
        'sender_id'   => $sender_id,   // id1
        'receiver_id' => $receiver_id, // id2
        'content'     => $content,     // Le message
        'lu'          => false,
        'created_at'  => new MongoDB\BSON\UTCDateTime() // Date et heure précises actuelles
    ];

    // 5. INSERTION DANS MONGO
    $result = $collection->insertOne($newMessage);

    // 6. RÉPONSE SUCCESS
    header('Content-Type: application/json');
    http_response_code(201); // 201 Created

    $message = 'Message envoyé avec succès !';


} catch (Exception $e) {
    http_response_code(500);
    $message = 'Erreur lors de l\'envoi : ' . $e->getMessage();
    
}
?>
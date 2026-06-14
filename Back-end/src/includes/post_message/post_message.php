<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include '../mongo.php'; // Contient $mongoClient

// 1. SÉCURITÉ : L'utilisateur doit être connecté
if (!isset($_SESSION['user_id'])){
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Connexion requise.']);
    exit();
}

// 2. RÉCUPÉRATION DU CORPS DE LA REQUÊTE (JSON)
// Comme c'est du POST "nature peinture", on récupère le flux raw de PHP
$json_input = file_get_contents('php://input');
$data = json_decode($json_input, true);

// Validation des données reçues
$receiver_id = isset($data['receiver_id']) ? $data['receiver_id'] : null;
$content = isset($data['content']) ? trim($data['content']) : null;
$sender_id = $_SESSION['user_id']; // L'expéditeur (id1) est TOUJOURS l'user connecté

if (!$receiver_id || empty($content)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Données manquantes (id2 ou content).']);
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
        'created_at'  => new MongoDB\BSON\UTCDateTime() // Date et heure précises actuelles
    ];

    // 5. INSERTION DANS MONGO
    $result = $collection->insertOne($newMessage);

    // 6. RÉPONSE SUCCESS
    header('Content-Type: application/json');
    http_response_code(201); // 201 Created
    echo json_encode([
        'success' => true,
        'message' => 'Message envoyé avec succès !',
        'message_id' => (string)$result->getInsertedId()
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de l\'envoi : ' . $e->getMessage()
    ]);
}
?>
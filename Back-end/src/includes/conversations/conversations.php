<?php
// On s'assure que la session est démarrée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include '../mongo.php'; // Contient ta connexion $mongoClient ou $db

if (!isset($_SESSION['user_id'])){
    
    $message = 'Vous devez être connecté pour accéder à cette page.';
    http_response_code(401);
    exit();
}

// Si pas d'erreur, on récupère l'ID de l'utilisateur connecté
$current_user_id = $_SESSION['user_id']; // Forcer en entier si tes ID MySQL sont des INT

try {
    // 1. Sélection de la base et de la collection (ajuste le nom de la DB si besoin)
    // Si ton mongo.php crée déjà une variable $db, utilise-la directement
    $collection = $mongoClient->WE4ADB->messages; 

    // 2. Requête : Trouver les messages où l'utilisateur est l'expéditeur OU le destinataire
    $filter = [
        '$or' => [
            ['sender_id' => $current_user_id],
            ['receiver_id' => $current_user_id]
        ]
    ];

    // 3. Options : Trier par date décroissante (-1) pour avoir les plus récents en premier
    $options = [
        'sort' => ['created_at' => -1],
    ];

    // 4. Exécution de la requête
    $cursor = $collection->find($filter, $options);

    // 5. Formatage de la réponse (id1, id2, content comme tu voulais)
    $discussion = [];
    foreach ($cursor as $document) {
        $discussion[] = [
            'id_message'  => (string)$document['_id'], // L'identifiant unique Mongo
            'sender_id'   => $document['sender_id'],   // L'expéditeur
            'receiver_id' => $document['receiver_id'], // Le destinataire
            'content'     => $document['content'],     // Le message texte
            'date'        => $document['created_at']->toDateTime()->format('Y-m-d H:i:s') // Date lisible
        ];
    }

    // On renvoie le résultat en JSON pour ton front
    header('Content-Type: application/json');
    $result = [
        'success' => true,
        'user_id' => $current_user_id,
        'messages' => $discussion
    ];
    $message = 'Messages récupérés avec succès.';

    http_response_code(200);

} catch (Exception $e) {
    http_response_code(500);
    $message = 'Erreur lors de la récupération des messages : ' . $e->getMessage();
    
}
?>
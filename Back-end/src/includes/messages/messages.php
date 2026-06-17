<?php
ini_set('display_errors', '0');
error_reporting(0); // temporairement, juste pour debug — à retirer ensuite et logger proprement
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include '../mongo.php'; // Contient ton instance $mongoClient

// 1. SÉCURITÉ : L'utilisateur doit être connecté
if (!isset($_SESSION['user_id'])){
    http_response_code(401);
    $message = 'Connexion requise.';
    exit();
}

// 2. RÉCUPÉRATION DE L'ID DE L'INTERLOCUTEUR (id2)
// On peut le passer en paramètre GET classique dans l'URL : ?action=get_conversation&with_user=99
$current_user_id = $_SESSION['user_id']; // id1
$with_user_id = isset($_GET['id']) ? $_GET['id'] : null; // id2

if (!$with_user_id) {
    http_response_code(400);
    $message = 'L\'ID de l\'interlocuteur (with_user) est manquant.';
    exit();
}

try {
    // 3. ACCÈS À LA COLLECTION
    $collection = $mongoClient->WE4ADB->messages;

    // 4. FILTRE CROISÉ (A écrit à B OR B écrit à A)
    $filter = [
        '$or' => [
            [
                'sender_id' => $current_user_id,
                'receiver_id' => $with_user_id
            ],
            [
                'sender_id' => $with_user_id,
                'receiver_id' => $current_user_id
            ]
        ]
    ];

    // 5. OPTIONS : Tri chronologique de bas en haut (1 = du plus ancien au plus récent)
    $options = [
        'sort' => ['created_at' => 1] 
    ];

    // 6. EXÉCUTION
    $cursor = $collection->find($filter, $options);
    // Marquer comme lus les messages reçus dans cette conversation
    $collection->updateMany(
        ['sender_id' => $with_user_id, 'receiver_id' => $current_user_id, 'lu' => ['$ne' => true]],
        ['$set' => ['lu' => true]]
    );
    
    // 7. FORMATAGE DU FIL DE DISCUSSION
    $chat_history = [];
    foreach ($cursor as $document) {
        $chat_history[] = [
            'id_message'  => (string)$document['_id'],
            'id1'         => $document['sender_id'],
            'id2'         => $document['receiver_id'],
            'content'     => $document['content'],
            'date'        => isset($document['created_at']) 
                                ? $document['created_at']->toDateTime()->format('Y-m-d H:i:s') 
                                : null,
            'is_me'       => ((string)$document['sender_id'] === (string)$current_user_id)
        ];
    }
    // 8. RÉPONSE JSON
    $message = "Récupération réussie.";

} catch (Exception $e) {
    http_response_code(500);
    $message = 'Erreur lors de la récupération du chat : ' . $e->getMessage();

}
?>
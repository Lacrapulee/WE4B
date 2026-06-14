<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include '../mongo.php'; // Contient ton instance $mongoClient

// 1. SÉCURITÉ : L'utilisateur doit être connecté
if (!isset($_SESSION['user_id'])){
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Connexion requise.']);
    exit();
}

// 2. RÉCUPÉRATION DE L'ID DE L'INTERLOCUTEUR (id2)
// On peut le passer en paramètre GET classique dans l'URL : ?action=get_conversation&with_user=99
$current_user_id = $_SESSION['user_id']; // id1
$with_user_id = isset($_GET['id']) ? $_GET['id'] : null; // id2

if (!$with_user_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'L\'ID de l\'interlocuteur (with_user) est manquant.']);
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

    // 7. FORMATAGE DU FIL DE DISCUSSION
    $chat_history = [];
    foreach ($cursor as $document) {
        $chat_history[] = [
            'id_message'  => (string)$document['_id'],
            'id1'         => $document['sender_id'],
            'id2'         => $document['receiver_id'],
            'content'     => $document['content'],
            'date'        => $document['created_at']->toDateTime()->format('Y-m-d H:i:s'),
            // Petit bonus pratique pour ton Front Angular :
            // Savoir en un clin d'œil si c'est le user connecté qui a écrit (pour aligner le message à droite ou à gauche)
            'is_me'       => ($document['sender_id'] === $current_user_id) 
        ];
    }

    // 8. RÉPONSE JSON
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'chatting_with' => $with_user_id,
        'messages' => $chat_history
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la récupération du chat : ' . $e->getMessage()
    ]);
}
?>
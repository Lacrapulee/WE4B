<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include __DIR__.'/../mongo.php';
include __DIR__.'/../db.php'; // pour avoir $pdo (connexion MySQL)

if (!isset($_SESSION['user_id'])){
    http_response_code(401);
    $message = 'Vous devez être connecté pour accéder à cette page.';
    exit();
}

$current_user_id = intval($_SESSION['user_id']);

try {
    $collection = $mongoClient->WE4BDB->messages;

    $pipeline = [
        ['$match' => [
            '$or' => [
                ['sender_id' => $current_user_id],
                ['receiver_id' => $current_user_id]
            ]
        ]],
        // On crée un champ "interlocuteur" = l'autre personne dans la conv
        ['$addFields' => [
            'interlocuteur' => [
                '$cond' => [
                    'if' => ['$eq' => ['$sender_id', $current_user_id]],
                    'then' => '$receiver_id',
                    'else' => '$sender_id'
                ]
            ]
        ]],
        ['$sort' => ['created_at' => -1]],
        // On garde le 1er doc (le plus récent) par interlocuteur
        ['$group' => [
            '_id' => '$interlocuteur',
            'last_message' => ['$first' => '$content'],
            'last_date'    => ['$first' => '$created_at'],
        ]],
        ['$sort' => ['last_date' => -1]]
    ];

    $cursor = $collection->aggregate($pipeline);

    $result = [];
    foreach ($cursor as $doc) {
        $result[] = [
            'with_user_id' => (string)$doc['_id'],
            'last_message' => $doc['last_message'],
            'last_date'    => $doc['last_date']->toDateTime()->format('Y-m-d H:i:s'),
        ];
    }

    // Enrichissement avec nom/prénom depuis MySQL
    if (!empty($result) && isset($pdo)) {
        $userIds = array_column($result, 'with_user_id');
        $placeholders = implode(',', array_fill(0, count($userIds), '?'));

        $stmt = $pdo->prepare("SELECT id, nom, prenom FROM users WHERE id IN ($placeholders)");
        $stmt->execute($userIds);
        $usersById = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $u) {
            $usersById[$u['id']] = $u;
        }

        foreach ($result as &$conv) {
            $u = $usersById[$conv['with_user_id']] ?? null;
            $conv['nom'] = $u['nom'] ?? 'Utilisateur';
            $conv['prenom'] = $u['prenom'] ?? '';
        }
        unset($conv);
    }

    $message = 'Conversations récupérées avec succès.';
    http_response_code(200);

} catch (Exception $e) {
    http_response_code(500);
    $message = 'Erreur lors de la récupération des conversations : ' . $e->getMessage();
    $result = [];
}
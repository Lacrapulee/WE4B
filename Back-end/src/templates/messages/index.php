<?php
require_once __DIR__ . '/../../includes/messages_functions.php';

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    header('Location: /routeur.php?action=connexion');
    exit;
}
?>

<link rel="stylesheet" href="/assets/css/style.css">
<link rel="stylesheet" href="/assets/css/message.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">


<?php
$conversation_id = $_GET['id'] ?? null;

if ($conversation_id) {
    // Afficher une conversation spécifique
    $conversation = getConversationDetails($pdo, $conversation_id);
    
    if (!$conversation) {
        $_SESSION['error'] = 'Conversation non trouvée';
        header('Location: /routeur.php?action=messages');
        exit;
    }

    // Vérifier l'accès
    if ($conversation['acheteur_id'] !== $user_id && $conversation['vendeur_id'] !== $user_id) {
        $_SESSION['error'] = 'Accès non autorisé';
        header('Location: /routeur.php?action=messages');
        exit;
    }

    $messages = getConversationMessages($pdo, $conversation_id, $user_id);
    $isVendeur = $conversation['vendeur_id'] === $user_id;
    $autrePersonne = $isVendeur ? 
        $conversation['acheteur_prenom'] . ' ' . $conversation['acheteur_nom'] :
        $conversation['vendeur_prenom'] . ' ' . $conversation['vendeur_nom'];
    
    ?>

    <main class="container mt-5">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <!-- En-tête de la conversation -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-1"><?php echo htmlspecialchars($autrePersonne); ?></h5>
                                <?php if ($conversation['article_titre']): ?>
                                    <p class="mb-0 small"><strong>Article:</strong> <?php echo htmlspecialchars($conversation['article_titre']); ?></p>
                                    <p class="mb-0 small"><strong>Prix:</strong> <?php echo number_format($conversation['article_prix'], 2, ',', ' '); ?>€</p>
                                <?php else: ?>
                                    <p class="mb-0 small"><strong>Conversation:</strong> Contact direct avec le vendeur</p>
                                <?php endif; ?>
                            </div>
                            <a href="/routeur.php?action=messages" class="btn btn-light btn-sm">← Retour</a>
                        </div>
                    </div>
                </div>

                <!-- Zone des messages -->
                <div class="card" style="height: 500px; overflow-y: auto; background-color: #f8f9fa;">
                    <div class="card-body">
                        <?php if (empty($messages)): ?>
                            <p class="text-center text-muted mt-5">Aucun message pour le moment. Commencez la conversation!</p>
                        <?php else: ?>
                            <?php foreach ($messages as $message): ?>
                                <div class="mb-3">
                                    <div class="<?php echo $message['position'] === 'sent' ? 'd-flex justify-content-end' : ''; ?>">
                                        <div class="<?php echo $message['position'] === 'sent' ? 'bg-primary text-white' : 'bg-white border'; ?> rounded p-3" style="max-width: 70%;">
                                            <?php if ($message['position'] === 'received'): ?>
                                                <strong class="d-block mb-1"><?php echo htmlspecialchars($message['prenom'] . ' ' . $message['nom']); ?></strong>
                                            <?php endif; ?>
                                            <p class="mb-2"><?php echo nl2br(htmlspecialchars($message['contenu'])); ?></p>
                                            <small class="<?php echo $message['position'] === 'sent' ? 'text-light' : 'text-muted'; ?>">
                                                <?php echo date('d/m/Y H:i', strtotime($message['date_envoi'])); ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Formulaire d'envoi -->
                <div class="card-footer">
                    <form method="POST" action="/routeur.php?action=messages">
                        <input type="hidden" name="send_message" value="1">
                        <input type="hidden" name="conversation_id" value="<?php echo htmlspecialchars($conversation_id); ?>">
                        <div class="input-group">
                            <textarea name="contenu" class="form-control" placeholder="Votre message..." rows="3" required></textarea>
                            <button class="btn btn-primary" type="submit">Envoyer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
    <?php
} else {
    // Afficher la liste des conversations
    $conversations = getUserConversations($pdo, $user_id);
    ?>
    <main class="container mt-5">
        <div class="row">
            <div class="col-md-10 offset-md-1">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Mes Messages</h2>
                    <span class="badge bg-primary"><?php echo count($conversations); ?> conversation(s)</span>
                </div>

                <?php if (empty($conversations)): ?>
                    <div class="alert alert-info text-center py-5">
                        <h4>Aucune conversation</h4>
                        <p>Vous n'avez pas encore de messages. Pour contacter un vendeur, allez sur une annonce et cliquez sur "Contacter le vendeur".</p>
                        <a href="/routeur.php?action=catalogue" class="btn btn-primary">Parcourir les annonces</a>
                    </div>
                <?php else: ?>
                    <div class="list-group">
                        <?php foreach ($conversations as $conversation): ?>
                            <a href="/routeur.php?action=messages&id=<?php echo htmlspecialchars($conversation['id']); ?>" 
                               class="list-group-item list-group-item-action <?php echo $conversation['non_lus'] > 0 ? 'border-left border-primary' : ''; ?>" 
                               style="<?php echo $conversation['non_lus'] > 0 ? 'background-color: #f0f8ff;' : ''; ?>">
                                <div class="d-flex w-100 justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">
                                            <?php echo htmlspecialchars($conversation['autre_utilisateur_nom']); ?>
                                            <?php if ($conversation['non_lus'] > 0): ?>
                                                <span class="badge bg-primary"><?php echo $conversation['non_lus']; ?></span>
                                            <?php endif; ?>
                                        </h6>
                                        <p class="mb-1"><strong>
                                            <?php 
                                            if ($conversation['article_titre']) {
                                                echo htmlspecialchars($conversation['article_titre']);
                                            } else {
                                                echo 'Contact direct';
                                            }
                                            ?>
                                        </strong></p>
                                        <p class="mb-1 text-muted small">
                                            <?php 
                                            $dernier = htmlspecialchars(substr($conversation['dernier_message'], 0, 80));
                                            echo strlen($conversation['dernier_message']) > 80 ? $dernier . '...' : $dernier;
                                            ?>
                                        </p>
                                    </div>
                                    <small class="text-muted">
                                        <?php 
                                        $date = new DateTime($conversation['date_dernier_message']);
                                        echo $date->format('d/m/Y H:i');
                                        ?>
                                    </small>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
    <?php
}
?>

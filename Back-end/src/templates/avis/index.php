<?php
$vendeur_id = $_GET['vendeur_id'] ?? null;
$article_id = $_GET['article_id'] ?? null;

// Si pas de vendeur_id, on redirige
if (!$vendeur_id) {
    header('Location: /');
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Laisser un avis</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <div class="form-container">
        <h2>Donner mon avis</h2>

        <!-- Affichage des messages d'erreur -->
        <?php if (isset($_GET['error'])): ?>
            <div style="color: red; margin-bottom: 1rem; padding: 0.5rem; background: #ffe6e6; border-radius: 4px;">
                <?php 
                    switch ($_GET['error']) {
                        case 'invalid_note':
                            echo "✗ La note doit être entre 1 et 5.";
                            break;
                        case 'empty_comment':
                            echo "✗ Le commentaire ne peut pas être vide.";
                            break;
                        case 'already_reviewed':
                            echo "✗ Vous avez déjà laissé un avis sur cet utilisateur.";
                            break;
                        case 'user_not_found':
                            echo "✗ Cet utilisateur n'existe pas.";
                            break;
                        case 'db_error':
                            echo "✗ Erreur lors de l'ajout de l'avis. Veuillez réessayer.";
                            break;
                        default:
                            echo "✗ Une erreur s'est produite.";
                    }
                ?>
            </div>
        <?php endif; ?>
        
        <form action="/routeur.php?action=avis" method="POST">
            <input type="hidden" name="destinataire_id" value="<?= htmlspecialchars($vendeur_id) ?>">
            <input type="hidden" name="article_id" value="<?= htmlspecialchars($article_id ?? '') ?>">

            <label for="note">Note :</label>
            <select name="note" id="note" required>
                <option value="">-- Sélectionner une note --</option>
                <option value="5">5/5 - Excellent</option>
                <option value="4">4/5 - Très bien</option>
                <option value="3">3/5 - Moyen</option>
                <option value="2">2/5 - Décevant</option>
                <option value="1">1/5 - À éviter</option>
            </select>

            <label for="commentaire">Votre commentaire :</label>
            <textarea name="commentaire" id="commentaire" required placeholder="Décrivez votre expérience..."></textarea>

            <button type="submit">Envoyer l'avis</button>
        </form>
    </div>
</body>
</html>
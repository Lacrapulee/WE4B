<?php
$reviewModalId = $reviewModalId ?? 'reviewModal';
$reviewTitle = $reviewTitle ?? 'Donner mon avis';
$reviewButtonLabel = $reviewButtonLabel ?? 'Envoyer l\'avis';
$reviewTargetId = $reviewTargetId ?? '';
$reviewArticleId = $reviewArticleId ?? '';
$reviewError = $_GET['error'] ?? null;
?>

<script>
    function openReviewModal(targetId, articleId = '') {
        document.getElementById('<?= htmlspecialchars($reviewModalId, ENT_QUOTES) ?>').style.display = 'block';
        document.getElementById('reviewTargetId').value = targetId;
        document.getElementById('reviewArticleId').value = articleId;
    }

    function closeReviewModal() {
        document.getElementById('<?= htmlspecialchars($reviewModalId, ENT_QUOTES) ?>').style.display = 'none';
    }

    window.addEventListener('click', function (event) {
        const modal = document.getElementById('<?= htmlspecialchars($reviewModalId, ENT_QUOTES) ?>');
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });

    <?php if ($reviewError && $reviewTargetId): ?>
    document.addEventListener('DOMContentLoaded', function () {
        openReviewModal('<?= htmlspecialchars($reviewTargetId, ENT_QUOTES) ?>', '<?= htmlspecialchars($reviewArticleId, ENT_QUOTES) ?>');
    });
    <?php endif; ?>
</script>

<div id="<?= htmlspecialchars($reviewModalId, ENT_QUOTES) ?>" class="modal" style="display:none; position:fixed; z-index:1000; left:0; top:0; width:100%; height:100%; background-color:rgba(0,0,0,0.4);">
    <div class="modal-content" style="background-color:#fff; margin:10% auto; padding:24px; border:1px solid #888; border-radius:12px; width:90%; max-width:520px; box-shadow:0 10px 30px rgba(0,0,0,0.15);">
        <span onclick="closeReviewModal()" style="color:#aaa; float:right; font-size:28px; font-weight:bold; cursor:pointer; line-height:1;">&times;</span>
        <h2 style="margin-top:0; margin-bottom:16px;"><?= htmlspecialchars($reviewTitle) ?></h2>

        <?php if ($reviewError): ?>
            <div style="color:red; margin-bottom:1rem; padding:0.75rem; background:#ffe6e6; border-radius:6px;">
                <?php
                    switch ($reviewError) {
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
            <input type="hidden" name="destinataire_id" id="reviewTargetId" value="<?= htmlspecialchars($reviewTargetId) ?>">
            <input type="hidden" name="article_id" id="reviewArticleId" value="<?= htmlspecialchars($reviewArticleId) ?>">

            <label for="note" style="display:block; margin-bottom:6px; font-weight:600;">Note :</label>
            <select name="note" id="note" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px; margin-bottom:14px;">
                <option value="">-- Sélectionner une note --</option>
                <option value="5">5/5 - Excellent</option>
                <option value="4">4/5 - Très bien</option>
                <option value="3">3/5 - Moyen</option>
                <option value="2">2/5 - Décevant</option>
                <option value="1">1/5 - À éviter</option>
            </select>

            <label for="commentaire" style="display:block; margin-bottom:6px; font-weight:600;">Votre commentaire :</label>
            <textarea name="commentaire" id="commentaire" required placeholder="Décrivez votre expérience..." style="width:100%; min-height:120px; padding:10px; border:1px solid #ddd; border-radius:6px; resize:vertical;"></textarea>

            <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:18px;">
                <button type="button" onclick="closeReviewModal()" style="padding:10px 18px; background:#e5e7eb; color:#1e293b; border:none; border-radius:6px; cursor:pointer;">Annuler</button>
                <button type="submit" style="padding:10px 18px; background:#005f83; color:white; border:none; border-radius:6px; cursor:pointer;"><?= htmlspecialchars($reviewButtonLabel) ?></button>
            </div>
        </form>
    </div>
</div>
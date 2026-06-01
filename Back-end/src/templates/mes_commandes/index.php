<?php require_once __DIR__ . '/../../includes/mes_commandes/mes_commandes.php'; ?>

<main style="max-width: 1200px; margin: 0 auto; padding: 20px; font-family: sans-serif;">
    
    <h1 style="font-size: 24px; font-weight: bold; margin-bottom: 20px;">Mes commandes</h1>

    <!-- RÉSUMÉ RAPIDE -->
    <div style="display: flex; gap: 20px; margin-bottom: 30px; padding: 15px; background: #eee; border-radius: 8px;">
        <div>
            <strong>Nombre de commandes :</strong> <?= count($commandes) ?>
        </div>
        <div>
            <strong>Total dépensé :</strong> <?= number_format(array_sum(array_column($commandes, 'montant')), 2, ',', ' ') ?> €
        </div>
    </div>

    <!-- GRILLE DE PRODUITS -->
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px;">
        
        <?php if (empty($commandes)): ?>
            <p>Vous n'avez aucune commande.</p>
        <?php else: ?>
            <?php foreach ($commandes as $cmd): ?>
                <?php 
                    $isRecu = ($cmd['statut'] === 'recu');
                    $image = $imagesByCommande[$cmd['article_id']] ?? 'default.png';
                ?>
                
                <article style="border: 1px solid #ccc; border-radius: 10px; overflow: hidden; background: #fff;">
                    <!-- IMAGE -->
                    <div style="width: 100%; height: 200px; background: #f9f9f9;">
                        <img src="/assets/img/<?= htmlspecialchars($image) ?>" 
                             style="width: 100%; height: 100%; object-fit: cover;" 
                             alt="Produit">
                    </div>

                    <!-- INFOS -->
                    <div style="padding: 15px;">
                        <span style="font-size: 10px; color: #888;">Réf: <?= htmlspecialchars($cmd['reference']) ?></span>
                        <h2 style="font-size: 18px; margin: 5px 0;"><?= htmlspecialchars($cmd['titre']) ?></h2>
                        <p style="font-weight: bold; font-size: 20px; color: #000;"><?= number_format($cmd['montant'], 2, ',', ' ') ?> €</p>
                        
                        <p style="margin: 10px 0;">
                            <strong>Statut :</strong> 
                            <span style="color: <?= $isRecu ? 'green' : 'orange' ?>;">
                                <?= $isRecu ? 'Livré / Reçu' : 'En attente de réception' ?>
                            </span>
                        </p>

                        <!-- BOUTONS -->
                        <div style="display: flex; flex-direction: column; gap: 10px; margin-top: 15px;">
                                     <button onclick="openContactModal(<?= $cmd['article_id'] ?>)" 
                                         style="display: block; width: 100%; padding: 10px; background: #f0f0f0; color: #333; text-decoration: none; border: none; border-radius: 5px; font-size: 14px; cursor: pointer;">
                                Contacter le vendeur
                            </button>

                            <?php if (!$isRecu): ?>
                                <form action="/routeur.php?action=valider_reception" method="POST">
                                    <input type="hidden" name="vente_id" value="<?= $cmd['id'] ?>">
                                    <button type="submit" style="width: 100%; padding: 12px; background: #000; color: #fff; border: none; border-radius: 5px; cursor: pointer; font-weight: bold;">
                                        Confirmer le colis reçu
                                    </button>
                                </form>
                            <?php else: ?>
                                <button type="button" onclick="openReviewModal('<?= htmlspecialchars($cmd['vendeur_id'], ENT_QUOTES) ?>', '<?= htmlspecialchars($cmd['article_id'], ENT_QUOTES) ?>')" 
                                   style="display: block; width: 100%; text-align: center; padding: 12px; background: #28a745; color: #fff; border: none; border-radius: 5px; font-weight: bold; cursor: pointer;">
                                    Laisser un avis
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>

    </div>
</main>

<script>
    function openContactModal(articleId) {
        document.getElementById('modalArticleId').value = articleId;
        document.getElementById('contactModal').style.display = 'block';
    }

    function closeContactModal() {
        document.getElementById('contactModal').style.display = 'none';
    }

    window.onclick = function(event) {
        const modal = document.getElementById('contactModal');
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    }
</script>

<?php
$reviewModalId = 'reviewModalMesCommandes';
$reviewTitle = 'Donner mon avis';
$reviewButtonLabel = 'Envoyer l\'avis';
$reviewTargetId = $_GET['vendeur_id'] ?? '';
$reviewArticleId = $_GET['article_id'] ?? '';
require __DIR__ . '/../avis/modal.php';
?>

<!-- Modal de contact -->
<div id="contactModal" class="modal" style="display: none;">
    <div class="modal-content" style="
        background-color: white;
        margin: 10% auto;
        padding: 20px;
        border: 1px solid #888;
        border-radius: 8px;
        width: 90%;
        max-width: 500px;
    ">
        <span onclick="closeContactModal()" style="
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        ">&times;</span>
        <h2>Contacter le vendeur</h2>
        <form id="contactForm" method="POST" action="/routeur.php?action=messages">
            <input type="hidden" name="contact_vendeur" value="1">
            <input type="hidden" name="article_id" id="modalArticleId" value="">
            
            <div style="margin-bottom: 15px;">
                <label for="contenu" style="display: block; margin-bottom: 5px; font-weight: bold;">Votre message:</label>
                <textarea id="contenu" name="contenu" rows="5" required style="
                    width: 100%;
                    padding: 10px;
                    border: 1px solid #ddd;
                    border-radius: 4px;
                    font-family: Arial, sans-serif;
                    font-size: 14px;
                    box-sizing: border-box;
                " placeholder="Écrivez votre message ici..."></textarea>
            </div>
            
            <div style="text-align: right;">
                <button type="button" onclick="closeContactModal()" style="
                    margin-right: 10px;
                    padding: 10px 20px;
                    background-color: #ccc;
                    border: none;
                    border-radius: 4px;
                    cursor: pointer;
                ">Annuler</button>
                <button type="submit" style="
                    padding: 10px 20px;
                    background-color: #6366f1;
                    color: white;
                    border: none;
                    border-radius: 4px;
                    cursor: pointer;
                ">Envoyer le message</button>
            </div>
        </form>
    </div>
</div>

<style>
    .modal {
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.4);
    }
</style>
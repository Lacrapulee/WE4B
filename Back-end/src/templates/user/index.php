<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Profil de <?= htmlspecialchars($nom . ' ' . $prenom) ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/user.css">
</head>
<body>
    <?php include __DIR__ . '/../../templates/header.php'; ?>
    <div class="profile-container">
        <?php if (isset($_GET['status']) && $_GET['status'] === 'success'): ?>
            <div class="alert alert-success" style="margin-bottom: 2rem; padding: 1rem; background: #dcfce7; border: 1px solid #86efac; border-radius: 8px; color: #166534; font-weight: 600;">
                ✓ Votre avis a été publié avec succès !
            </div>
        <?php endif; ?>

        <header class="profile-header">
            <div class="profile-info">
                <h1><?= htmlspecialchars($nom . ' ' . $prenom) ?></h1>
                <p>Membre depuis le <?= date('d/m/Y', strtotime($user['created_at'])) ?></p>
                <p> Téléphone: <?= htmlspecialchars($telephone) ?></p>
                <p> Adresse postale: <?= htmlspecialchars($adresse_postale ?? 'Non renseignée') ?></p>
                <?php 
            // 1. Logique d'ÉDITION (Si c'est mon profil ou si je suis admin)
            if ($is_owner || $isAdmin): 
                // On récupère l'ID soit dans l'URL, soit en session pour éviter les liens vides
                $edit_id = $_GET['id'] ?? $_SESSION['user_id'] ?? '';
            ?>
                <a href="../routeur.php?action=edit_profile&id=<?= htmlspecialchars($edit_id) ?>" class="btn-edit">
                    Modifier le profil
                </a>

            <?php 
            // 2. Logique de CONTACT (Si je ne suis PAS le propriétaire)
            elseif (!$is_owner): ?>
                
                <?php if (isset($_SESSION['user_id'])): ?>
                    <!-- Connecté : On ouvre la modal de contact -->
                    <button onclick="openContactModal('<?= htmlspecialchars($profile_id, ENT_QUOTES) ?>')" 
                            class="btn-edit" 
                            style="border:none; cursor:pointer;">
                        ✉️ Contacter le vendeur
                    </button>
                <?php else: ?>
                    <!-- Non connecté : On renvoie vers la page de connexion -->
                    <a href="/routeur.php?action=connexion" class="btn-edit">
                        ✉️ Se connecter pour contacter
                    </a>
                <?php endif; ?>

            <?php endif; ?>
            </div>
        </header>

        <div class="profile-grid">
            <section class="user-items">
                <h2 class="section-title">Annonces</h2>
                <div class="items-grid">
                    <?php foreach ($articles as $article): ?>
                        <div class="item-card">
                            <!-- Ici, il faudrait une jointure pour avoir l'image principale -->
                            <a href="/routeur.php?action=item&id=<?= $article['id'] ?>"> 
                                <img src="../assets/img/<?= htmlspecialchars($article['image']) ?>" alt="Item">
                            </a>
                            <div class="item-info">
                                <strong><?= htmlspecialchars($article['titre']) ?></strong>
                                <p><?= $article['prix'] ?> € <span class="status-tag"><?= $article['statut'] ?></span></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>

            <aside class="user-reviews">
                <div class="reviews-header">
                    <h2 class="section-title">Avis clients</h2>
                    <?php if (!$is_owner && isset($_SESSION['user_id'])): ?>
                        <button type="button" onclick="openReviewModal('<?= htmlspecialchars($profile_id, ENT_QUOTES) ?>')" class="btn-add-review" style="border:none; cursor:pointer; background:transparent; padding:0;">
                            + Ajouter un avis
                        </button>
                    <?php endif; ?>
                </div>
                <?php if (empty($reviews)): ?>
                    <p>Aucun avis pour le moment.</p>
                <?php else: ?>
                    <?php foreach ($reviews as $review): ?>
                        <div class="review-card">
                            <div class="review-header">
                                <strong><?= htmlspecialchars($review['auteur_prenom'] . ' ' . $review['auteur_nom']) ?></strong>
                                <span class="review-note">⭐ <?= $review['note'] ?>/5</span>
                            </div>
                            <?php if ($review['article_titre']): ?>
                                <small class="review-item">Pour l'article : <?= htmlspecialchars($review['article_titre']) ?></small>
                            <?php endif; ?>
                            <p class="review-text">"<?= htmlspecialchars($review['commentaire']) ?>"</p>
                            <small class="review-date">Le <?= date('d/m/Y', strtotime($review['date_avis'])) ?></small>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </aside>
        </div>
    </div>

    <?php
    $reviewModalId = 'reviewModalUser';
    $reviewTitle = 'Donner mon avis';
    $reviewButtonLabel = 'Envoyer l\'avis';
    $reviewTargetId = $profile_id;
    $reviewArticleId = '';
    require __DIR__ . '/../avis/modal.php';
    ?>

    <script>
    function openContactModal(vendorId) {
        document.getElementById('modalVendorId').value = vendorId;
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
                <input type="hidden" name="vendor_id" id="modalVendorId" value="">

                <div style="margin-bottom: 15px;">
                    <label for="contenu" style="display: block; margin-bottom: 5px; font-weight: bold;">Votre message:</label>
                    <textarea id="contenu" name="contenu" rows="5" required style="
                        width: 100%;
                        padding: 10px;
                        border: 1px solid #ddd;
                        border-radius: 4px;
                        font-family: Arial, sans-serif;
                        font-size: 14px;
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

    <?php include __DIR__ . '/../../templates/footer.php'; ?>
</body>
</html>
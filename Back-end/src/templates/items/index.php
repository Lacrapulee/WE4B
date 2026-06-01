<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $product ? htmlspecialchars($product['titre']) : 'Article introuvable'; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/item.css">
</head>
<body class="page-body">

<main class="main-container">
    <?php if ($errorMessage): ?>
        <div class="error-box">
            <h1 class="text-2xl font-bold mb-4">Oups !</h1>
            <p class="text-gray-600 mb-6"><?= $errorMessage ?></p>
            <a href="routeur.php?action=catalogue" class="btn-back">Retour au catalogue</a>
        </div>
    <?php else: ?>

        <div class="product-grid">
            <!-- COLONNE GAUCHE -->
            <div class="lg:col-span-2 space-y-6">

                <!-- CARROUSEL -->
                <div class="carousel-card">
                    <div class="carousel-viewport">
                        <div id="carousel-track" class="carousel-track">
                            <?php foreach ($allImages as $img): ?>
                                <img src="/assets/img/<?= htmlspecialchars($img) ?>" class="carousel-slide">
                            <?php endforeach; ?>
                        </div>

                        <?php if (count($allImages) > 1): ?>
                            <button onclick="moveCarousel(-1)" class="carousel-nav left-4">❮</button>
                            <button onclick="moveCarousel(1)" class="carousel-nav right-4">❯</button>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- TITRE & PRIX -->
                <div class="flex justify-between items-center px-4">
                    <h1 class="text-3xl font-extrabold tracking-tight text-gray-900"><?= htmlspecialchars($product['titre']) ?></h1>
                    <p class="product-price"><?= number_format($product['prix'], 2, ',', ' ') ?> €</p>
                </div>

                <!-- INFORMATIONS -->
                <div class="content-section">
                    <div class="info-block">
                        <h2>Description</h2>
                        <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="info-block">
                            <h2>Localisation</h2>
                            <p><?= htmlspecialchars(($product['ville_nom'] ?? 'Ville inconnue') . ' (' . ($product['code_postal'] ?? '') . ')') ?></p>
                        </div>

                        <div class="info-block">
                            <h2>Catégorie</h2>
                            <p><?= htmlspecialchars($product['categorie_nom'] ?? 'Non classé') ?></p>
                        </div>

                        <div class="info-block">
                            <h2>Publié le</h2>
                            <p><?= date('d/m/Y', strtotime($product['created_at'])) ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- COLONNE DROITE -->
            <aside class="space-y-6">
                <section class="aside-card">
                    <p class="meta-label">Vendeur</p>
                    <a href="routeur.php?action=user&id=<?= $product['vendeur_id'] ?>" class="vendeur-link flex items-center gap-2">
                        <span class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center text-sm">👤</span>
                        <?= htmlspecialchars($product['vendeur_prenom'] . ' ' . $product['vendeur_nom']) ?>
                    </a>
                </section>

                <div class="actions-container">
                    <?php if (!$isOwner && ($product['statut'] ?? '') === 'en_ligne'): ?>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <a href="routeur.php?action=paiement&id=<?= $product['id'] ?>" class="btn-buy" style="background-color: #005F83;">Acheter l'article</a>
                            <button onclick="openContactModal(<?= $product['id'] ?>)" class="btn-buy" style="background-color: #004a66;">Contacter le vendeur</button>
                        <?php else: ?>
                            <a href="routeur.php?action=auth" class="btn-buy" style="background-color: #005F83;">Se connecter pour acheter</a>
                            <a href="routeur.php?action=auth" class="btn-buy" style="background-color: #004a66;">Contacter le vendeur</a>
                        <?php endif; ?>
                    <?php endif; ?>
                    <?php if ($isOwner || $isAdmin): ?>
                        <a href="routeur.php?action=edit_item&id=<?= $product['id'] ?>" class="btn-buy">Modifier mon annonce</a>
                    <?php endif; ?>
                </div>

                <!-- BOUTON FAVORIS -->
                <?php if (isset($_SESSION['user_id'])): ?>
                    <button id="item-favoris-btn" class="w-full px-4 py-3 border-2 border-red-500 text-red-500 font-bold rounded-lg transition-all hover:bg-red-50" 
                            onclick="toggleItemFavoris(<?= $product['id'] ?>, this)"
                            style="display: flex; align-items: center; justify-content: center; gap: 8px;">
                        <span id="item-favoris-icon" style="font-size: 20px;">♡</span>
                        <span id="item-favoris-text">Ajouter aux favoris</span>
                    </button>
                <?php endif; ?>

                <!-- SIMILAIRES -->
                <section class="aside-card">
                    <p class="meta-label">Annonces similaires</p>
                    <div class="space-y-2">
                        <?php foreach ($similarAds as $ad): ?>
                            <a href="routeur.php?action=item&id=<?= $ad['id'] ?>" class="similar-item">
                                <img src="/assets/img/<?= getImageByAnnonceId($pdo, $ad['id']) ?: 'default.png' ?>" alt="Thumbnail">
                                <div>
                                    <h4><?= htmlspecialchars($ad['titre']) ?></h4>
                                    <p><?= number_format($ad['prix'], 2, ',', ' ') ?> €</p>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </section>
            </aside>
        </div>
    <?php endif; ?>
</main>


<script>
    let currentIndex = 0;
    const track = document.getElementById('carousel-track');
    const total = <?= count($allImages ?? []) ?>;

    function moveCarousel(dir) {
        if (total <= 1) return;
        currentIndex = (currentIndex + dir + total) % total;
        track.style.transform = `translateX(-${currentIndex * 100}%)`;
    }

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

    // Initialiser l'état du bouton favoris
    document.addEventListener('DOMContentLoaded', function() {
        const btn = document.getElementById('item-favoris-btn');
        if (!btn) return;

        const articleId = <?= $product['id'] ?? 0 ?>;
        
        // Vérifier l'état initial
        fetch('/routeur.php?action=favoris_ajax', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=check&article_id=' + articleId
        })
        .then(response => response.json())
        .then(data => {
            if (data.is_favoris) {
                updateItemFavorisButton(true);
            }
        })
        .catch(error => console.error('Error checking favoris:', error));
    });

    function toggleItemFavoris(articleId, button) {
        const isFavoris = button.classList.contains('filled');
        const action = isFavoris ? 'remove' : 'add';

        fetch('/routeur.php?action=favoris_ajax', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=' + action + '&article_id=' + articleId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateItemFavorisButton(!isFavoris);
            }
        })
        .catch(error => console.error('Error:', error));
    }

    function updateItemFavorisButton(isFavoris) {
        const btn = document.getElementById('item-favoris-btn');
        const icon = document.getElementById('item-favoris-icon');
        const text = document.getElementById('item-favoris-text');

        if (isFavoris) {
            btn.classList.add('filled');
            btn.style.backgroundColor = '#fef2f2';
            btn.style.borderColor = '#ef4444';
            btn.style.color = '#ef4444';
            icon.textContent = '♥';
            text.textContent = 'Retirer des favoris';
        } else {
            btn.classList.remove('filled');
            btn.style.backgroundColor = 'white';
            btn.style.borderColor = '#ef4444';
            btn.style.color = '#ef4444';
            icon.textContent = '♡';
            text.textContent = 'Ajouter aux favoris';
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
</body>
</html>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Catalogue - LeCoinCarré</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/assets/css/catalogue.css">


</head>
<body class="bg-[#EDFCFD] flex flex-col min-h-screen">
<?php include '../templates/header.php'; ?>

    <main class="max-w-7xl mx-auto px-4 py-8 w-full flex-grow">
    <!-- SECTION FILTRES -->
    <section class="bg-white p-6 mb-10">
        <form action="/routeur.php" method="GET" class="space-y-4">
            <input type="hidden" name="action" value="catalogue">

            <!-- Ligne 1 -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase text-gray-400 mb-2">Que cherchez-vous ?</label>
                    <input type="text" name="search" value="<?= htmlspecialchars($filters['search'] ?? '') ?>"
                           placeholder="Ex: Vélo, iPhone..." class="w-full rounded-lg p-2.5 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-gray-400 mb-2">Catégorie</label>
                    <select name="categorie" class="w-full rounded-lg p-2.5 outline-none">
                        <option value="">Toutes les catégories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= (isset($filters['categorie']) && $filters['categorie'] == $cat['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['nom']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-gray-400 mb-2">Ville</label>
                    <input type="text" name="ville" value="<?= htmlspecialchars($filters['ville'] ?? '') ?>"
                           placeholder="Ex: Paris" class="w-full rounded-lg p-2.5 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-gray-400 mb-2">Distance (km)</label>
                    <input type="number" name="distance" value="<?= htmlspecialchars($filters['distance'] ?? '') ?>"
                           placeholder="Ex: 50" min="0" step="1" class="w-full rounded-lg p-2.5 outline-none">
                </div>
            </div>

            <!-- Ligne 2 -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                <div>
                    <label class="block text-xs font-bold uppercase text-gray-400 mb-2">Prix Min</label>
                    <input type="number" name="prix_min" value="<?= htmlspecialchars($filters['prix_min'] ?? '') ?>"
                           placeholder="€" class="w-full rounded-lg p-2.5 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-gray-400 mb-2">Prix Max</label>
                    <input type="number" name="prix_max" value="<?= htmlspecialchars($filters['prix_max'] ?? '') ?>"
                           placeholder="€" class="w-full rounded-lg p-2.5 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-gray-400 mb-2">Tri</label>
                    <select name="tri" class="w-full rounded-lg p-2.5 outline-none">
                        <option value="date_recent" <?= (($filters['tri'] ?? 'date_recent') === 'date_recent') ? 'selected' : '' ?>>Date</option>
                        <option value="date_ancien" <?= (($filters['tri'] ?? '') === 'date_ancien') ? 'selected' : '' ?>>Date ancienne</option>
                        <option value="prix_min" <?= (($filters['tri'] ?? '') === 'prix_min') ? 'selected' : '' ?>>Prix min</option>
                        <option value="prix_max" <?= (($filters['tri'] ?? '') === 'prix_max') ? 'selected' : '' ?>>Prix max</option>
                        <option value="distance" <?= (($filters['tri'] ?? '') === 'distance') ? 'selected' : '' ?>>Distance</option>
                    </select>
                </div>

                <button type="submit" class="px-6 py-2.5 font-bold transition-all rounded-lg bg-blue-500 text-white hover:bg-blue-600">
                    Appliquer
                </button>
            </div>
        </form>
    </section>

    <!-- GRILLE D'ARTICLES -->
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-6">
        <?php if (!empty($results)): ?>
            <?php foreach ($results as $item): ?>
                <?php $img = getImageByAnnonceId($pdo, $item['id']) ?: 'default.png'; ?>
                <div class="group relative bg-white rounded-lg shadow hover:shadow-lg transition-shadow overflow-hidden">
                    <a href="/routeur.php?action=item&id=<?= $item['id'] ?>" class="block">
                        <img src="/assets/img/<?= htmlspecialchars($img) ?>" alt="Produit" class="w-full h-48 object-cover">
                        <div class="p-4">
                            <h3 class="font-bold truncate"><?= htmlspecialchars($item['titre']) ?></h3>
                            <p class="text-blue-600"><?= number_format($item['prix'], 2, ',', ' ') ?> €</p>

                            <div class="flex justify-between items-center mt-4">
                                <span class="text-gray-400 text-[10px] font-bold uppercase">
                                    <?= htmlspecialchars($item['ville_nom']) ?>
                                    <?php if (!empty($item['distance'])): ?>
                                        <span class="text-blue-500 ml-1">(<?= htmlspecialchars($item['distance']) ?> km)</span>
                                    <?php endif; ?>
                                </span>
                                <span class="text-gray-400 text-[10px] font-bold uppercase"><?= htmlspecialchars($item['categorie_nom']) ?></span>
                            </div>
                        </div>
                    </a>

                    <!-- BOUTON FAVORIS -->
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <button class="favoris-btn absolute top-2 right-2 text-2xl opacity-80 hover:opacity-100 transition-opacity" 
                                data-article-id="<?= $item['id'] ?>"
                                title="Ajouter aux favoris"
                                style="background: rgba(255, 255, 255, 0.9); border: none; border-radius: 50%; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #ef4444; padding: 0;">
                            <span class="favoris-icon">♡</span>
                        </button>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-span-full text-center py-20 text-gray-400">
                <p class="text-lg">Aucun article trouvé.</p>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php include '../templates/footer.php'; ?>

<script>
    
document.addEventListener('DOMContentLoaded', function() {
    const favorisButtons = document.querySelectorAll('.favoris-btn');
    
    // Ajouter les écouteurs d'événements
    favorisButtons.forEach(button => {
        const articleId = button.dataset.articleId;
        
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
                button.classList.add('filled');
                button.style.color = '#ef4444';
                button.querySelector('.favoris-icon').textContent = '♥';
            }
        })
        .catch(error => console.error('Error checking favoris:', error));
        
        // Ajouter le clic
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            toggleFavoris(articleId, this);
        });
    });
});

function toggleFavoris(articleId, button) {
    const isFilled = button.classList.contains('filled');
    const action = isFilled ? 'remove' : 'add';

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
            if (isFilled) {
                button.classList.remove('filled');
                button.style.color = '#ef4444';
                const icon = button.querySelector('.favoris-icon');
                icon.textContent = '♡';
            } else {
                button.classList.add('filled');
                button.style.color = '#ef4444';
                const icon = button.querySelector('.favoris-icon');
                icon.textContent = '♥';
            }
        } else {
            console.error('Error:', data.error);
        }
    })
    .catch(error => console.error('Error:', error));
}
</script>
</body>
</html>
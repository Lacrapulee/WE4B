<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mes Favoris - LeCoinCarré</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/assets/css/catalogue.css">
</head>
<body class="bg-[#EDFCFD] flex flex-col min-h-screen">
<?php include '../templates/header.php'; ?>

    <main class="max-w-7xl mx-auto px-4 py-8 w-full flex-grow">
        <!-- TITRE -->
        <div class="mb-8">
            <h1 class="text-4xl font-extrabold text-gray-900">Mes Favoris</h1>
            <p class="text-gray-600 mt-2"><?= count($favoris) ?> article<?= count($favoris) > 1 ? 's' : '' ?> en favoris</p>
        </div>

        <!-- GRILLE D'ARTICLES -->
        <?php if (!empty($favoris)): ?>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-6" id="favoris-grid">
                <?php foreach ($favoris as $item): ?>
                    <?php $img = $images[$item['id']] ?? 'default.png'; ?>
                    <div class="group relative bg-white rounded-lg shadow hover:shadow-lg transition-shadow" data-article-id="<?= $item['id'] ?>">
                        <a href="/routeur.php?action=item&id=<?= $item['id'] ?>" class="block">
                            <img src="/assets/img/<?= htmlspecialchars($img) ?>" alt="Produit" class="w-full h-48 object-cover rounded-t-lg">
                            <div class="p-4">
                                <h3 class="font-bold truncate"><?= htmlspecialchars($item['titre']) ?></h3>
                                <p class="text-blue-600 font-semibold"><?= number_format($item['prix'], 2, ',', ' ') ?> €</p>

                                <div class="flex justify-between items-center mt-2">
                                    <span class="text-gray-400 text-[10px] font-bold uppercase">
                                        <?= htmlspecialchars($item['ville_nom']) ?>
                                    </span>
                                    <span class="text-gray-400 text-[10px] font-bold uppercase"><?= htmlspecialchars($item['categorie_nom']) ?></span>
                                </div>
                            </div>
                        </a>

                        
                        <button class="remove-favoris-btn absolute top-2 right-2 text-2xl opacity-80 hover:opacity-100 transition-opacity" 
                                title="Retirer des favoris"
                                style="background: rgba(255, 255, 255, 0.9); border: none; border-radius: 50%; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #ef4444; padding: 0;">
                            ♥
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-20" id="empty-message">
                <p class="text-lg text-gray-600 mb-4">Vous n'avez pas encore d'articles en favoris.</p>
                <a href="/routeur.php?action=catalogue" class="inline-block px-6 py-3 bg-blue-500 text-white font-bold rounded-lg hover:bg-blue-600 transition-colors">
                    Consulter le catalogue
                </a>
            </div>
        <?php endif; ?>
    </main>

<?php include '../templates/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const buttons = document.querySelectorAll('.remove-favoris-btn');
    
    buttons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const card = this.closest('[data-article-id]');
            const articleId = card.dataset.articleId;
            
            removeFavorisAjax(articleId, card);
        });
    });
});

function removeFavorisAjax(articleId, card) {
    fetch('/routeur.php?action=favoris_ajax', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=remove&article_id=' + articleId
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Retirer la carte avec animation
            card.style.transition = 'all 0.3s ease-out';
            card.style.opacity = '0';
            card.style.transform = 'scale(0.8)';
            
            setTimeout(() => {
                card.remove();
                
                // Vérifier s'il y a encore des articles
                const grid = document.getElementById('favoris-grid');
                if (grid && grid.children.length === 0) {
                    // Remplacer la grille par le message vide
                    grid.remove();
                    
                    const emptyHtml = `
                        <div class="text-center py-20" id="empty-message">
                            <p class="text-lg text-gray-600 mb-4">Vous n'avez pas encore d'articles en favoris.</p>
                            <a href="/routeur.php?action=catalogue" class="inline-block px-6 py-3 bg-blue-500 text-white font-bold rounded-lg hover:bg-blue-600 transition-colors">
                                Consulter le catalogue
                            </a>
                        </div>
                    `;
                    
                    const main = document.querySelector('main');
                    main.innerHTML = main.innerHTML.replace(/<div class="grid grid-cols-2[\s\S]*?<\/div>\s*$/, '') + emptyHtml;
                    
                    // Mettre à jour le compteur
                    const counter = document.querySelector('main > div.mb-8 > p');
                    if (counter) {
                        counter.textContent = '0 article en favoris';
                    }
                }
                
                // Mettre à jour le compteur
                const counter = document.querySelector('main > div.mb-8 > p');
                if (counter && grid && grid.children.length > 0) {
                    const count = grid.children.length;
                    counter.textContent = count + ' article' + (count > 1 ? 's' : '') + ' en favoris';
                }
            }, 300);
        }
    })
    .catch(error => console.error('Error:', error));
}
</script>
</body>
</html>

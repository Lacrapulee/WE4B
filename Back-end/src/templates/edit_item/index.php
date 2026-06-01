<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $product ? htmlspecialchars($product['titre']) : 'Article introuvable'; ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/edit_item.css">
</head>
<body class="page-body">


<!-- templates/edit_item/index.php -->
<main class="main-container">
    <div class="max-w-3xl mx-auto">
        <div class="flex items-center justify-between mb-8">
            <h1 class="text-3xl font-extrabold">Modifier votre annonce</h1>
            <a href="routeur.php?action=item&id=<?= $product['id'] ?>" class="text-gray-500 hover:text-gray-700">Annuler</a>
        </div>

        <form action="routeur.php?action=edit_item" method="POST" class="edit-card">
            <!-- ID caché pour le traitement -->
            <input type="hidden" name="article_id" value="<?= $product['id'] ?>">
            <input type="hidden" name="update_article" value="1">

            <div class="space-y-6">
                <!-- Titre -->
                <div>
                    <label class="input-label">Titre de l'annonce</label>
                    <input type="text" name="titre" value="<?= htmlspecialchars($product['titre']) ?>" class="form-input" required>
                </div>

                <!-- Catégorie -->
                <div>
                    <label class="input-label">Catégorie</label>
                    <select name="categorie_id" class="form-input">
                        <!-- Ici vous devriez boucler sur vos catégories réelles -->
                        <option value="1" <?= $product['categorie_id'] == 1 ? 'selected' : '' ?>>Électronique</option>
                        <option value="2" <?= $product['categorie_id'] == 2 ? 'selected' : '' ?>>Mode</option>
                        <option value="3" <?= $product['categorie_id'] == 3 ? 'selected' : '' ?>>Maison</option>
                    </select>
                </div>

                <!-- Prix -->
                <div>
                    <label class="input-label">Prix (€)</label>
                    <input type="number" step="0.01" name="prix" value="<?= $product['prix'] ?>" class="form-input" required>
                </div>

                <!-- Description -->
                <div>
                    <label class="input-label">Description</label>
                    <textarea name="description" rows="6" class="form-input"><?= htmlspecialchars($product['description']) ?></textarea>
                </div>

                <div class="pt-4">
                    <button type="submit" class="btn-save">
                        Enregistrer les modifications
                    </button>
                </div>
            </div>
        </form>
        <!-- Formulaire de suppression (séparé pour la sécurité) -->
        <div class="delete-section">
            <h3>Supprimer l'annonce</h3>
            <form action="routeur.php?action=delete_item&id=<?= $product['id'] ?>" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette annonce ? Cette action est irréversible.');">
                <button type="submit" class="btn-delete">Supprimer l'annonce</button>
            </form>
        </div>
        <!-- Optionnel : Gestion des images (simplifié) -->
        <div class="mt-8 p-6 bg-blue-50 rounded-2xl border border-blue-100">
            <h3 class="font-bold text-blue-800 mb-2">Gestion des images</h3>
            <p class="text-sm text-blue-600 mb-4">La modification des images sera bientôt disponible.</p>
            <div class="flex gap-2">
                <?php foreach ($allImages as $img): ?>
                    <img src="/assets/img/<?= htmlspecialchars($img) ?>" class="w-20 h-20 object-cover rounded-lg border-2 border-white shadow-sm">
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</main>
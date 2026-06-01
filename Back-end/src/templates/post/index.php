<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Publier une annonce - LeCoinCarré</title>
    <script src="[https://cdn.tailwindcss.com](https://cdn.tailwindcss.com)"></script>
    <link rel="stylesheet" href="/assets/css/post.css">
</head>
<body class="page-post">


<main class="page-annonce">
    <div class="form-container">
        <h2 class="form-title">Publier une annonce</h2>

        <form action="/routeur.php?action=post" method="post" enctype="multipart/form-data" class="auth-form">

            <section>
                <h3>Informations générales</h3>
                <div class="form-group">
                    <label for="titre">Titre de l'annonce</label>
                    <input type="text" id="titre" name="titre" placeholder="Ex: iPhone 13 comme neuf" required>
                </div>

                <div class="form-group">
                    <label for="categorie">Catégorie</label>
                    <select id="categorie" name="categorie_id" required>
                        <option value="">-- Choisir --</option>
                        <option value="1">Informatique (PC, Consoles, Accessoires)</option>
                        <option value="2">Vehicules (Voitures, Velos, Trottinettes)</option>
                        <option value="3">Immobilier (Ventes et Locations)</option>
                        <option value="4">Maison (Meubles et Deco)</option>
                        <option value="5">Loisirs (Sport, Musique, Jeux)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="description">Description détaillée</label>
                    <textarea id="description" name="description" placeholder="Décrivez l'état, la marque..." required></textarea>
                </div>

                <div class="form-group">
                    <label for="prix">Prix (€)</label>
                    <input type="number" id="prix" step="0.01" name="prix" placeholder="0.00" required>
                </div>
            </section>

            <section>
                <h3>Localisation</h3>
                <div class="form-group">
                    <label for="adresse">Adresse</label>
                    <input type="text" id="adresse" name="coordonnees" placeholder="10 rue de la paix">
                </div>

                <div class="grid-local">
                    <div class="form-group">
                        <label for="ville">Ville</label>
                        <input type="text" id="ville" name="ville_nom" placeholder="Paris" required>
                    </div>
                    <div class="form-group">
                        <label for="cp">Code Postal</label>
                        <input type="text" id="cp" name="code_postal" placeholder="75000" required>
                    </div>
                </div>
            </section>

            <section>
                <h3>Photos</h3>
                <div class="form-group file-input-wrapper">
                    <label for="images">Sélectionnez vos images</label>
                    <input type="file" id="images" name="ma_super_image[]" multiple accept="image/jpeg, image/png, image/webp" required>
                </div>
            </section>

            <button type="submit" class="btn-auth">Publier l'annonce</button>
        </form>
    </div>
</main>

<?php include '../templates/footer.php'; ?>

</body>
</html>
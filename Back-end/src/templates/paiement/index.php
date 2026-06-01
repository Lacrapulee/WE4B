<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($viewData['title']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/paiement.css"> <!-- TON NOUVEAU CSS -->
</head>
<body class="bg-[#EDFCFD] text-gray-900">

<main class="payment-main">
    <?php if ($viewData['errorMessage'] && !$product): ?>
        <!-- État Erreur (Article introuvable) -->
        <section class="payment-card text-center max-w-2xl mx-auto mt-10">
            <h1 class="text-3xl font-black mb-4">Oups !</h1>
            <p class="text-gray-600 mb-8"><?php echo htmlspecialchars($viewData['errorMessage']); ?></p>
            <a href="/routeur.php?action=catalogue" class="btn-pay inline-block px-10">Retour au catalogue</a>
        </section>

    <?php elseif ($product): ?>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">

            <!-- RÉCAPITULATIF ARTICLE -->
            <article class="payment-card">
                <span class="payment-label">Récapitulatif de l'article</span>
                <img src="/assets/img/<?php echo htmlspecialchars($viewData['imageName']); ?>" class="product-preview-img">
                <h2 class="text-2xl font-extrabold mb-2"><?php echo htmlspecialchars($product['titre']); ?></h2>
                <p class="text-2xl font-black text-[#005F83] mb-4"><?php echo htmlspecialchars($viewData['priceLabel']); ?></p>
                <p class="text-gray-500 flex items-center gap-2">
                    <span>📍</span> <?php echo htmlspecialchars($product['ville_nom'] ?? 'Ville inconnue'); ?>
                </p>
            </article>

            <!-- FORMULAIRE DE PAIEMENT -->
            <article class="payment-card">
                <span class="payment-label">Validation du paiement</span>

                <?php if ($viewData['successMessage']): ?>
                    <div class="success-banner">
                        <p class="font-extrabold text-lg">✓ <?php echo htmlspecialchars($viewData['successMessage']); ?></p>
                        <p class="text-sm opacity-80 mt-1">Référence : <?php echo htmlspecialchars($viewData['orderReference']); ?></p>
                    </div>
                    <a href="/routeur.php?action=catalogue" class="btn-pay text-center">Continuer mes achats</a>
                <?php else: ?>

                    <?php if ($viewData['errorMessage']): ?>
                        <div class="bg-red-50 text-red-600 p-4 rounded-xl text-sm mb-6 font-bold border border-red-100">
                            ⚠️ <?php echo htmlspecialchars($viewData['errorMessage']); ?>
                        </div>
                    <?php endif; ?>

                    <form method="post" class="space-y-6">
                        <input type="hidden" name="article_id" value="<?php echo htmlspecialchars($product['id']); ?>">

                        <div class="form-group">
                            <label class="form-label" for="buyer_name">Nom complet</label>
                            <input id="buyer_name" name="buyer_name" type="text" placeholder="Jean Dupont"
                                   value="<?php echo htmlspecialchars($viewData['buyerName']); ?>" class="form-input" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="buyer_email">Adresse Email</label>
                            <input id="buyer_email" name="buyer_email" type="email" placeholder="jean@exemple.com"
                                   value="<?php echo htmlspecialchars($viewData['buyerEmail']); ?>" class="form-input" required>
                        </div>

                        <button type="submit" name="confirm_payment" value="1" class="btn-pay">
                            Payer <?php echo htmlspecialchars($viewData['priceLabel']); ?>
                        </button>

                        <p class="text-center text-xs text-gray-400 mt-4">
                            Paiement sécurisé par simulation. Aucun débit réel.
                        </p>
                    </form>
                <?php endif; ?>
            </article>
        </div>
    <?php endif; ?>
</main>

</body>
</html>
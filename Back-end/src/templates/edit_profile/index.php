<div class="edit-container">
    <h2>Modifier le profil</h2>

    <!-- Messages d'alerte -->
    <?php if ($success): ?>
        <p class="alert success">
            Profil mis à jour avec succès ! 
            <a href="index.php?action=user&id=<?= $user_id ?>">Voir mon profil</a>
        </p>
    <?php endif; ?>

    <?php if ($error): ?>
        <p class="alert error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <!-- Formulaire de modification -->
    <form action="routeur.php?action=edit_profile&id=<?= $user_id ?>" method="POST" class="edit-form">
        
        <div class="form-group">
            <label for="prenom">Prénom</label>
            <input type="text" id="prenom" name="prenom" value="<?= htmlspecialchars($user['prenom'] ?? '') ?>" required>
        </div>

        <div class="form-group">
            <label for="nom">Nom</label>
            <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($user['nom'] ?? '') ?>" required>
        </div>

        <div class="form-group">
            <label for="bio">Ma Bio</label>
            <textarea id="bio" name="bio" placeholder="Parlez-nous de vous..."><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
        </div>
        
        <div class="form-group">
            <?php if (!$isAdmin): ?>
                <label>Email (Non modifiable)</label>
                <input type="email" value="<?= htmlspecialchars($user['email']) ?>" disabled class="input-disabled">
            <?php else: ?>
                <label for="email">Email (Admin uniquement)</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>">

                <label for="telephone">Numéro de téléphone</label>
                <input type="text" id="telephone" name="telephone" value="<?= htmlspecialchars($user['telephone'] ?? '') ?>">

                <label for="adresse">Adresse postale</label>
                <input type="text" id="adresse" name="adresse_postale" value="<?= htmlspecialchars($user['adresse_postale'] ?? '') ?>">
            <?php endif; ?>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-save">Enregistrer les modifications</button>
        </div>
    </form>

    <hr>

    <!-- Formulaire de suppression (séparé pour la sécurité) -->
    <div class="delete-section">
        <h3>Supprimer le compte</h3>
        <form action="routeur.php?action=delete_user&id=<?= $user_id ?>" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce compte ? Cette action est irréversible.');">
            <button type="submit" class="btn-delete">Supprimer le compte</button>
        </form>
    </div>

</div>
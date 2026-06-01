<link rel="stylesheet" href="../assets/css/inscription.css">

<main class="auth-container">
    <div class="auth-card">
        <h1 class="auth-title">Créer un compte</h1>

        <!-- Ajout de l'ID pour le JS et suppression de l'action directe -->
        <form id="form-inscription" class="auth-form">

            <!-- Zone d'affichage des messages d'erreur (masquée par défaut) -->
            <div id="ajax-response" style="display:none; padding: 10px; margin-bottom: 15px; border-radius: 8px; text-align: center; font-weight: 600;"></div>

            <div class="form-group">
                <input type="email" name="email" placeholder="Email" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <input type="password" name="password" id="password" placeholder="Mot de passe" required>
                </div>
                <div class="form-group">
                    <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirmer" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <input type="text" name="prenom" placeholder="Prénom">
                </div>
                <div class="form-group">
                    <input type="text" name="nom" placeholder="Nom">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <input type="text" name="telephone" placeholder="Téléphone">
                </div>
                <div class="form-group">
                    <input type="date" name="date_naissance">
                </div>
            </div>

            <div class="form-group">
                <textarea name="adresse_postale" placeholder="Adresse complète"></textarea>
            </div>

            <button type="submit" class="btn-auth">S'inscrire</button>

            <div style="margin-top: 1.5rem; text-align: center;">
                <p class="form-help">Déjà membre ? <a href="../connexion/index.php" style="color: #005F83; text-decoration: none; font-weight: 600;">Connecte-toi</a></p>
            </div>
        </form>
    </div>
</main>

<script>
    // --- LOGIQUE AJAX ---
    document.querySelector('#form-inscription').addEventListener('submit', function(e) {
        e.preventDefault(); // On n'actualise pas la page

        const form = this;
        const responseDiv = document.querySelector('#ajax-response');
        const submitBtn = form.querySelector('.btn-auth');
        const formData = new FormData(form);

        // UI : On désactive le bouton pendant le chargement
        submitBtn.disabled = true;
        submitBtn.innerText = "Vérification...";

        // On envoie vers le fichier de traitement
        fetch('../routeur.php?action=inscription', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Succès : Petit message vert et redirection
                    responseDiv.style.display = "block";
                    responseDiv.style.backgroundColor = "#dcfce7";
                    responseDiv.style.color = "#166534";
                    responseDiv.innerText = data.message;

                    setTimeout(() => {
                        window.location.href = "/index.php";
                    }, 1500);
                } else {
                    // Erreur : Message rouge et on réactive le bouton
                    responseDiv.style.display = "block";
                    responseDiv.style.backgroundColor = "#fee2e2";
                    responseDiv.style.color = "#991b1b";
                    responseDiv.innerText = data.message;

                    submitBtn.disabled = false;
                    submitBtn.innerText = "S'inscrire";
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                submitBtn.disabled = false;
                submitBtn.innerText = "S'inscrire";
            });
    });
</script>
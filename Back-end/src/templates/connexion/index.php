
    <link rel="stylesheet" href="../assets/css/connexion.css">

    <main class="auth-container">
        <div class="auth-card">
            <h1 class="auth-title">Se connecter</h1>

            <form method="POST" action="../routeur.php?action=connexion" class="auth-form">

                <div class="form-group">
                    <input type="email" id="email" name="email" placeholder="Adresse e-mail" required>

                </div>


                <div class="form-group">
                    <div class="password-wrapper" style="position: relative;">
                        <input type="password" id="password" name="password" placeholder="Mot de passe" required>

                        <span class="toggle-password" style="position: absolute; right: 0; top: 10px; cursor: pointer; color: #6b7280;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </span>
                    </div>
                </div>

                <button type="submit" class="btn-auth">Se connecter</button>

                <div style="margin-top: 1.5rem; text-align: center;">
                    <p class="form-help">Pas encore de compte ? <a href="../inscription/index.php" style="color: #005F83; text-decoration: none; font-weight: 600;">Inscris-toi</a></p>
                </div>
            </form>
        </div>
    </main>

    <script>

        const togglePassword = document.querySelector('.toggle-password');
        const passwordInput = document.querySelector('#password');

        togglePassword.addEventListener('click', () => {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
        });
    </script>


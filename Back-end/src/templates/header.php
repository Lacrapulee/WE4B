
<!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>LeCoinCarré</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
        <link rel="stylesheet" href="/assets/css/style.css">
    </head>

<header class="main-header">
    <div class="logo">
        <a href="/">
            <img src="/assets/img/logo.png" alt="Accueil">
        </a>
    </div>

    <div class="nav-links">
        <?php if (isset($_SESSION['user_id'])): ?>
        <a href="/routeur.php?action=post">Vendre</a>
        <?php endif; ?>
        <a href="<?= isset($_SESSION['user_id']) ? '/routeur.php?action=user&id=' . urlencode($_SESSION['user_id']) : '/routeur.php?action=auth' ?>">Mes annonces</a>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="/routeur.php?action=post">Vendre</a>
            <a href="/routeur.php?action=user&id=<?= urlencode($_SESSION['user_id']) ?>">Mes annonces</a>
            <a href="/routeur.php?action=mes_commandes">Mes commandes</a>
            <a href="/routeur.php?action=favoris">Favoris</a>
            <?php
            require_once __DIR__ . '/../includes/messages_functions.php';
            $unread = countUnreadMessages($pdo, $_SESSION['user_id']);
            ?>
            <a href="/routeur.php?action=messages" style="position: relative;">
                Messages
                <?php if ($unread > 0): ?>
                    <span style="
                        position: absolute;
                        top: -5px;
                        right: -10px;
                        background-color: #ff4444;
                        color: white;
                        border-radius: 50%;
                        padding: 2px 6px;
                        font-size: 12px;
                        font-weight: bold;
                    "><?php echo $unread; ?></span>
                <?php endif; ?>
            </a>
        <?php endif; ?>

    </div>

    <div class="search-bar">
        <form action="/routeur.php?action=catalogue" method="GET">
            <input type="hidden" name="action" value="catalogue">
            <input type="text" name="search" placeholder="Rechercher un produit...">
            <button type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
        </form>
    </div>

    <nav class="header-actions">
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="/routeur.php?action=deconnexion" class="btn-secondary">Déconnexion</a>
        <?php else: ?>
            <a href="/routeur.php?action=auth" class="btn-secondary">Connexion</a>
            <a href="/routeur.php?action=inscription" class="btn-secondary">Inscription</a>
        <?php endif; ?>
        <a href="<?= isset($_SESSION['user_id']) ? '/routeur.php?action=user&id=' . urlencode($_SESSION['user_id']) : '/routeur.php?action=auth' ?>" class="btn-cart"><i class="fa-solid fa-user"></i></a>
    </nav>
</header>
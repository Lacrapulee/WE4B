<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LeCoinCarré - Administration</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

<header class="main-header">


    <div class="nav-links">
        <?php if (isset($_SESSION['user_id']) && isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
            <a href="/routeur.php?action=dashboard"><i class="fa-solid fa-chart-pie"></i> Dashboard</a>
            <a href="/routeur.php?action=catalogue"><i class="fa-solid fa-boxes-stacked"></i> Catalogue Articles</a>
            <a href="/routeur.php?action=user&id=<?= $_SESSION['user_id'] ?? '' ?>"><i class="fa-solid fa-user-gear"></i> Mon profil</a>
        <?php endif; ?>
    </div>

    <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
    <div class="search-bar">
        <form action="/routeur.php" method="GET">
            <input type="hidden" name="action" value="catalogue">
            <input type="text" name="search" placeholder="Rechercher un produit ou un ID..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
            <button type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
        </form>
    </div>
    <?php endif; ?>

    <nav class="header-actions">
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="/routeur.php?action=deconnexion" class="btn-secondary"><i class="fa-solid fa-power-off"></i></a>
        <?php else: ?>
            <a href="/routeur.php?action=auth" class="btn-secondary">Connexion Admin</a>
        <?php endif; ?>
    </nav>
</header>
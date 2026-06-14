<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin - Statistiques API</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f4F6F9; color: #333; margin: 20px; }
        .dashboard-container { max-width: 1000px; margin: 0 auto; }
        h1 { color: #2c3e50; border-bottom: 20px; }
        .card { 
                    background: white; 
                    padding: 25px; 
                    border-radius: 8px; 
                    box-shadow: 0 4px 6px rgba(0,0,0,0.05); 
                    display: block;        /* Passe de inline-block à block */
                    max-width: 1300px;      /* Largeur max de la boîte */
                    margin-top: 20px;
                }        
        .alert-danger { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 4px; margin-bottom: 20px; border-left: 5px solid #dc3545; }
        .chart-img { 
                    display: block; 
                    width: 100%;           /* Prend toute la largeur disponible dans la carte */
                    max-width: 1300px;      /* Mais ne dépasse pas 1000px pour ne pas être géante non plus */
                    height: auto; 
                    margin: 20px auto 0 auto; /* Centre l'image horizontalement */
                    border: 1px solid #ddd; 
                    border-radius: 4px; 
            }    
    </style>
</head>
<body>

<div class="dashboard-container">
    <h1>Tableau de bord de Gestion</h1>
    <p>Bienvenue dans l'espace d'administration réservé.</p>

    <?php if (!empty($dashboardError)): ?>
        <div class="alert alert-danger">
            <strong>Erreur de calcul :</strong> <?= htmlspecialchars($dashboardError) ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <h2>Analyse du trafic d'API (Données MongoDB)</h2>
        <p>Ce graphique est généré en temps réel par Python à partir des logs.</p>
        
        <img src="/img/chart_status.png?v=<?= time(); ?>" class="chart-img" alt="Graphique des requêtes HTTP" />
    </div>
</div>

</body>
</html>
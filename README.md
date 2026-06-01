Projet WE4A -- Création d'un site internet de revente


Pour l'envoie des fichiers de la page post/index.php :
- sudo chown -R www-data:www-data src/public/assets/img
- sudo chmod -R 775 src/public/assets/img
Dans php/8.*/php.ini changer les lignes : 
- upload_max_filesize = 64M
- post_max_size = 65 
- memory_limit = -1 

PHP My admin : http://localhost:8080

User : création de la page : affichage du profil, 

Deux versions : 
- version 'viewer' : le profil avec tout les commentaires, les items vendus et à vendre et les informations du vendeur 
- version 'owner' : le profil avec tout les commentaire, les items vendus et à vendre les infos du owner mais un bouton modification du profil


TODO : 
    
    methode post ajouter Les bonne catégorie V

    ne pas pouvoir acheter son propre article 

    mode super admin

    Les avis

    Le style
    
    "Mes annonces" dans le catalogue

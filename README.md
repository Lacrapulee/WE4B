# Projet WE4B - Plateforme de Revente

Bienvenue sur le projet **WE4B**, une application web complète de revente de particuliers à particuliers, développée avec une architecture associant un front-end Angular et un back-end PHP, le tout orchestré via Docker.

---

## Identifiants de test (Administrateur)

Pour vous connecter en tant qu'administrateur et tester l'ensemble des droits de modération et de gestion :

*   **Email :** `admin@gmail.com`
*   **Mot de passe :** `admin`

*(Ces identifiants sont automatiquement insérés dans la base de données lors du premier lancement via le script d'initialisation `sql/seed.sql`)*

---

## Fonctionnalités principales

*   **Gestion des Annonces (Articles) :** Publication, modification, suppression, classement par catégories hiérarchiques et spécification d'attributs personnalisés.
*   **Système de Messagerie Interne :** Chat en temps réel/interne entre acheteurs et vendeurs à propos d'un article spécifique.
*   **Favoris :** Possibilité de mettre des articles en favoris.
*   **Promotions / Boost d'Annonces :** Options pour mettre en avant une annonce (Bannière "Urgent", affichage en page d'accueil, boost de 7 ou 30 jours).
*   **Système d'Avis et d'Évaluation :** Notation sous forme d'étoiles (1 à 5) et commentaires entre acheteurs et vendeurs après transaction.
*   **Suivi des Ventes :** Génération de références de transaction, gestion du statut du paiement et de la livraison.
*   **Espace Administrateur :** Tableau de bord pour superviser les utilisateurs, modérer les articles signalés et consulter des statistiques.
*   **Profils Utilisateur dynamiques :**
    *   *Mode Propriétaire (Owner) :* Permet de modifier ses informations, voir ses annonces et modifier ses coordonnées.
    *   *Mode Visiteur (Viewer) :* Affiche les informations publiques du vendeur, ses annonces actives et les avis laissés par la communauté.

---

## Architecture Technique & Services

L'application est entièrement conteneurisée à l'aide de **Docker Compose**. Voici la liste des services configurés :

| Service | Technologie | Port Local / URL | Description |
| :--- | :--- | :--- | :--- |
| **Front-end** | Angular 20+ / Node.js | [http://localhost:4200](http://localhost:4200) | Interface utilisateur réactive et dynamique. |
| **Back-end** | PHP 8.2 / Apache | [http://localhost:8000](http://localhost:8000) | API REST personnalisée avec routeur PHP. |
| **Base de Données SQL** | MySQL 8.0 | `localhost:3306` | Stockage des utilisateurs, articles, avis, transactions, etc. |
| **Base de Données NoSQL** | MongoDB | `localhost:27018` | Stockage des images et autres métadonnées complexes. |
| **Administration SQL** | phpMyAdmin | [http://localhost:8080](http://localhost:8080) | Interface web pour gérer la base de données MySQL. |
| **Administration NoSQL** | Mongo Express | [http://localhost:8081](http://localhost:8081) | Interface web pour administrer MongoDB. |

---

## Installation et Lancement

### 1. Prérequis
Assurez-vous d'avoir installé **Docker** et **Docker Compose** sur votre machine.

### 2. Démarrage des conteneurs
Exécutez la commande suivante à la racine du projet pour construire et démarrer l'ensemble des services :
```bash
docker compose up --build
```
Les bases de données MySQL et MongoDB s'initialisent automatiquement avec les schémas (`sql/init.sql`) et les données de test (`sql/seed.sql`).

---

## Astuces et configurations système

### Droits d'écriture pour l'upload d'images
Si vous rencontrez des problèmes lors de l'envoi de fichiers depuis la page de publication d'annonces, appliquez les commandes suivantes dans votre conteneur ou serveur web :
```bash
sudo chown -R www-data:www-data Back-end/src/public/assets/img
sudo chmod -R 775 Back-end/src/public/assets/img
```

### Configuration PHP (`php.ini`) recommandée
Pour accepter le téléversement d'images volumineuses, les variables PHP suivantes ont été ajustées :
*   `upload_max_filesize = 64M`
*   `post_max_size = 65M`
*   `memory_limit = -1`

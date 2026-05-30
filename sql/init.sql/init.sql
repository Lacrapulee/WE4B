-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `WE4ADB`
--

DROP DATABASE IF EXISTS `WE4ADB`;
CREATE DATABASE `WE4ADB`;
USE `WE4ADB`;

-- Structure de la table `users`
CREATE TABLE `users` (
  `id` char(36) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nom` varchar(100) DEFAULT NULL,
  `prenom` varchar(100) DEFAULT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `date_naissance` date DEFAULT NULL,
  `adresse_postale` text DEFAULT NULL,
  `is_admin` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Structure de la table `categories`
CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `categories` (`id`, `nom`, `description`) VALUES
(1, 'Informatique', 'PC, consoles, accessoires'),
(2, 'Vehicules', 'Voitures, velos, trottinettes'),
(3, 'Immobilier', 'Ventes et locations'),
(4, 'Maison', 'Meubles et deco'),
(5, 'Loisirs', 'Sport, musique, jeux'),
(6, 'Vêtements', 'T-shirts, pantalons, vestes et tenues du quotidien'),
(7, 'Chaussures', 'Baskets, chaussures de ville, bottes et sandales'),
(8, 'Accessoires mode', 'Sacs, ceintures, lunettes et montres'),
(9, 'Bijoux', 'Bagues, colliers, bracelets et accessoires précieux'),
(10, 'Beauté', 'Cosmétiques, parfums et soins');

-- Structure de la table `articles`
CREATE TABLE `articles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vendeur_id` char(36) NOT NULL,
  `categorie_id` int(11) NOT NULL,
  `titre` varchar(150) NOT NULL,
  `description` text NOT NULL,
  `prix` decimal(12,2) NOT NULL,
  `statut` enum('en_ligne','vendu','archive','banni') DEFAULT 'en_ligne',
  `coordonnees` point NOT NULL,
  `ville_nom` varchar(100) DEFAULT NULL,
  `code_postal` varchar(10) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_art_vendeur` FOREIGN KEY (`vendeur_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_art_categorie` FOREIGN KEY (`categorie_id`) REFERENCES `categories` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Index spatial pour les articles
ALTER TABLE `articles` ADD SPATIAL KEY `idx_coords` (`coordonnees`);

-- Structure de la table `article_images`
CREATE TABLE `article_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `article_id` int(11) NOT NULL,
  `url_image` varchar(512) NOT NULL,
  `est_principale` tinyint(1) DEFAULT 0,
  `ordre` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_img_article` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Structure de la table `avis`
CREATE TABLE `avis` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `article_id` int(11) DEFAULT NULL,
  `expediteur_id` char(36) NOT NULL,
  `destinataire_id` char(36) NOT NULL,
  `note` tinyint(4) NOT NULL CHECK (`note` between 1 and 5),
  `commentaire` text DEFAULT NULL,
  `date_avis` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_avis_article` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_avis_exp` FOREIGN KEY (`expediteur_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_avis_dest` FOREIGN KEY (`destinataire_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Structure de la table `conversations`
CREATE TABLE `conversations` (
  `id` char(36) NOT NULL,
  `article_id` int(11) DEFAULT NULL,
  `acheteur_id` char(36) NOT NULL,
  `vendeur_id` char(36) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_conv` (`article_id`,`acheteur_id`,`vendeur_id`),
  CONSTRAINT `fk_conv_article` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_conv_acheteur` FOREIGN KEY (`acheteur_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_conv_vendeur` FOREIGN KEY (`vendeur_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Structure de la table `messages`
CREATE TABLE `messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `conversation_id` char(36) NOT NULL,
  `expediteur_id` char(36) NOT NULL,
  `contenu` text NOT NULL,
  `lu` tinyint(1) DEFAULT 0,
  `date_envoi` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_msg_conv` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_msg_exp` FOREIGN KEY (`expediteur_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Structure de la table `favoris`
CREATE TABLE `favoris` (
  `user_id` char(36) NOT NULL,
  `article_id` int(11) NOT NULL,
  `ajoute_le` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`user_id`,`article_id`),
  CONSTRAINT `fk_fav_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_fav_art` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Structure de la table `ventes`
DROP TABLE IF EXISTS `ventes`;
CREATE TABLE `ventes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reference` varchar(30) NOT NULL,
  `article_id` int(11) NOT NULL,
  `vendeur_id` char(36) NOT NULL,
  `acheteur_id` char(36) DEFAULT NULL,
  `acheteur_nom` varchar(150) NOT NULL,
  `acheteur_email` varchar(255) NOT NULL,
  `montant` decimal(12,2) NOT NULL,
  `statut` enum('paye', 'recu') NOT NULL DEFAULT 'paye', 
  `statut_paiement` enum('valide', 'refuse', 'rembourse') NOT NULL DEFAULT 'valide',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_ventes_ref` (`reference`),
  UNIQUE KEY `uk_ventes_art` (`article_id`),
  CONSTRAINT `fk_ventes_art` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`),
  CONSTRAINT `fk_ventes_vend` FOREIGN KEY (`vendeur_id`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_ventes_acheteur` FOREIGN KEY (`acheteur_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
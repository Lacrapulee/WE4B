-- phpMyAdmin SQL Dump
-- MariaDB 10.4 / PHP 8.2

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

DROP DATABASE IF EXISTS `WE4BDB`;
CREATE DATABASE `WE4BDB`
CHARACTER SET utf8mb4
COLLATE utf8mb4_general_ci;

USE `WE4BDB`;

-- ==========================
-- USERS
-- ==========================

CREATE TABLE `users` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `email` VARCHAR(255) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `nom` VARCHAR(100) DEFAULT NULL,
    `prenom` VARCHAR(100) DEFAULT NULL,
    `telephone` VARCHAR(20) DEFAULT NULL,
    `date_naissance` DATE DEFAULT NULL,
    `adresse_postale` TEXT DEFAULT NULL,
    `is_admin` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_users_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================
-- CATEGORIES
-- ==========================

CREATE TABLE `categories` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `nom` VARCHAR(100) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `parent_id` INT DEFAULT NULL,
    PRIMARY KEY (`id`),
    CONSTRAINT `fk_categories_parent`
        FOREIGN KEY (`parent_id`)
        REFERENCES `categories`(`id`)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================
-- ARTICLES
-- ==========================

CREATE TABLE `articles` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `vendeur_id` INT NOT NULL,
    `categorie_id` INT NOT NULL,
    `titre` VARCHAR(150) NOT NULL,
    `description` TEXT NOT NULL,
    `prix` DECIMAL(12,2) NOT NULL,
    `statut` ENUM('en_ligne','vendu','archive','banni')
        DEFAULT 'en_ligne',
    `coordonnees` POINT NOT NULL,
    `ville_nom` VARCHAR(100) DEFAULT NULL,
    `code_postal` VARCHAR(10) DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    SPATIAL KEY `idx_coords` (`coordonnees`),
    CONSTRAINT `fk_art_vendeur`
        FOREIGN KEY (`vendeur_id`)
        REFERENCES `users`(`id`)
        ON DELETE CASCADE,
    CONSTRAINT `fk_art_categorie`
        FOREIGN KEY (`categorie_id`)
        REFERENCES `categories`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================
-- ATTRIBUTS
-- ==========================

CREATE TABLE `attributs_definition` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `nom` VARCHAR(50) NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `article_attributs_valeurs` (
    `article_id` INT NOT NULL,
    `attribut_id` INT NOT NULL,
    `valeur` TEXT NOT NULL,
    PRIMARY KEY (`article_id`,`attribut_id`),
    CONSTRAINT `fk_attr_article`
        FOREIGN KEY (`article_id`)
        REFERENCES `articles`(`id`)
        ON DELETE CASCADE,
    CONSTRAINT `fk_attr_def`
        FOREIGN KEY (`attribut_id`)
        REFERENCES `attributs_definition`(`id`)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================
-- IMAGES
-- ==========================

CREATE TABLE `article_images` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `article_id` INT NOT NULL,
    `url_image` VARCHAR(512) NOT NULL,
    `est_principale` TINYINT(1) DEFAULT 0,
    `ordre` INT DEFAULT 0,
    PRIMARY KEY (`id`),
    CONSTRAINT `fk_img_article`
        FOREIGN KEY (`article_id`)
        REFERENCES `articles`(`id`)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================
-- AVIS
-- ==========================

CREATE TABLE `avis` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `article_id` INT DEFAULT NULL,
    `expediteur_id` INT NOT NULL,
    `destinataire_id` INT NOT NULL,
    `note` TINYINT NOT NULL CHECK (`note` BETWEEN 1 AND 5),
    `commentaire` TEXT DEFAULT NULL,
    `date_avis` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    CONSTRAINT `fk_avis_article`
        FOREIGN KEY (`article_id`)
        REFERENCES `articles`(`id`)
        ON DELETE SET NULL,
    CONSTRAINT `fk_avis_exp`
        FOREIGN KEY (`expediteur_id`)
        REFERENCES `users`(`id`)
        ON DELETE CASCADE,
    CONSTRAINT `fk_avis_dest`
        FOREIGN KEY (`destinataire_id`)
        REFERENCES `users`(`id`)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================
-- CONVERSATIONS
-- ==========================

CREATE TABLE `conversations` (
    `id` CHAR(36) NOT NULL,
    `article_id` INT DEFAULT NULL,
    `acheteur_id` INT NOT NULL,
    `vendeur_id` INT NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_conv` (`article_id`,`acheteur_id`,`vendeur_id`),
    CONSTRAINT `fk_conv_article`
        FOREIGN KEY (`article_id`)
        REFERENCES `articles`(`id`)
        ON DELETE SET NULL,
    CONSTRAINT `fk_conv_acheteur`
        FOREIGN KEY (`acheteur_id`)
        REFERENCES `users`(`id`)
        ON DELETE CASCADE,
    CONSTRAINT `fk_conv_vendeur`
        FOREIGN KEY (`vendeur_id`)
        REFERENCES `users`(`id`)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================
-- MESSAGES
-- ==========================

CREATE TABLE `messages` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `conversation_id` CHAR(36) NOT NULL,
    `expediteur_id` INT NOT NULL,
    `contenu` TEXT NOT NULL,
    `lu` TINYINT(1) DEFAULT 0,
    `date_envoi` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    CONSTRAINT `fk_msg_conv`
        FOREIGN KEY (`conversation_id`)
        REFERENCES `conversations`(`id`)
        ON DELETE CASCADE,
    CONSTRAINT `fk_msg_exp`
        FOREIGN KEY (`expediteur_id`)
        REFERENCES `users`(`id`)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================
-- FAVORIS
-- ==========================

CREATE TABLE `favoris` (
    `user_id` INT NOT NULL,
    `article_id` INT NOT NULL,
    `ajoute_le` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`user_id`,`article_id`),
    CONSTRAINT `fk_fav_user`
        FOREIGN KEY (`user_id`)
        REFERENCES `users`(`id`)
        ON DELETE CASCADE,
    CONSTRAINT `fk_fav_art`
        FOREIGN KEY (`article_id`)
        REFERENCES `articles`(`id`)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================
-- PROMOTIONS
-- ==========================

CREATE TABLE `promotions` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `article_id` INT NOT NULL,
    `type_promo` ENUM('urgent','accueil','boost_7j','boost_30j') NOT NULL,
    `date_debut` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `date_fin` TIMESTAMP NULL DEFAULT NULL,
    `est_actif` TINYINT(1) DEFAULT 1,
    PRIMARY KEY (`id`),
    CONSTRAINT `fk_promo_art`
        FOREIGN KEY (`article_id`)
        REFERENCES `articles`(`id`)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================
-- VENTES
-- ==========================

CREATE TABLE `ventes` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `reference` VARCHAR(30) NOT NULL,
    `article_id` INT NOT NULL,
    `vendeur_id` INT NOT NULL,
    `acheteur_id` INT DEFAULT NULL,
    `acheteur_nom` VARCHAR(150) NOT NULL,
    `acheteur_email` VARCHAR(255) NOT NULL,
    `montant` DECIMAL(12,2) NOT NULL,
    `statut` ENUM('paye','recu') NOT NULL DEFAULT 'paye',
    `statut_paiement` ENUM('valide','refuse','rembourse') NOT NULL DEFAULT 'valide',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_ventes_ref` (`reference`),
    UNIQUE KEY `uk_ventes_art` (`article_id`),
    CONSTRAINT `fk_ventes_art`
        FOREIGN KEY (`article_id`)
        REFERENCES `articles`(`id`),
    CONSTRAINT `fk_ventes_vend`
        FOREIGN KEY (`vendeur_id`)
        REFERENCES `users`(`id`),
    CONSTRAINT `fk_ventes_acheteur`
        FOREIGN KEY (`acheteur_id`)
        REFERENCES `users`(`id`)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

COMMIT;
-- ==========================================================
-- FICHIER DE SEEDING : seed.sql
-- Base de données : WE4ADB
-- ==========================================================

-- 0. CONFIGURATION DE LA SESSION
SET NAMES utf8mb4 COLLATE utf8mb4_general_ci;
USE WE4ADB;

-- Décocher si vous voulez supprimer les anciennes données
-- SET FOREIGN_KEY_CHECKS = 0;
-- DELETE FROM `article_attributs_valeurs`;
-- DELETE FROM `article_images`;
-- DELETE FROM `messages`;
-- DELETE FROM `conversations`;
-- DELETE FROM `favoris`;
-- DELETE FROM `avis`;
-- DELETE FROM `promotions`;
-- DELETE FROM `articles`;
-- DELETE FROM `categories`;
-- DELETE FROM `users`;
-- SET FOREIGN_KEY_CHECKS = 1;

-- 1. CRÉATION DES UTILISATEURS
INSERT INTO `users` (`id`, `email`, `password`, `nom`, `prenom`, `telephone`) VALUES
('u1-uuid-placeholder', 'jean.dupont@gmail.com', 'hash123', 'Dupont', 'Jean', '0611223344'),
('u2-uuid-placeholder', 'marie.curie@utbm.fr', 'hash123', 'Curie', 'Marie', '0655443322'),
('u3-uuid-placeholder', 'lucas.bertrand@orange.fr', 'hash123', 'Bertrand', 'Lucas', '0788990011'),
('u4-uuid-placeholder', 'sophie.fonfec@yahoo.fr', 'hash123', 'Fonfec', 'Sophie', '0600112233'),
('u5-uuid-placeholder', 'admin.boutique@utbm.fr', 'hash123', 'Admin', 'Boutique', '0384001122'),
('u6-paris-uuid', 'thomas.paris@gmail.com', 'hash123', 'Rousseau', 'Thomas', '0612345678'),
('u7-brest-uuid', 'isabelle.brest@gmail.com', 'hash123', 'Lefevre', 'Isabelle', '0698765432'),
('u8-montpellier-uuid', 'sebastien.mtp@gmail.com', 'hash123', 'Moreau', 'Sebastien', '0687654321'),
('u9-lyon-uuid', 'anne.lyon@gmail.com', 'hash123', 'Garneau', 'Anne', '0676543210'),
('u10-marseille-uuid', 'pierre.marseille@gmail.com', 'hash123', 'Blanc', 'Pierre', '0665432109');

-- Variables pour les IDs utilisateurs (forcées en general_ci)
SET @u1 = 'u1-uuid-placeholder' COLLATE utf8mb4_general_ci;
SET @u2 = 'u2-uuid-placeholder' COLLATE utf8mb4_general_ci;
SET @u3 = 'u3-uuid-placeholder' COLLATE utf8mb4_general_ci;
SET @u4 = 'u4-uuid-placeholder' COLLATE utf8mb4_general_ci;
SET @u5 = 'u5-uuid-placeholder' COLLATE utf8mb4_general_ci;
SET @u6 = 'u6-paris-uuid' COLLATE utf8mb4_general_ci;
SET @u7 = 'u7-brest-uuid' COLLATE utf8mb4_general_ci;
SET @u8 = 'u8-montpellier-uuid' COLLATE utf8mb4_general_ci;
SET @u9 = 'u9-lyon-uuid' COLLATE utf8mb4_general_ci;
SET @u10 = 'u10-marseille-uuid' COLLATE utf8mb4_general_ci;

-- 2. CRÉATION DES CATÉGORIES
INSERT INTO `categories` (`id`, `nom`, `description`) VALUES 
(1, 'Informatique', 'PC, Consoles, Accessoires'),
(2, 'Vehicules', 'Voitures, Velos, Trottinettes'),
(3, 'Immobilier', 'Ventes et Locations'),
(4, 'Maison', 'Meubles et Deco'),
(5, 'Loisirs', 'Sport, Musique, Jeux');

-- 3. INSERTION DES ARTICLES
INSERT INTO `articles` (`vendeur_id`, `categorie_id`, `titre`, `description`, `prix`, `statut`, `coordonnees`, `ville_nom`, `code_postal`) VALUES
-- Informatique (Jean @u1 - Belfort)
(@u1, 1, 'MacBook Pro 14', 'M2, 16Go RAM, comme neuf.', 1800.00, 'en_ligne', POINT(47.64, 6.85), 'Belfort', '90000'),
(@u1, 1, 'iPhone 15', 'Noir, 128Go, debloque.', 850.00, 'en_ligne', POINT(47.64, 6.85), 'Belfort', '90000'),
(@u1, 1, 'Clavier Corsair K70', 'Switch Red, RGB.', 110.00, 'en_ligne', POINT(47.64, 6.85), 'Belfort', '90000'),
(@u1, 1, 'Souris MX Master 3S', 'Ideal productivite.', 75.00, 'en_ligne', POINT(47.64, 6.85), 'Belfort', '90000'),
(@u1, 1, 'Casque Bose QC45', 'Reduction de bruit active.', 190.00, 'en_ligne', POINT(47.64, 6.85), 'Belfort', '90000'),
(@u1, 1, 'ecran Dell 27 4K', 'Dalle IPS ultra precise.', 350.00, 'en_ligne', POINT(47.64, 6.85), 'Belfort', '90000'),
(@u1, 1, 'PC Portable Asus Zenbook 14', 'Intel Core i7, 16Go RAM, SSD 512Go.', 999.00, 'en_ligne', POINT(47.64, 6.85), 'Belfort', '90000'),
(@u1, 1, 'Moniteur LG 34 Ultrawide', 'Ecran incurve 34 pouces, parfait teletravail.', 310.00, 'en_ligne', POINT(47.64, 6.85), 'Belfort', '90000'),
-- Vehicules (Marie @u2 - Montbeliard)
(@u2, 2, 'Peugeot 208', '1.2 PureTech, 50k km.', 11500.00, 'en_ligne', POINT(47.51, 6.79), 'Montbeliard', '25200'),
(@u2, 2, 'VTT Specialized', 'Tout suspendu, Taille M.', 1200.00, 'en_ligne', POINT(47.51, 6.79), 'Montbeliard', '25200'),
(@u2, 2, 'Renault Clio 5', 'Essence, 68k km, entretien a jour.', 9800.00, 'en_ligne', POINT(47.51, 6.79), 'Montbeliard', '25200'),
(@u2, 2, 'Trottinette Xiaomi Pro 2', 'Batterie en bon etat, autonomie confortable.', 260.00, 'en_ligne', POINT(47.51, 6.79), 'Montbeliard', '25200'),
-- Maison (Lucas @u3 - Sevenans)
(@u3, 4, 'Canape d angle convertible', 'Gris fonce, 4 places.', 450.00, 'en_ligne', POINT(47.58, 6.81), 'Sevenans', '90400'),
(@u3, 4, 'Table a manger Chene', 'Avec 6 chaises assorties.', 320.00, 'en_ligne', POINT(47.58, 6.81), 'Sevenans', '90400'),
(@u3, 4, 'Lit coffre 160x200', 'Sommiers inclus, tres bon etat.', 290.00, 'en_ligne', POINT(47.58, 6.81), 'Sevenans', '90400'),
(@u3, 4, 'Bibliotheque bois massif', '5 etageres, finition claire.', 140.00, 'en_ligne', POINT(47.58, 6.81), 'Sevenans', '90400'),
-- Loisirs (Sophie @u4 - Hericourt)
(@u4, 5, 'Guitare Fender Stratocaster', 'Made in Mexico, Sunburst.', 650.00, 'en_ligne', POINT(47.58, 6.79), 'Hericourt', '70400'),
(@u4, 5, 'Halteres 20kg', 'Lot de 2, fonte.', 40.00, 'en_ligne', POINT(47.58, 6.79), 'Hericourt', '70400'),
(@u4, 5, 'Velo de route Triban RC120', 'Cadre aluminium, ideal debutant.', 420.00, 'en_ligne', POINT(47.58, 6.79), 'Hericourt', '70400'),
(@u4, 5, 'Piano numerique Yamaha P45', '88 touches, support pedalier.', 380.00, 'en_ligne', POINT(47.58, 6.79), 'Hericourt', '70400'),
-- Immobilier (Admin @u5 - Belfort/Montbeliard)
(@u5, 3, 'Studio Centre Belfort', 'Proche gare, 25m2.', 55000.00, 'en_ligne', POINT(47.63, 6.86), 'Belfort', '90000'),
(@u5, 3, 'T3 Montbeliard', 'Vue sur le chateau, parking.', 125000.00, 'en_ligne', POINT(47.51, 6.79), 'Montbeliard', '25200'),
(@u5, 3, 'T2 meuble centre Montbeliard', 'Residence calme, ideal investissement.', 69000.00, 'en_ligne', POINT(47.51, 6.79), 'Montbeliard', '25200'),
(@u5, 3, 'Garage securise proche gare', 'Acces facile, portail electrique.', 12000.00, 'en_ligne', POINT(47.63, 6.86), 'Belfort', '90000'),
-- Articles croises pour diversifier les vendeurs
(@u1, 2, 'VTT Trek Marlin 7', 'Shimano Deore, tres bon etat.', 780.00, 'en_ligne', POINT(47.64, 6.85), 'Belfort', '90000'),
(@u2, 1, 'iPad Air 5', 'WiFi, 64Go, etat impeccable.', 490.00, 'en_ligne', POINT(47.51, 6.79), 'Montbeliard', '25200'),
(@u3, 3, 'Parking couvert centre Belfort', 'Place securisee, acces badge.', 8500.00, 'en_ligne', POINT(47.58, 6.81), 'Sevenans', '90400'),
(@u4, 1, 'Casque gaming SteelSeries Arctis 7', 'Sans fil, son clair, micro retractable.', 95.00, 'en_ligne', POINT(47.58, 6.79), 'Hericourt', '70400'),
(@u5, 4, 'Canape convertible scandinave', 'Beige, 3 places, tres bon confort.', 520.00, 'en_ligne', POINT(47.63, 6.86), 'Belfort', '90000'),
-- Articles Paris (@u6 - 48.8566, 2.3522)
(@u6, 1, 'MacBook Air M1', '13 pouces, excellent etat, batterie 92%.', 950.00, 'en_ligne', POINT(48.8566, 2.3522), 'Paris', '75001'),
(@u6, 1, 'Google Pixel 7 Pro', '256Go, etat comme neuf.', 650.00, 'en_ligne', POINT(48.8566, 2.3522), 'Paris', '75001'),
(@u6, 1, 'Webcam Logitech C920', 'Full HD, parfait pour streaming.', 55.00, 'en_ligne', POINT(48.8566, 2.3522), 'Paris', '75001'),
(@u6, 2, 'Velo Fixie State Bicycle', 'Cadre noir mat, tres stylise.', 380.00, 'en_ligne', POINT(48.8566, 2.3522), 'Paris', '75001'),
(@u6, 4, 'Chaise Eames DSW', 'Coque blanche, pied epoxyee.', 95.00, 'en_ligne', POINT(48.8566, 2.3522), 'Paris', '75001'),
-- Articles Brest (@u7 - 48.3905, -4.4860)
(@u7, 1, 'iMac 27 pouces', 'Intel Core i5, 8Go RAM, Retina 5K.', 1100.00, 'en_ligne', POINT(48.3905, -4.4860), 'Brest', '29200'),
(@u7, 1, 'Clavier mecanique Ducky', 'Cherry MX Brown, RGB.', 145.00, 'en_ligne', POINT(48.3905, -4.4860), 'Brest', '29200'),
(@u7, 2, 'Kayak Ocean Kayak', 'Couleur bleue, bon etat.', 590.00, 'en_ligne', POINT(48.3905, -4.4860), 'Brest', '29200'),
(@u7, 5, 'Planche de surf Catch Surf', 'Longueur 6 pieds, design vintage.', 280.00, 'en_ligne', POINT(48.3905, -4.4860), 'Brest', '29200'),
(@u7, 4, 'Lampe de table industrielle', 'Metal noir et bois, ampoule LED.', 65.00, 'en_ligne', POINT(48.3905, -4.4860), 'Brest', '29200'),
-- Articles Montpellier (@u8 - 43.6108, 3.8767)
(@u8, 1, 'Alienware Gaming Monitor 34', '3440x1440, 100Hz, parfait gaming.', 580.00, 'en_ligne', POINT(43.6108, 3.8767), 'Montpellier', '34000'),
(@u8, 1, 'RTX 3070 Ti GPU', 'Founders Edition, peu utilisee.', 820.00, 'en_ligne', POINT(43.6108, 3.8767), 'Montpellier', '34000'),
(@u8, 5, 'Drone DJI Air 3', 'Quasi neuf, avec tous les accessoires.', 1350.00, 'en_ligne', POINT(43.6108, 3.8767), 'Montpellier', '34000'),
(@u8, 2, 'Skateboard Element', 'Complet, roulements ABEC-7.', 125.00, 'en_ligne', POINT(43.6108, 3.8767), 'Montpellier', '34000'),
(@u8, 4, 'Miroir mural design', 'Cadre dore, dimensions 80x60cm.', 110.00, 'en_ligne', POINT(43.6108, 3.8767), 'Montpellier', '34000'),
-- Articles Lyon (@u9 - 45.7640, 4.8357)
(@u9, 1, 'iPhone 14 Pro Max', 'Espace noir, 256Go, boite complete.', 1050.00, 'en_ligne', POINT(45.7640, 4.8357), 'Lyon', '69000'),
(@u9, 1, 'Sony WH-1000XM4', 'Casque bluetooth, annulation bruit.', 220.00, 'en_ligne', POINT(45.7640, 4.8357), 'Lyon', '69000'),
(@u9, 2, 'Velo Gravel Canyon Grail', 'Taille M, suspension RockShox.', 1200.00, 'en_ligne', POINT(45.7640, 4.8357), 'Lyon', '69000'),
(@u9, 5, 'Roland TR-808', 'Machine a rythme, tres bon etat.', 480.00, 'en_ligne', POINT(45.7640, 4.8357), 'Lyon', '69000'),
(@u9, 4, 'Tapis kilim Turc', 'Couleurs bleu et rouge, 200x300cm.', 350.00, 'en_ligne', POINT(45.7640, 4.8357), 'Lyon', '69000'),
-- Articles Marseille (@u10 - 43.2965, 5.3698)
(@u10, 1, 'Dell XPS 15', 'Processeur i7, RTX 3050, 16Go RAM.', 1300.00, 'en_ligne', POINT(43.2965, 5.3698), 'Marseille', '13000'),
(@u10, 1, 'AirPods Max', 'Argent, etat neuf, jamais portes.', 580.00, 'en_ligne', POINT(43.2965, 5.3698), 'Marseille', '13000'),
(@u10, 2, 'Motocyclette Honda CB500F', '15 000 km, parfait entretien.', 5200.00, 'en_ligne', POINT(43.2965, 5.3698), 'Marseille', '13000'),
(@u10, 5, 'Appareil photo Fujifilm X-T4', 'Noir, avec objectif 18-55mm.', 950.00, 'en_ligne', POINT(43.2965, 5.3698), 'Marseille', '13000'),
(@u10, 4, 'Lit design avec tete de lit', 'Tissu gris, 160x200cm, quasi neuf.', 620.00, 'en_ligne', POINT(43.2965, 5.3698), 'Marseille', '13000');

-- 4. IMAGES
INSERT INTO `article_images` (`article_id`, `url_image`, `est_principale`)
SELECT `id`, 'default.png', 1 FROM `articles`;

-- 5. AVIS (Correction Illegal Mix of Collations)
INSERT INTO `avis` (`article_id`, `expediteur_id`, `destinataire_id`, `note`, `commentaire`, `date_avis`) VALUES
-- Avis sur Jean (@u1)
((SELECT id FROM articles WHERE vendeur_id = @u1 LIMIT 1 OFFSET 0), @u2, @u1, 5, 'Excellent vendeur ! MacBook comme neuf.', NOW() - INTERVAL 15 DAY),
((SELECT id FROM articles WHERE vendeur_id = @u1 LIMIT 1 OFFSET 1), @u3, @u1, 5, 'Très professionnel et réactif.', NOW() - INTERVAL 10 DAY),
((SELECT id FROM articles WHERE vendeur_id = @u1 LIMIT 1 OFFSET 4), @u3, @u1, 5, 'Casque Bose parfait, Jean est top.', NOW() - INTERVAL 4 DAY),
((SELECT id FROM articles WHERE vendeur_id = @u1 LIMIT 1 OFFSET 5), @u4, @u1, 3, 'Ecran OK, mais câble manquant.', NOW() - INTERVAL 12 DAY),

-- Avis sur Marie (@u2)
((SELECT id FROM articles WHERE vendeur_id = @u2 LIMIT 1 OFFSET 0), @u5, @u2, 5, 'La Peugeot est parfaite.', NOW() - INTERVAL 15 DAY),
((SELECT id FROM articles WHERE vendeur_id = @u2 LIMIT 1 OFFSET 1), @u3, @u2, 4, 'VTT en bon état, merci.', NOW() - INTERVAL 7 DAY),

-- Avis sur Lucas (@u3)
((SELECT id FROM articles WHERE vendeur_id = @u3 LIMIT 1 OFFSET 0), @u2, @u3, 5, 'Canapé magnifique, livraison facile.', NOW() - INTERVAL 2 DAY),
((SELECT id FROM articles WHERE vendeur_id = @u3 LIMIT 1 OFFSET 1), @u4, @u3, 2, 'Table avec rayures non mentionnées.', NOW() - INTERVAL 20 DAY),

-- Avis "Profil" (Transactions sans article spécifique)
(NULL, @u1, @u3, 5, 'Acheteur très poli et ponctuel.', NOW() - INTERVAL 5 DAY),
(NULL, @u5, @u4, 4, 'Sophie est une acheteuse réactive.', NOW() - INTERVAL 9 DAY);

-- 6. FAVORIS
INSERT INTO `favoris` (`user_id`, `article_id`) VALUES
(@u1, (SELECT id FROM articles WHERE titre = 'Peugeot 208' COLLATE utf8mb4_general_ci LIMIT 1)),
(@u2, (SELECT id FROM articles WHERE titre = 'MacBook Pro 14' COLLATE utf8mb4_general_ci LIMIT 1)),
(@u3, (SELECT id FROM articles WHERE titre = 'MacBook Pro 14' COLLATE utf8mb4_general_ci LIMIT 1)),
(@u4, (SELECT id FROM articles WHERE titre = 'iPhone 15' COLLATE utf8mb4_general_ci LIMIT 1));

COMMIT;
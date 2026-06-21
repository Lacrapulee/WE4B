-- ==========================================================
-- FICHIER DE SEEDING : seed.sql 
-- Base de données : WE4BDB
-- ==========================================================

SET NAMES utf8mb4;
USE WE4BDB;

-- ==========================================================
-- 1. USERS
-- ==========================================================

INSERT INTO users (email, password, nom, prenom, telephone, is_admin) VALUES
('jean.dupont@gmail.com', 'hash123', 'Dupont', 'Jean', '0611223344', 0),
('marie.curie@utbm.fr', 'hash123', 'Curie', 'Marie', '0655443322', 0),
('lucas.bertrand@orange.fr', 'hash123', 'Bertrand', 'Lucas', '0788990011', 0),
('sophie.fonfec@yahoo.fr', 'hash123', 'Fonfec', 'Sophie', '0600112233', 0),
('admin.boutique@utbm.fr', '$2y$10$J8Hc3w6LRgkWr/ISvw2eyuj.6F2QaGyvA7cOuY5rulmWJSqaDaGnq', 'Admin', 'Boutique', '0384001122', 1),
('thomas.paris@gmail.com', 'hash123', 'Rousseau', 'Thomas', '0612345678', 0),
('isabelle.brest@gmail.com', 'hash123', 'Lefevre', 'Isabelle', '0698765432', 0),
('sebastien.mtp@gmail.com', 'hash123', 'Moreau', 'Sebastien', '0687654321', 0),
('anne.lyon@gmail.com', 'hash123', 'Garneau', 'Anne', '0676543210', 0),
('pierre.marseille@gmail.com', 'hash123', 'Blanc', 'Pierre', '0665432109', 0),
('admin@gmail.com', '$2y$10$J8Hc3w6LRgkWr/ISvw2eyuj.6F2QaGyvA7cOuY5rulmWJSqaDaGnq', 'Admin', 'Admin', '0384031122', 1);



-- ==========================================================
-- 2. CATEGORIES
-- ==========================================================

INSERT INTO categories (id, nom, description) VALUES
(1, 'Informatique', 'PC, Consoles, Accessoires'),
(2, 'Vehicules', 'Voitures, Velos, Trottinettes'),
(3, 'Immobilier', 'Ventes et Locations'),
(4, 'Maison', 'Meubles et Deco'),
(5, 'Loisirs', 'Sport, Musique, Jeux');

-- ==========================================================
-- 3. ARTICLES
-- ==========================================================

INSERT INTO articles (vendeur_id, categorie_id, titre, description, prix, statut, coordonnees, ville_nom, code_postal) VALUES

-- Informatique (Jean = 1)
(1, 1, 'MacBook Pro 14', 'M2, 16Go RAM, comme neuf.', 1800.00, 'en_ligne', POINT(47.64, 6.85), 'Belfort', '90000'),
(1, 1, 'iPhone 15', 'Noir, 128Go, debloque.', 850.00, 'en_ligne', POINT(47.64, 6.85), 'Belfort', '90000'),
(1, 1, 'Clavier Corsair K70', 'Switch Red, RGB.', 110.00, 'en_ligne', POINT(47.64, 6.85), 'Belfort', '90000'),
(1, 1, 'Souris MX Master 3S', 'Ideal productivite.', 75.00, 'en_ligne', POINT(47.64, 6.85), 'Belfort', '90000'),
(1, 1, 'Casque Bose QC45', 'Reduction de bruit active.', 190.00, 'en_ligne', POINT(47.64, 6.85), 'Belfort', '90000'),
(1, 1, 'ecran Dell 27 4K', 'Dalle IPS ultra precise.', 350.00, 'en_ligne', POINT(47.64, 6.85), 'Belfort', '90000'),
(1, 1, 'PC Portable Asus Zenbook 14', 'Intel Core i7, 16Go RAM, SSD 512Go.', 999.00, 'en_ligne', POINT(47.64, 6.85), 'Belfort', '90000'),
(1, 1, 'Moniteur LG 34 Ultrawide', 'Ecran incurve 34 pouces, parfait teletravail.', 310.00, 'en_ligne', POINT(47.64, 6.85), 'Belfort', '90000'),

-- Vehicules (Marie = 2)
(2, 2, 'Peugeot 208', '1.2 PureTech, 50k km.', 11500.00, 'en_ligne', POINT(47.51, 6.79), 'Montbeliard', '25200'),
(2, 2, 'VTT Specialized', 'Tout suspendu, Taille M.', 1200.00, 'en_ligne', POINT(47.51, 6.79), 'Montbeliard', '25200'),
(2, 2, 'Renault Clio 5', 'Essence, 68k km, entretien a jour.', 9800.00, 'en_ligne', POINT(47.51, 6.79), 'Montbeliard', '25200'),
(2, 2, 'Trottinette Xiaomi Pro 2', 'Batterie en bon etat, autonomie confortable.', 260.00, 'en_ligne', POINT(47.51, 6.79), 'Montbeliard', '25200'),

-- Maison (Lucas = 3)
(3, 4, 'Canape d angle convertible', 'Gris fonce, 4 places.', 450.00, 'en_ligne', POINT(47.58, 6.81), 'Sevenans', '90400'),
(3, 4, 'Table a manger Chene', 'Avec 6 chaises assorties.', 320.00, 'en_ligne', POINT(47.58, 6.81), 'Sevenans', '90400'),
(3, 4, 'Lit coffre 160x200', 'Sommiers inclus, tres bon etat.', 290.00, 'en_ligne', POINT(47.58, 6.81), 'Sevenans', '90400'),
(3, 4, 'Bibliotheque bois massif', '5 etageres, finition claire.', 140.00, 'en_ligne', POINT(47.58, 6.81), 'Sevenans', '90400'),

-- Loisirs (Sophie = 4)
(4, 5, 'Guitare Fender Stratocaster', 'Made in Mexico, Sunburst.', 650.00, 'en_ligne', POINT(47.58, 6.79), 'Hericourt', '70400'),
(4, 5, 'Halteres 20kg', 'Lot de 2, fonte.', 40.00, 'en_ligne', POINT(47.58, 6.79), 'Hericourt', '70400'),
(4, 5, 'Velo de route Triban RC120', 'Cadre aluminium, ideal debutant.', 420.00, 'en_ligne', POINT(47.58, 6.79), 'Hericourt', '70400'),
(4, 5, 'Piano numerique Yamaha P45', '88 touches, support pedalier.', 380.00, 'en_ligne', POINT(47.58, 6.79), 'Hericourt', '70400'),

-- Immobilier (Admin = 5)
(5, 3, 'Studio Centre Belfort', 'Proche gare, 25m2.', 55000.00, 'en_ligne', POINT(47.63, 6.86), 'Belfort', '90000'),
(5, 3, 'T3 Montbeliard', 'Vue sur le chateau, parking.', 125000.00, 'en_ligne', POINT(47.51, 6.79), 'Montbeliard', '25200'),
(5, 3, 'T2 meuble centre Montbeliard', 'Residence calme, ideal investissement.', 69000.00, 'en_ligne', POINT(47.51, 6.79), 'Montbeliard', '25200'),
(5, 3, 'Garage securise proche gare', 'Acces facile, portail electrique.', 12000.00, 'en_ligne', POINT(47.63, 6.86), 'Belfort', '90000'),

-- Articles croisés
(1, 2, 'VTT Trek Marlin 7', 'Shimano Deore, tres bon etat.', 780.00, 'en_ligne', POINT(47.64, 6.85), 'Belfort', '90000'),
(2, 1, 'iPad Air 5', 'WiFi, 64Go, etat impeccable.', 490.00, 'en_ligne', POINT(47.51, 6.79), 'Montbeliard', '25200'),
(3, 3, 'Parking couvert centre Belfort', 'Place securisee, acces badge.', 8500.00, 'en_ligne', POINT(47.58, 6.81), 'Sevenans', '90400'),
(4, 1, 'Casque gaming SteelSeries Arctis 7', 'Sans fil, son clair, micro retractable.', 95.00, 'en_ligne', POINT(47.58, 6.79), 'Hericourt', '70400'),
(5, 4, 'Canape convertible scandinave', 'Beige, 3 places, tres bon confort.', 520.00, 'en_ligne', POINT(47.63, 6.86), 'Belfort', '90000'),

-- Paris (6)
(6, 1, 'MacBook Air M1', '13 pouces, excellent etat, batterie 92%.', 950.00, 'en_ligne', POINT(48.8566, 2.3522), 'Paris', '75001'),
(6, 1, 'Google Pixel 7 Pro', '256Go, etat comme neuf.', 650.00, 'en_ligne', POINT(48.8566, 2.3522), 'Paris', '75001'),
(6, 1, 'Webcam Logitech C920', 'Full HD, parfait streaming.', 55.00, 'en_ligne', POINT(48.8566, 2.3522), 'Paris', '75001'),
(6, 2, 'Velo Fixie State Bicycle', 'Cadre noir mat, tres stylise.', 380.00, 'en_ligne', POINT(48.8566, 2.3522), 'Paris', '75001'),
(6, 4, 'Chaise Eames DSW', 'Coque blanche, pied epoxyee.', 95.00, 'en_ligne', POINT(48.8566, 2.3522), 'Paris', '75001'),

-- Brest (7)
(7, 1, 'iMac 27 pouces', 'Intel Core i5, 8Go RAM, Retina 5K.', 1100.00, 'en_ligne', POINT(48.3905, -4.4860), 'Brest', '29200'),
(7, 1, 'Clavier mecanique Ducky', 'Cherry MX Brown, RGB.', 145.00, 'en_ligne', POINT(48.3905, -4.4860), 'Brest', '29200'),
(7, 2, 'Kayak Ocean Kayak', 'Couleur bleue, bon etat.', 590.00, 'en_ligne', POINT(48.3905, -4.4860), 'Brest', '29200'),
(7, 5, 'Planche de surf Catch Surf', 'Longueur 6 pieds.', 280.00, 'en_ligne', POINT(48.3905, -4.4860), 'Brest', '29200'),
(7, 4, 'Lampe industrielle', 'Metal bois, LED.', 65.00, 'en_ligne', POINT(48.3905, -4.4860), 'Brest', '29200'),

-- Montpellier (8)
(8, 1, 'Alienware Monitor 34', '3440x1440 100Hz.', 580.00, 'en_ligne', POINT(43.6108, 3.8767), 'Montpellier', '34000'),
(8, 1, 'RTX 3070 Ti', 'Peu utilisee.', 820.00, 'en_ligne', POINT(43.6108, 3.8767), 'Montpellier', '34000'),
(8, 5, 'Drone DJI Air 3', 'Quasi neuf.', 1350.00, 'en_ligne', POINT(43.6108, 3.8767), 'Montpellier', '34000'),
(8, 2, 'Skateboard Element', 'ABEC-7.', 125.00, 'en_ligne', POINT(43.6108, 3.8767), 'Montpellier', '34000'),
(8, 4, 'Miroir design', 'Cadre dore 80x60.', 110.00, 'en_ligne', POINT(43.6108, 3.8767), 'Montpellier', '34000'),

-- Lyon (9)
(9, 1, 'iPhone 14 Pro Max', '256Go boite complete.', 1050.00, 'en_ligne', POINT(45.7640, 4.8357), 'Lyon', '69000'),
(9, 1, 'Sony WH-1000XM4', 'Reduction bruit.', 220.00, 'en_ligne', POINT(45.7640, 4.8357), 'Lyon', '69000'),
(9, 2, 'Velo Gravel Canyon', 'Taille M.', 1200.00, 'en_ligne', POINT(45.7640, 4.8357), 'Lyon', '69000'),
(9, 5, 'Roland TR-808', 'Machine a rythme.', 480.00, 'en_ligne', POINT(45.7640, 4.8357), 'Lyon', '69000'),
(9, 4, 'Tapis kilim', '200x300cm.', 350.00, 'en_ligne', POINT(45.7640, 4.8357), 'Lyon', '69000'),

-- Marseille (10)
(10, 1, 'Dell XPS 15', 'i7 RTX 3050.', 1300.00, 'en_ligne', POINT(43.2965, 5.3698), 'Marseille', '13000'),
(10, 1, 'AirPods Max', 'Argent neuf.', 580.00, 'en_ligne', POINT(43.2965, 5.3698), 'Marseille', '13000'),
(10, 2, 'Honda CB500F', '15 000 km.', 5200.00, 'en_ligne', POINT(43.2965, 5.3698), 'Marseille', '13000'),
(10, 5, 'Fujifilm X-T4', 'Avec objectif.', 950.00, 'en_ligne', POINT(43.2965, 5.3698), 'Marseille', '13000'),
(10, 4, 'Lit design 160x200', 'Quasi neuf.', 620.00, 'en_ligne', POINT(43.2965, 5.3698), 'Marseille', '13000');

-- ==========================================================
-- 4. IMAGES
-- ==========================================================

-- Removed default.png inserts as images are now stored in MongoDB

-- ==========================================================
-- 5. AVIS
-- ==========================================================

INSERT INTO avis (article_id, expediteur_id, destinataire_id, note, commentaire, date_avis) VALUES
(1, 2, 1, 5, 'Excellent vendeur ! MacBook comme neuf.', NOW() - INTERVAL 15 DAY),
(2, 3, 1, 5, 'Très professionnel.', NOW() - INTERVAL 10 DAY),
(3, 4, 2, 5, 'Voiture parfaite.', NOW() - INTERVAL 8 DAY),
(4, 1, 3, 4, 'Bon produit.', NOW() - INTERVAL 6 DAY),

(NULL, 1, 3, 5, 'Acheteur très sérieux.', NOW() - INTERVAL 5 DAY),
(NULL, 5, 4, 4, 'Très bonne communication.', NOW() - INTERVAL 9 DAY);

-- ==========================================================
-- 6. FAVORIS
-- ==========================================================

INSERT INTO favoris (user_id, article_id) VALUES
(1, (SELECT id FROM articles WHERE titre = 'Peugeot 208' LIMIT 1)),
(2, (SELECT id FROM articles WHERE titre = 'MacBook Pro 14' LIMIT 1)),
(3, (SELECT id FROM articles WHERE titre = 'MacBook Pro 14' LIMIT 1)),
(4, (SELECT id FROM articles WHERE titre = 'iPhone 15' LIMIT 1));

-- ==========================================================
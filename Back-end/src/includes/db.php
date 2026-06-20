<?php
/**
 * Configuration de la connexion à la base de données
 */
$host = 'db';           
$db   = 'WE4BDB';       
$user = 'root';         
$pass = 'rootpassword'; 
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";


try {
     $pdo = new PDO($dsn, $user, $pass);
} catch (\PDOException $e) {
     die("Erreur de connexion à la base de données : " . $e->getMessage());
}
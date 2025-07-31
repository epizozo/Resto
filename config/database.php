<?php
// Paramètres de connexion
$host = "localhost";
$dbname = "resto_db";
$username = "root";
$password = "";

try {
    // Création de la connexion PDO
    $conn = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
    return $conn;
} catch(PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Pour utiliser la connexion dans d'autres fichiers, il suffit de faire un require_once de ce fichier
// La variable $conn sera disponible
<?php
require_once 'config/database.php';
session_start();

$isLoggedIn = false;

if (isset($_SESSION['user_id'])) {
    try {
        // Vérifier si l'utilisateur existe et est actif
        $stmt = $conn->prepare("
            SELECT u.statut 
            FROM utilisateurs u 
            INNER JOIN sessions_utilisateur s ON u.id_utilisateur = s.id_utilisateur 
            WHERE u.id_utilisateur = ? 
            AND s.date_expiration > NOW() 
            AND u.statut = 'actif'
        ");
        $stmt->execute([$_SESSION['user_id']]);
        
        if ($stmt->fetchColumn()) {
            $isLoggedIn = true;
        }
    } catch(PDOException $e) {
        $isLoggedIn = false;
    }
}

header('Content-Type: application/json');
echo json_encode(['isLoggedIn' => $isLoggedIn]);
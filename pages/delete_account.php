<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit;
}

try {
    $stmt = $conn->prepare("UPDATE utilisateurs SET statut = 'inactif' WHERE id_utilisateur = ?");
    $stmt->execute([$_SESSION['user_id']]);
    
    session_destroy();
    http_response_code(200);
} catch(PDOException $e) {
    error_log("Erreur de suppression : " . $e->getMessage());
    http_response_code(500);
}
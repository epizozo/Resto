<?php
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['error' => 'ID non fourni']);
    exit;
}

try {
    $stmt = $conn->prepare("
        SELECT id_plat, nom, description, prix, categorie, image, disponibilite
        FROM plats 
        WHERE id_plat = ?
    ");
    $stmt->execute([$_GET['id']]);
    $plat = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($plat) {
        echo json_encode($plat);
    } else {
        echo json_encode(['error' => 'Plat non trouvé']);
    }
} catch(PDOException $e) {
    echo json_encode(['error' => 'Erreur de base de données']);
}
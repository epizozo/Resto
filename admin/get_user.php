<?php
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['error' => 'ID utilisateur non fourni']);
    exit;
}

try {
    $stmt = $conn->prepare("
        SELECT id_utilisateur, nom, prenom, email, statut 
        FROM utilisateurs 
        WHERE id_utilisateur = ?
    ");
    
    $stmt->execute([$_GET['id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        // Formatage des données pour le front-end
        $response = [
            'id_utilisateur' => $user['id_utilisateur'],
            'nom' => $user['nom'] . ' ' . $user['prenom'], // Concaténation nom et prénom
            'email' => $user['email'],
            'statut' => $user['statut']
        ];
        echo json_encode($response);
    } else {
        echo json_encode(['error' => 'Utilisateur non trouvé']);
    }
} catch(PDOException $e) {
    error_log("Erreur dans get_user.php : " . $e->getMessage());
    echo json_encode(['error' => 'Erreur lors de la récupération des données']);
}
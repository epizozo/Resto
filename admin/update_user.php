<?php
require_once '../config/database.php';

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['id'])) {
    try {
        $stmt = $conn->prepare("
            UPDATE utilisateurs 
            SET nom = ?, email = ?, statut = ?
            WHERE id_utilisateur = ?
        ");
        $success = $stmt->execute([$data['nom'], $data['email'], $data['statut'], $data['id']]);
        
        echo json_encode(['success' => $success]);
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
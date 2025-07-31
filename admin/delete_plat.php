<?php
require_once '../config/database.php';

header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['id'])) {
        throw new Exception('ID non fourni');
    }

    // Récupérer l'image avant la suppression
    $stmt = $conn->prepare("SELECT image FROM plats WHERE id_plat = ?");
    $stmt->execute([$data['id']]);
    $plat = $stmt->fetch();

    // Supprimer le plat
    $stmt = $conn->prepare("DELETE FROM plats WHERE id_plat = ?");
    $success = $stmt->execute([$data['id']]);

    // Si la suppression a réussi et qu'il y avait une image
    if ($success && $plat && $plat['image']) {
        $imagePath = '../uploads/plats/' . $plat['image'];
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }

    echo json_encode(['success' => $success]);

} catch(Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
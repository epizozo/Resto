<?php
require_once '../config/database.php';

header('Content-Type: application/json');

try {
    $id = $_POST['id'];
    $nom = $_POST['nom'];
    $categorie = $_POST['categorie'];
    $prix = $_POST['prix'];
    $description = $_POST['description'];
    $disponibilite = $_POST['disponibilite'];

    // Si une nouvelle image est fournie
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (!in_array($ext, $allowed)) {
            throw new Exception('Format d\'image non autorisé');
        }

        $newName = uniqid() . '.' . $ext;
        $uploadPath = '../uploads/plats/' . $newName;

        if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
            throw new Exception('Erreur lors du téléchargement de l\'image');
        }

        // Mettre à jour avec la nouvelle image
        $stmt = $conn->prepare("
            UPDATE plats 
            SET nom = ?, description = ?, prix = ?, categorie = ?, 
                image = ?, disponibilite = ?
            WHERE id_plat = ?
        ");
        $stmt->execute([$nom, $description, $prix, $categorie, $newName, $disponibilite, $id]);
    } else {
        // Mise à jour sans nouvelle image
        $stmt = $conn->prepare("
            UPDATE plats 
            SET nom = ?, description = ?, prix = ?, categorie = ?, 
                disponibilite = ?
            WHERE id_plat = ?
        ");
        $stmt->execute([$nom, $description, $prix, $categorie, $disponibilite, $id]);
    }

    echo json_encode(['success' => true]);

} catch(Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
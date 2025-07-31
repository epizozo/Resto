<?php
require_once '../config/database.php';

header('Content-Type: application/json');

try {
    // Vérifier si une image a été uploadée
    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== 0) {
        throw new Exception('Image requise');
    }

    // Validation de l'image
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    $filename = $_FILES['image']['name'];
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    
    if (!in_array($ext, $allowed)) {
        throw new Exception('Format d\'image non autorisé');
    }

    // Générer un nom unique pour l'image
    $newName = uniqid() . '.' . $ext;
    $uploadPath = '../uploads/plats/' . $newName;

    // Déplacer l'image
    if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
        throw new Exception('Erreur lors du téléchargement de l\'image');
    }

    // Récupérer les données du plat
    $nom = filter_input(INPUT_POST, 'nom', FILTER_SANITIZE_STRING);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
    $prix = filter_input(INPUT_POST, 'prix', FILTER_VALIDATE_FLOAT);
    $categorie = filter_input(INPUT_POST, 'categorie', FILTER_SANITIZE_STRING);
    $disponibilite = filter_input(INPUT_POST, 'disponibilite', FILTER_SANITIZE_STRING) ?? 'disponible';

    // Validation des données
    if (!$nom || !$prix || !$categorie) {
        throw new Exception('Données invalides');
    }

    // Insérer dans la base de données
    $stmt = $conn->prepare("
        INSERT INTO plats (nom, description, prix, categorie, image, disponibilite)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $nom,
        $description,
        $prix,
        $categorie,
        $newName,
        $disponibilite
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Plat ajouté avec succès'
    ]);

} catch (Exception $e) {
    // En cas d'erreur, supprimer l'image si elle a été uploadée
    if (isset($newName) && file_exists($uploadPath)) {
        unlink($uploadPath);
    }
    
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
<?php
require_once '../config/database.php';

header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);

    // Validation des données
    if (empty($data['email']) || empty($data['mot_de_passe']) || empty($data['nom'])) {
        throw new Exception('Tous les champs obligatoires doivent être remplis');
    }

    // Vérification si l'email existe déjà
    $stmt = $conn->prepare("SELECT COUNT(*) FROM utilisateurs WHERE email = ?");
    $stmt->execute([$data['email']]);
    if ($stmt->fetchColumn() > 0) {
        throw new Exception('Cet email est déjà utilisé');
    }

    // Hash du mot de passe
    $mot_de_passe_hash = password_hash($data['mot_de_passe'], PASSWORD_DEFAULT);

    // Insertion de l'utilisateur
    $stmt = $conn->prepare("
        INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe_hash, statut)
        VALUES (?, ?, ?, ?, ?)
    ");

    $success = $stmt->execute([
        $data['nom'],
        $data['prenom'],
        $data['email'],
        $mot_de_passe_hash,
        $data['statut']
    ]);

    echo json_encode(['success' => $success]);

} catch(Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
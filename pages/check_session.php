<?php
session_start();
header('Content-Type: application/json');

// Vérifier si l'utilisateur est connecté
$response = [
    'isLoggedIn' => isset($_SESSION['user_id']),
    'userData' => isset($_SESSION['user_id']) ? [
        'id' => $_SESSION['user_id'],
        'nom' => $_SESSION['nom'] ?? '',
        'prenom' => $_SESSION['prenom'] ?? '',
        'email' => $_SESSION['email'] ?? ''
    ] : null
];

echo json_encode($response);
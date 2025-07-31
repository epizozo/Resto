<?php
require_once '../config/database.php';

try {
    // Récupération des plats
    $stmt = $conn->prepare("
        SELECT id_plat, nom, description, prix, categorie, image, date_creation 
        FROM plats 
        WHERE statut = 'actif' 
        AND disponibilite = 'disponible'
        ORDER BY categorie, nom
    ");
    $stmt->execute();
    $plats = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Menu du Restaurant</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .card-img-top {
            height: 200px;
            object-fit: cover;
        }
        .card {
            transition: transform 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .category-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 5px 15px;
            border-radius: 20px;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container py-5">
        <h1 class="text-center mb-4">🍽️ Menu du Restaurant</h1>
        <div class="row row-cols-1 row-cols-md-3 g-4">
            <?php foreach ($plats as $plat): ?>
                <div class="col">
                    <div class="card h-100 shadow-sm position-relative">
                        <?php if ($plat['image']): ?>
                            <img src="../uploads/plats/<?= htmlspecialchars($plat['image']) ?>" 
                                 class="card-img-top" 
                                 alt="<?= htmlspecialchars($plat['nom']) ?>"
                                 onerror="this.src='../assets/img/default-dish.jpg'">
                        <?php else: ?>
                            <img src="../assets/img/default-dish.jpg" 
                                 class="card-img-top" 
                                 alt="Image par défaut">
                        <?php endif; ?>
                        
                        <span class="category-badge badge bg-<?php 
                            echo match($plat['categorie']) {
                                'entree' => 'success',
                                'main' => 'primary',
                                'dessert' => 'warning',
                                'drink' => 'info',
                                default => 'secondary'
                            };
                        ?>">
                            <?php 
                            echo match($plat['categorie']) {
                                'entree' => 'Entrée',
                                'main' => 'Plat principal',
                                'dessert' => 'Dessert',
                                'drink' => 'Boisson',
                                default => 'Autre'
                            };
                            ?>
                        </span>
                        
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($plat['nom']) ?></h5>
                            <p class="card-text"><?= nl2br(htmlspecialchars($plat['description'])) ?></p>
                            <p class="text-primary fw-bold fs-4"><?= number_format($plat['prix'], 2, ',', ' ') ?> €</p>
                        </div>
                        <div class="card-footer text-muted">
                            Ajouté le <?= date("d/m/Y", strtotime($plat['date_creation'])) ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
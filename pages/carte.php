<?php
require_once '../config/database.php';

try {
    $stmt = $conn->prepare("
        SELECT id_plat, nom, description, prix, categorie, image 
        FROM plats 
        WHERE statut = 'actif' 
        AND disponibilite = 'disponible'
        ORDER BY categorie, nom
    ");
    $stmt->execute();
    $plats = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Organiser les plats par catégorie
    $plats_par_categorie = [];
    foreach ($plats as $plat) {
        $plats_par_categorie[$plat['categorie']][] = $plat;
    }
} catch(PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notre Carte - Resto</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #ff6b6b;
            --secondary-color: #4ecdc4;
        }

        body {
            background-color: #f8f9fa;
            font-family: 'Roboto', sans-serif;
        }

        .menu-section {
            padding: 80px 0;
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)),
                        url('../assets/img/restaurant-bg.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            color: white;
        }

        .category-title {
            margin: 40px 0;
            text-align: center;
            font-size: 2.5rem;
            font-weight: 700;
            color: white;
            text-transform: uppercase;
            letter-spacing: 2px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }

        .dish-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            overflow: hidden;
            margin-bottom: 30px;
            transition: all 0.3s ease;
            height: 100%;
        }

        .dish-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.3);
        }

        .dish-image {
            height: 200px;
            overflow: hidden;
        }

        .dish-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .dish-card:hover .dish-image img {
            transform: scale(1.1);
        }

        .dish-info {
            padding: 20px;
        }

        .dish-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 10px;
        }

        .dish-description {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 15px;
            min-height: 40px;
        }

        .dish-price {
            font-size: 1.5rem;
            color: var(--primary-color);
            font-weight: 700;
        }

        .category-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
    </style>
</head>
<body>

<section class="menu-section">
    <div class="container">
        <h1 class="text-center mb-5">Notre Carte</h1>

        <?php
        $categories = [
            'entree' => ['titre' => 'Entrées', 'icone' => 'fa-leaf'],
            'main' => ['titre' => 'Plats Principaux', 'icone' => 'fa-utensils'],
            'dessert' => ['titre' => 'Desserts', 'icone' => 'fa-ice-cream'],
            'drink' => ['titre' => 'Boissons', 'icone' => 'fa-glass-martini']
        ];

        foreach ($categories as $categorie => $info):
            if (isset($plats_par_categorie[$categorie])):
        ?>
            <div class="category-section mb-5">
                <h2 class="category-title">
                    <i class="fas <?php echo $info['icone']; ?> me-3"></i>
                    <?php echo $info['titre']; ?>
                </h2>
                <div class="row">
                    <?php foreach ($plats_par_categorie[$categorie] as $plat): ?>
                        <div class="col-md-4 mb-4">
                            <div class="dish-card position-relative">
                                <div class="dish-image">
                                    <img src="<?php echo !empty($plat['image']) ? 
                                        '../uploads/plats/' . htmlspecialchars($plat['image']) : 
                                        '../assets/img/default-dish.jpg'; ?>" 
                                         alt="<?php echo htmlspecialchars($plat['nom']); ?>">
                                </div>
                                <div class="dish-info">
                                    <h3 class="dish-title"><?php echo htmlspecialchars($plat['nom']); ?></h3>
                                    <p class="dish-description">
                                        <?php echo htmlspecialchars($plat['description'] ?? 'Aucune description disponible'); ?>
                                    </p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="dish-price"><?php echo number_format($plat['prix'], 2, ',', ' '); ?> €</span>
                                        <button class="btn btn-outline-primary btn-sm commander-btn" 
                                                data-plat-id="<?php echo $plat['id_plat']; ?>">
                                            Commander
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php 
            endif;
        endforeach; 
        ?>
    </div>
</section>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.querySelectorAll('.commander-btn').forEach(button => {
    button.addEventListener('click', function() {
        const platId = this.dataset.platId;
        Swal.fire({
            title: 'Commander ce plat ?',
            text: "Ajoutez ce plat à votre commande",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#ff6b6b',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Oui, commander',
            cancelButtonText: 'Annuler'
        }).then((result) => {
            if (result.isConfirmed) {
                // Ici, ajoutez la logique pour gérer la commande
                Swal.fire(
                    'Ajouté !',
                    'Le plat a été ajouté à votre commande.',
                    'success'
                );
            }
        });
    });
});
</script>

</body>
</html>
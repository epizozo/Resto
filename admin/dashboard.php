<?php
require_once '../config/database.php';

// Configuration de la pagination
$par_page = 5; // Nombre d'utilisateurs par page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$debut = ($page - 1) * $par_page;

// Récupération du nombre total d'utilisateurs
try {
    $stmt = $conn->query("SELECT COUNT(*) FROM utilisateurs");
    $total_utilisateurs = $stmt->fetchColumn();
    $total_pages = ceil($total_utilisateurs / $par_page);
} catch(PDOException $e) {
    error_log("Erreur lors du comptage des utilisateurs : " . $e->getMessage());
    $total_utilisateurs = 0;
    $total_pages = 0;
}

// Récupération des utilisateurs avec pagination
try {
    $stmt = $conn->prepare("
        SELECT id_utilisateur, nom, prenom, email, statut
        FROM utilisateurs 
        ORDER BY date_creation DESC
        LIMIT :debut, :par_page
    ");
    $stmt->bindValue(':debut', $debut, PDO::PARAM_INT);
    $stmt->bindValue(':par_page', $par_page, PDO::PARAM_INT);
    $stmt->execute();
    $utilisateurs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    error_log("Erreur lors de la récupération des utilisateurs : " . $e->getMessage());
    $utilisateurs = [];
}

// Configuration de la pagination des plats
$plats_par_page = 5;
$page_plats = isset($_GET['page_plats']) ? (int)$_GET['page_plats'] : 1;
$debut_plats = ($page_plats - 1) * $plats_par_page;

// Récupération des plats avec pagination
try {
    // Compte total des plats
    $stmt = $conn->query("SELECT COUNT(*) FROM plats");
    $total_plats = $stmt->fetchColumn();
    $total_pages_plats = ceil($total_plats / $plats_par_page);

    // Récupération des plats
    $stmt = $conn->prepare("
        SELECT id_plat, nom, categorie, prix, image, statut
        FROM plats 
        ORDER BY date_creation DESC
        LIMIT :debut, :par_page
    ");
    $stmt->bindValue(':debut', $debut_plats, PDO::PARAM_INT);
    $stmt->bindValue(':par_page', $plats_par_page, PDO::PARAM_INT);
    $stmt->execute();
    $plats = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    error_log("Erreur lors de la récupération des plats : " . $e->getMessage());
    $plats = [];
}

// Configuration de la pagination des messages
$messages_par_page = 5;
$page_messages = isset($_GET['page_messages']) ? (int)$_GET['page_messages'] : 1;
$debut_messages = ($page_messages - 1) * $messages_par_page;

// Récupération des messages avec pagination
try {
    // Compte total des messages
    $stmt = $conn->query("SELECT COUNT(*) FROM messages_contact");
    $total_messages = $stmt->fetchColumn();
    $total_pages_messages = ceil($total_messages / $messages_par_page);

    // Récupération des messages
    $stmt = $conn->prepare("
        SELECT id_message, nom_complet, sujet, message, 
               DATE_FORMAT(date_envoi, '%d/%m/%Y %H:%i') as date_formattee,
               statut
        FROM messages_contact 
        ORDER BY date_envoi DESC
        LIMIT :debut, :par_page
    ");
    $stmt->bindValue(':debut', $debut_messages, PDO::PARAM_INT);
    $stmt->bindValue(':par_page', $messages_par_page, PDO::PARAM_INT);
    $stmt->execute();
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    error_log("Erreur lors de la récupération des messages : " . $e->getMessage());
    $messages = [];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Resto</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-dark@4/dark.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --primary-color: #ff6b6b;
            --secondary-color: #4ecdc4;
            --sidebar-width: 250px;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        /* Sidebar */
        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            background: #2c3e50;
            color: white;
            transition: all 0.3s;
            z-index: 1000;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }

        .sidebar.collapsed {
            transform: translateX(-100%);
        }

        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-header h3 {
            color: white;
            margin: 0;
            display: flex;
            align-items: center;
        }

        .sidebar-header h3 i {
            margin-right: 10px;
            color: var(--primary-color);
        }

        .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 20px;
            border-left: 3px solid transparent;
            display: flex;
            align-items: center;
        }

        .nav-link:hover, .nav-link.active {
            background: rgba(255,255,255,0.1);
            color: white;
            border-left: 3px solid var(--primary-color);
        }

        .nav-link i {
            margin-right: 10px;
            width: 20px;
        }

        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            transition: all 0.3s;
        }

        .main-content.expanded {
            margin-left: 0;
        }

        /* Header */
        .header {
            height: 60px;
            background: white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .toggle-sidebar {
            background: none;
            border: none;
            font-size: 1.2rem;
            color: #555;
            cursor: pointer;
        }

        .user-menu {
            display: flex;
            align-items: center;
            cursor: pointer;
        }

        .user-img {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            margin-right: 10px;
        }

        /* Content */
        .content {
            padding: 20px;
        }

        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }

        .card-header {
            background: white;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-header h5 {
            font-weight: 600;
            margin: 0;
        }

        .btn-primary {
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            border: none;
            padding: 8px 15px;
            border-radius: 6px;
            color: white;
        }

        /* Table */
        .table th {
            border-top: none;
            font-weight: 600;
        }

        .table td {
            vertical-align: middle;
        }

        /* Formulaire */
        #dish-form-container {
            transition: all 0.3s ease;
        }
        
        .form-control, .form-select {
            border-radius: 8px;
            padding: 10px 15px;
            margin-bottom: 15px;
            border: 1px solid #ced4da;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(255, 107, 107, 0.25);
        }
        
        .img-thumbnail {
            border-radius: 8px;
            border: 1px solid #ddd;
            max-height: 150px;
            max-width: 100%;
        }

        .image-preview {
            display: none;
            text-align: center;
            margin-top: 10px;
        }

        .dish-image-cell {
            width: 80px;
        }

        .file-input-label {
            display: block;
            margin-bottom: 5px;
        }

        .file-input-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
            width: 100%;
        }

        .file-input-wrapper input[type=file] {
            font-size: 100px;
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
        }

        .file-input-custom {
            border: 1px dashed #ced4da;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }

        .file-input-custom:hover {
            border-color: var(--primary-color);
            background-color: rgba(255, 107, 107, 0.05);
        }

        .file-input-text {
            color: #6c757d;
            font-size: 0.9rem;
        }

        .badge {
            padding: 0.5em 0.8em;
            border-radius: 30px;
            font-weight: 500;
            font-size: 0.75em;
        }

        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }

        .btn-outline-primary:hover {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-outline-danger:hover {
            background-color: #dc3545;
            border-color: #dc3545;
        }

        /* Pagination */
        .pagination {
            margin: 0;
        }

        .page-link {
            color: var(--primary-color);
            border: 1px solid rgba(0,0,0,.1);
            padding: 0.5rem 0.75rem;
            margin: 0 2px;
            min-width: 38px;
            text-align: center;
            transition: all 0.3s ease;
        }

        .page-link:hover {
            color: white;
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .page-item.active .page-link {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .page-item.disabled .page-link {
            color: #6c757d;
            pointer-events: none;
            background-color: #fff;
            border-color: rgba(0,0,0,.1);
        }

        .pagination .fas {
            font-size: 0.8rem;
        }

        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
        }

        /* Styles pour les messages */
        .message-row.non_lu {
            background-color: rgba(255, 243, 205, 0.1);
        }

        .message-count {
            margin-left: 5px;
        }

        .badge {
            padding: 0.5em 0.8em;
            font-weight: 500;
        }

        .text-truncate {
            cursor: pointer;
        }

        .btn-group .btn {
            margin-right: 5px;
        }

        .btn-group .btn:last-child {
            margin-right: 0;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h3><i class="fas fa-utensils"></i> Admin Resto</h3>
        </div>
        <div class="sidebar-menu">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link active" href="#">
                        <i class="fas fa-utensils"></i> Gérer les plats
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <i class="fas fa-users"></i> Gérer les utilisateurs
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#" id="messages-section">
                        <i class="fas fa-envelope"></i> Messages
                        <span class="badge bg-danger message-count"></span>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content" id="main-content">
        <!-- Header -->
        <header class="header">
            <button class="toggle-sidebar" id="toggle-sidebar">
                <i class="fas fa-bars"></i>
            </button>
            <div class="user-menu">
                <img src="../assets/img/moncompte.png" alt="Admin" class="user-img">
                <span>Admin</span>
            </div>
        </header>

        <!-- Content -->
        <div class="content">
            <!-- Conteneur du formulaire (masqué par défaut) -->
            <div id="dish-form-container" style="display: none;"></div>

            <!-- Section Plats -->
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-utensils"></i> Liste des plats</h5>
                    <button class="btn btn-primary" id="add-dish-btn">
                        <i class="fas fa-plus"></i> Ajouter un plat
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th class="dish-image-cell">Image</th>
                                    <th>Nom</th>
                                    <th>Catégorie</th>
                                    <th>Prix</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($plats as $plat): ?>
                                    <tr data-plat-id="<?php echo $plat['id_plat']; ?>">
                                        <td>
                                            <img src="../uploads/plats/<?php echo htmlspecialchars($plat['image']); ?>" 
                                                 alt="<?php echo htmlspecialchars($plat['nom']); ?>"
                                                 class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover;">
                                        </td>
                                        <td><?php echo htmlspecialchars($plat['nom']); ?></td>
                                        <td>
                                            <?php 
                                                $categories = [
                                                    'entree' => 'Entrée',
                                                    'main' => 'Plat principal',
                                                    'dessert' => 'Dessert',
                                                    'drink' => 'Boisson'
                                                ];
                                                echo $categories[$plat['categorie']] ?? $plat['categorie'];
                                            ?>
                                        </td>
                                        <td><?php echo number_format($plat['prix'], 2, ',', ' '); ?> €</td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary me-1" onclick="modifierPlat(<?php echo $plat['id_plat']; ?>)">
                                                <i class="fas fa-edit"></i> Modifier
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" onclick="supprimerPlat(<?php echo $plat['id_plat']; ?>)">
                                                <i class="fas fa-trash"></i> Supprimer
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                        <!-- Pagination pour les plats -->
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="text-muted">
                                Affichage de <?php echo min($debut_plats + 1, $total_plats); ?> à 
                                <?php echo min($debut_plats + $plats_par_page, $total_plats); ?> sur 
                                <?php echo $total_plats; ?> plats
                            </div>
                            <?php if ($total_pages_plats > 1): ?>
                                <nav aria-label="Navigation des pages">
                                    <ul class="pagination mb-0">
                                        <!-- Bouton Précédent -->
                                        <li class="page-item <?php echo ($page_plats <= 1) ? 'disabled' : ''; ?>">
                                            <a class="page-link" href="?page_plats=<?php echo $page_plats - 1; ?>" 
                                               <?php if($page_plats <= 1) echo 'tabindex="-1" aria-disabled="true"'; ?>>
                                                <i class="fas fa-chevron-left"></i>
                                            </a>
                                        </li>
                                        
                                        <?php
                                        // Affichage des numéros de page
                                        for($i = max(1, $page_plats - 2); $i <= min($total_pages_plats, $page_plats + 2); $i++): ?>
                                            <li class="page-item <?php echo ($page_plats == $i) ? 'active' : ''; ?>">
                                                <a class="page-link" href="?page_plats=<?php echo $i; ?>">
                                                    <?php echo $i; ?>
                                                </a>
                                            </li>
                                        <?php endfor; ?>

                                        <!-- Bouton Suivant -->
                                        <li class="page-item <?php echo ($page_plats >= $total_pages_plats) ? 'disabled' : ''; ?>">
                                            <a class="page-link" href="?page_plats=<?php echo $page_plats + 1; ?>"
                                               <?php if($page_plats >= $total_pages_plats) echo 'tabindex="-1" aria-disabled="true"'; ?>>
                                                <i class="fas fa-chevron-right"></i>
                                            </a>
                                        </li>
                                    </ul>
                                </nav>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section Messages -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5><i class="fas fa-envelope"></i> Liste des messages</h5>
                    <div class="btn-group">
                        <button class="btn btn-outline-primary" onclick="filterMessages('tous')">Tous</button>
                        <button class="btn btn-outline-warning" onclick="filterMessages('non_lu')">Non lus</button>
                        <button class="btn btn-outline-success" onclick="filterMessages('repondu')">Répondus</button>
                        <button class="btn btn-outline-secondary" onclick="filterMessages('archive')">Archivés</button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Nom complet</th>
                                    <th>Sujet</th>
                                    <th>Message</th>
                                    <th>Date d'envoi</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($messages as $message): ?>
                                    <tr class="message-row <?php echo $message['statut']; ?>">
                                        <td><?php echo htmlspecialchars($message['nom_complet']); ?></td>
                                        <td><?php echo htmlspecialchars($message['sujet']); ?></td>
                                        <td>
                                            <div class="text-truncate" style="max-width: 200px;" title="<?php echo htmlspecialchars($message['message']); ?>">
                                                <?php echo htmlspecialchars($message['message']); ?>
                                            </div>
                                        </td>
                                        <td><?php echo $message['date_formattee']; ?></td>
                                        <td>
                                            <span class="badge <?php 
                                                echo match($message['statut']) {
                                                    'non_lu' => 'bg-warning',
                                                    'lu' => 'bg-info',
                                                    'repondu' => 'bg-success',
                                                    'archivé' => 'bg-secondary',
                                                    default => 'bg-primary'
                                                };
                                            ?>">
                                                <?php echo ucfirst($message['statut']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-info me-1" onclick="voirMessage(<?php echo $message['id_message']; ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-success me-1" onclick="repondreMessage(<?php echo $message['id_message']; ?>)">
                                                <i class="fas fa-reply"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-secondary" onclick="archiverMessage(<?php echo $message['id_message']; ?>)">
                                                <i class="fas fa-archive"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                        <!-- Pagination pour les messages -->
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="text-muted">
                                Affichage de <?php echo min($debut_messages + 1, $total_messages); ?> à 
                                <?php echo min($debut_messages + $messages_par_page, $total_messages); ?> sur 
                                <?php echo $total_messages; ?> messages
                            </div>
                            <?php if ($total_pages_messages > 1): ?>
                                <nav aria-label="Navigation des pages">
                                    <ul class="pagination mb-0">
                                        <li class="page-item <?php echo ($page_messages <= 1) ? 'disabled' : ''; ?>">
                                            <a class="page-link" href="?page_messages=<?php echo $page_messages - 1; ?>">
                                                <i class="fas fa-chevron-left"></i>
                                            </a>
                                        </li>
                                        
                                        <?php for($i = max(1, $page_messages - 2); $i <= min($total_pages_messages, $page_messages + 2); $i++): ?>
                                            <li class="page-item <?php echo ($page_messages == $i) ? 'active' : ''; ?>">
                                                <a class="page-link" href="?page_messages=<?php echo $i; ?>">
                                                    <?php echo $i; ?>
                                                </a>
                                            </li>
                                        <?php endfor; ?>

                                        <li class="page-item <?php echo ($page_messages >= $total_pages_messages) ? 'disabled' : ''; ?>">
                                            <a class="page-link" href="?page_messages=<?php echo $page_messages + 1; ?>">
                                                <i class="fas fa-chevron-right"></i>
                                            </a>
                                        </li>
                                    </ul>
                                </nav>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section Utilisateurs -->
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-users"></i> Liste des utilisateurs</h5>
                    <button class="btn btn-primary" id="add-user-btn">
                        <i class="fas fa-plus"></i> Ajouter un utilisateur
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Nom complet</th>
                                    <th>Email</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($utilisateurs as $user): ?>
                                    <tr data-user-id="<?php echo $user['id_utilisateur']; ?>">
                                        <td><?php echo htmlspecialchars($user['nom'] . ' ' . $user['prenom']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td>
                                            <span class="badge <?php echo $user['statut'] === 'actif' ? 'bg-success' : 'bg-danger'; ?>">
                                                <?php echo ucfirst(htmlspecialchars($user['statut'])); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary me-1" onclick="modifierUtilisateur(<?php echo $user['id_utilisateur']; ?>)">
                                                <i class="fas fa-edit"></i> Modifier
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" onclick="supprimerUtilisateur(<?php echo $user['id_utilisateur']; ?>)">
                                                <i class="fas fa-trash"></i> Supprimer
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="text-muted">
                                Affichage de <?php echo min($debut + 1, $total_utilisateurs); ?> à 
                                <?php echo min($debut + $par_page, $total_utilisateurs); ?> sur 
                                <?php echo $total_utilisateurs; ?> utilisateurs
                            </div>
                            <?php if ($total_pages > 1): ?>
                                <nav aria-label="Navigation des pages">
                                    <ul class="pagination mb-0">
                                        <!-- Bouton Précédent -->
                                        <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $page - 1; ?>" 
                                               <?php if($page <= 1) echo 'tabindex="-1" aria-disabled="true"'; ?>>
                                                <i class="fas fa-chevron-left"></i>
                                            </a>
                                        </li>
                                        
                                        <?php
                                        // Affichage des numéros de page
                                        for($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                            <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                                                <a class="page-link" href="?page=<?php echo $i; ?>">
                                                    <?php echo $i; ?>
                                                </a>
                                            </li>
                                        <?php endfor; ?>

                                        <!-- Bouton Suivant -->
                                        <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $page + 1; ?>"
                                               <?php if($page >= $total_pages) echo 'tabindex="-1" aria-disabled="true"'; ?>>
                                                <i class="fas fa-chevron-right"></i>
                                            </a>
                                        </li>
                                    </ul>
                                </nav>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('main-content');
            const toggleSidebarBtn = document.getElementById('toggle-sidebar');
            const dishFormContainer = document.getElementById('dish-form-container');
            const addDishBtn = document.getElementById('add-dish-btn');

            // Gestion du sidebar responsive
            toggleSidebarBtn.addEventListener('click', function() {
                sidebar.classList.toggle('active');
                mainContent.classList.toggle('expanded');
            });

            // Fermer le sidebar quand on clique à l'extérieur (sur mobile)
            document.addEventListener('click', function(event) {
                if (window.innerWidth <= 992) {
                    const isClickInsideSidebar = sidebar.contains(event.target);
                    const isClickOnToggleBtn = toggleSidebarBtn.contains(event.target);
                    
                    if (!isClickInsideSidebar && !isClickOnToggleBtn && sidebar.classList.contains('active')) {
                        sidebar.classList.remove('active');
                        mainContent.classList.remove('expanded');
                    }
                }
            });

            // HTML du formulaire d'ajout de plat
            const dishFormHTML = `
            <div class="card mb-4" id="dish-form">
                <div class="card-header">
                    <h5><i class="fas fa-plus-circle"></i> Ajouter un nouveau plat</h5>
                </div>
                <div class="card-body">
                    <form id="add-dish-form">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="dish-name" class="form-label">Nom du plat*</label>
                                    <input type="text" class="form-control" id="dish-name" required>
                                </div>
                                <div class="mb-3">
                                    <label for="dish-category" class="form-label">Catégorie*</label>
                                    <select class="form-select" id="dish-category" required>
                                        <option value="">Choisir une catégorie</option>
                                        <option value="entree">Entrée</option>
                                        <option value="main">Plat principal</option>
                                        <option value="dessert">Dessert</option>
                                        <option value="drink">Boisson</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="dish-price" class="form-label">Prix (€)*</label>
                                    <input type="number" step="0.01" min="0" class="form-control" id="dish-price" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Image du plat</label>
                                    <div class="file-input-wrapper">
                                        <div class="file-input-custom">
                                            <i class="fas fa-cloud-upload-alt fa-2x mb-2"></i>
                                            <div class="file-input-text">Cliquez pour téléverser une image</div>
                                            <div id="file-name" class="small text-muted mt-1"></div>
                                            <input type="file" id="dish-image" accept="image/*">
                                        </div>
                                    </div>
                                    <div class="image-preview mt-3" id="image-preview">
                                        <img id="preview-img" src="#" alt="Aperçu de l'image" class="img-thumbnail">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="dish-description" class="form-label">Description</label>
                            <textarea class="form-control" id="dish-description" rows="3"></textarea>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="button" class="btn btn-secondary me-2" id="cancel-dish">Annuler</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Enregistrer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            `;

            // Gestion de l'affichage du formulaire
            addDishBtn.addEventListener('click', function() {
                if (dishFormContainer.style.display === 'none' || !dishFormContainer.innerHTML) {
                    dishFormContainer.innerHTML = dishFormHTML;
                    dishFormContainer.style.display = 'block';
                    
                    // Initialiser la prévisualisation d'image
                    const imageInput = document.getElementById('dish-image');
                    const preview = document.getElementById('preview-img');
                    const previewContainer = document.getElementById('image-preview');
                    const fileNameDisplay = document.getElementById('file-name');
                    
                    previewContainer.style.display = 'none';
                    
                    imageInput.addEventListener('change', function(e) {
                        const file = e.target.files[0];
                        if (file) {
                            // Afficher le nom du fichier
                            fileNameDisplay.textContent = file.name;
                            
                            // Prévisualiser l'image
                            const reader = new FileReader();
                            reader.onload = function(e) {
                                preview.src = e.target.result;
                                previewContainer.style.display = 'block';
                            }
                            reader.readAsDataURL(file);
                        } else {
                            fileNameDisplay.textContent = '';
                            previewContainer.style.display = 'none';
                        }
                    });
                    
                    // Annuler
                    document.getElementById('cancel-dish').addEventListener('click', function() {
                        dishFormContainer.style.display = 'none';
                    });
                    
                    // Soumettre
                    document.getElementById('add-dish-form').addEventListener('submit', function(e) {
                        e.preventDefault();
                        
                        // Validation
                        const dishName = document.getElementById('dish-name').value;
                        const dishCategory = document.getElementById('dish-category').value;
                        const dishPrice = document.getElementById('dish-price').value;
                        const dishImage = document.getElementById('dish-image').files[0];
                        
                        if (!dishName || !dishCategory || !dishPrice || !dishImage) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Erreur',
                                text: 'Veuillez remplir tous les champs obligatoires (*)',
                                confirmButtonColor: '#ff6b6b'
                            });
                            return;
                        }

                        // Création du FormData
                        const formData = new FormData();
                        formData.append('nom', dishName);
                        formData.append('categorie', dishCategory);
                        formData.append('prix', dishPrice);
                        formData.append('description', document.getElementById('dish-description').value);
                        formData.append('image', dishImage);
                        formData.append('disponibilite', 'disponible');

                        // Afficher le loader
                        Swal.fire({
                            title: 'Enregistrement en cours...',
                            text: 'Veuillez patienter...',
                            didOpen: () => {
                                Swal.showLoading();
                            },
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            allowEnterKey: false
                        });

                        // Envoi des données
                        fetch('add_plat.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Succès',
                                    text: data.message,
                                    confirmButtonColor: '#4ecdc4'
                                }).then(() => {
                                    window.location.reload();
                                });
                            } else {
                                throw new Error(data.error);
                            }
                        })
                        .catch(error => {
                            Swal.fire({
                                icon: 'error',
                                title: 'Erreur',
                                text: error.message || 'Une erreur est survenue',
                                confirmButtonColor: '#ff6b6b'
                            });
                        });
                    });
                } else {
                    dishFormContainer.style.display = 'none';
                }
            });

            // Initialisation du modal
            const modalEl = document.getElementById('userModal');
            const userModal = new bootstrap.Modal(modalEl);

            // Fonction de modification utilisateur
            window.modifierUtilisateur = function(userId) {
                const userRow = document.querySelector(`tr[data-user-id="${userId}"]`);
                
                if (userRow) {
                    const nom = userRow.querySelector('td:nth-child(1)').textContent;
                    const email = userRow.querySelector('td:nth-child(2)').textContent;
                    const statut = userRow.querySelector('.badge').textContent.toLowerCase();

                    document.getElementById('userId').value = userId;
                    document.getElementById('userName').value = nom;
                    document.getElementById('userEmail').value = email;
                    document.getElementById('userStatus').value = statut;

                    userModal.show();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erreur',
                        text: 'Utilisateur non trouvé',
                        confirmButtonColor: '#ff6b6b'
                    });
                }
            };

            // Fonction de sauvegarde
            window.sauvegarderUtilisateur = function() {
                Swal.fire({
                    title: 'Sauvegarde en cours...',
                    didOpen: () => {
                        Swal.showLoading();
                    },
                    allowOutsideClick: false
                });

                const userId = document.getElementById('userId').value;
                const data = {
                    id: userId,
                    nom: document.getElementById('userName').value,
                    email: document.getElementById('userEmail').value,
                    statut: document.getElementById('userStatus').value
                };

                fetch('update_user.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Succès',
                            text: 'Les modifications ont été enregistrées',
                            confirmButtonColor: '#4ecdc4'
                        }).then(() => {
                            userModal.hide();
                            window.location.reload();
                        });
                    } else {
                        throw new Error(data.error || 'Erreur lors de la mise à jour');
                    }
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erreur',
                        text: error.message || 'Une erreur est survenue',
                        confirmButtonColor: '#ff6b6b'
                    });
                });
            };

            // Fonction de suppression
            window.supprimerUtilisateur = function(userId) {
                Swal.fire({
                    title: 'Êtes-vous sûr ?',
                    text: "Cette action est irréversible !",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ff6b6b',
                    cancelButtonColor: '#4ecdc4',
                    confirmButtonText: 'Oui, supprimer',
                    cancelButtonText: 'Annuler'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Suppression...',
                            didOpen: () => {
                                Swal.showLoading();
                            },
                            allowOutsideClick: false
                        });

                        fetch('delete_user.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({ id: userId })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Supprimé !',
                                    text: 'L\'utilisateur a été supprimé avec succès.',
                                    confirmButtonColor: '#4ecdc4'
                                }).then(() => {
                                    window.location.reload();
                                });
                            } else {
                                throw new Error(data.error || 'Erreur lors de la suppression');
                            }
                        })
                        .catch(error => {
                            Swal.fire({
                                icon: 'error',
                                title: 'Erreur',
                                text: error.message || 'Une erreur est survenue',
                                confirmButtonColor: '#ff6b6b'
                            });
                        });
                    }
                });
            };

            // Initialisation du modal d'ajout d'utilisateur
            const addUserModal = new bootstrap.Modal(document.getElementById('addUserModal'));
            
            // Gestionnaire du bouton d'ajout d'utilisateur
            document.getElementById('add-user-btn').addEventListener('click', function() {
                document.getElementById('addUserForm').reset();
                addUserModal.show();
            });

            // Modifier la fonction ajouterUtilisateur
            window.ajouterUtilisateur = function() {
                const password = document.getElementById('newUserPassword').value;
                
                // Vérification de la longueur du mot de passe
                if (password.length !== 8) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erreur',
                        text: 'Le mot de passe doit contenir exactement 8 caractères',
                        confirmButtonColor: '#ff6b6b'
                    });
                    return;
                }

                const formData = {
                    nom: document.getElementById('newUserName').value,
                    prenom: document.getElementById('newUserPrenom').value,
                    email: document.getElementById('newUserEmail').value,
                    mot_de_passe: password,
                    statut: document.getElementById('newUserStatus').value
                };

                Swal.fire({
                    title: 'Ajout en cours...',
                    didOpen: () => {
                        Swal.showLoading();
                    },
                    allowOutsideClick: false
                });

                fetch('add_user.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(formData)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Succès',
                            text: 'L\'utilisateur a été ajouté avec succès',
                            confirmButtonColor: '#4ecdc4'
                        }).then(() => {
                            addUserModal.hide();
                            window.location.reload();
                        });
                    } else {
                        throw new Error(data.error || 'Erreur lors de l\'ajout');
                    }
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erreur',
                        text: error.message || 'Une erreur est survenue',
                        confirmButtonColor: '#ff6b6b'
                    });
                });
            };

            // Ajouter après l'initialisation du modal
            document.getElementById('newUserPassword').addEventListener('input', function(e) {
                const password = e.target.value;
                const feedback = this.nextElementSibling;
                
                if (password.length < 8) {
                    feedback.className = 'form-text text-danger';
                    feedback.textContent = `Il manque ${8 - password.length} caractère(s)`;
                } else if (password.length > 8) {
                    feedback.className = 'form-text text-danger';
                    feedback.textContent = `${password.length - 8} caractère(s) en trop`;
                } else {
                    feedback.className = 'form-text text-success';
                    feedback.textContent = 'Longueur correcte (8 caractères)';
                }
            });

            // Initialisation du modal de modification de plat
            const editDishModal = new bootstrap.Modal(document.getElementById('editDishModal'));

            // Fonction de modification de plat
            window.modifierPlat = function(platId) {
                Swal.fire({
                    title: 'Chargement...',
                    didOpen: () => {
                        Swal.showLoading();
                    },
                    allowOutsideClick: false
                });

                // Récupérer les données du plat
                fetch('get_plat.php?id=' + platId)
                    .then(response => response.json())
                    .then(data => {
                        Swal.close();
                        if (data.error) {
                            throw new Error(data.error);
                        }

                        // Remplir le formulaire
                        document.getElementById('editDishId').value = data.id_plat;
                        document.getElementById('editDishName').value = data.nom;
                        document.getElementById('editDishCategory').value = data.categorie;
                        document.getElementById('editDishPrice').value = data.prix;
                        document.getElementById('editDishDescription').value = data.description;
                        document.getElementById('editDishDisponibilite').value = data.disponibilite;
                        document.querySelector('#currentImage img').src = '../uploads/plats/' + data.image;

                        editDishModal.show();
                    })
                    .catch(error => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Erreur',
                            text: error.message || 'Impossible de récupérer les données du plat',
                            confirmButtonColor: '#ff6b6b'
                        });
                    });
            };

            // Fonction de sauvegarde des modifications
            window.sauvegarderPlat = function() {
                const formData = new FormData();
                formData.append('id', document.getElementById('editDishId').value);
                formData.append('nom', document.getElementById('editDishName').value);
                formData.append('categorie', document.getElementById('editDishCategory').value);
                formData.append('prix', document.getElementById('editDishPrice').value);
                formData.append('description', document.getElementById('editDishDescription').value);
                formData.append('disponibilite', document.getElementById('editDishDisponibilite').value);

                const newImage = document.getElementById('editDishImage').files[0];
                if (newImage) {
                    formData.append('image', newImage);
                }

                Swal.fire({
                    title: 'Sauvegarde en cours...',
                    didOpen: () => {
                        Swal.showLoading();
                    },
                    allowOutsideClick: false
                });

                fetch('update_plat.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Succès',
                            text: 'Le plat a été modifié avec succès',
                            confirmButtonColor: '#4ecdc4'
                        }).then(() => {
                            editDishModal.hide();
                            window.location.reload();
                        });
                    } else {
                        throw new Error(data.error || 'Erreur lors de la modification');
                    }
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erreur',
                        text: error.message || 'Une erreur est survenue',
                        confirmButtonColor: '#ff6b6b'
                    });
                });
            };

            // Fonction de suppression de plat
            window.supprimerPlat = function(platId) {
                Swal.fire({
                    title: 'Êtes-vous sûr ?',
                    text: "Cette action est irréversible !",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ff6b6b',
                    cancelButtonColor: '#4ecdc4',
                    confirmButtonText: 'Oui, supprimer',
                    cancelButtonText: 'Annuler'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Suppression en cours...',
                            didOpen: () => {
                                Swal.showLoading();
                            },
                            allowOutsideClick: false
                        });

                        fetch('delete_plat.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({ id: platId })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Supprimé !',
                                    text: 'Le plat a été supprimé avec succès.',
                                    confirmButtonColor: '#4ecdc4'
                                }).then(() => {
                                    window.location.reload();
                                });
                            } else {
                                throw new Error(data.error || 'Erreur lors de la suppression');
                            }
                        })
                        .catch(error => {
                            Swal.fire({
                                icon: 'error',
                                title: 'Erreur',
                                text: error.message || 'Une erreur est survenue',
                                confirmButtonColor: '#ff6b6b'
                            });
                        });
                    }
                });
            };

            // Fonction pour filtrer les messages
            function filterMessages(status) {
                const rows = document.querySelectorAll('.message-row');
                rows.forEach(row => {
                    if (status === 'tous') {
                        row.style.display = '';
                    } else {
                        row.style.display = row.classList.contains(status) ? '' : 'none';
                    }
                });
            }

            // Fonction pour voir un message
            function voirMessage(messageId) {
                // Implémenter l'affichage détaillé du message
                fetch(`get_message.php?id=${messageId}`)
                    .then(response => response.json())
                    .then(data => {
                        Swal.fire({
                            title: data.sujet,
                            html: `
                                <p><strong>De :</strong> ${data.nom_complet}</p>
                                <p><strong>Date :</strong> ${data.date_formattee}</p>
                                <hr>
                                <p>${data.message}</p>
                            `,
                            confirmButtonColor: '#4ecdc4'
                        });
                        // Marquer comme lu si nécessaire
                        if (data.statut === 'non_lu') {
                            updateMessageStatus(messageId, 'lu');
                        }
                    });
            }
        });
    </script>

    <!-- Ajouter avant la fermeture de body -->
    <div class="modal fade" id="userModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Modifier l'utilisateur</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="userForm">
                        <input type="hidden" id="userId">
                        <div class="mb-3">
                            <label class="form-label">Nom complet</label>
                            <input type="text" class="form-control" id="userName" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" id="userEmail" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Statut</label>
                            <select class="form-select" id="userStatus">
                                <option value="actif">Actif</option>
                                <option value="inactif">Inactif</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-primary" onclick="sauvegarderUtilisateur()">
                        <i class="fas fa-save me-2"></i>Sauvegarder
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ajouter un utilisateur</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addUserForm">
                        <div class="mb-3">
                            <label class="form-label">Nom</label>
                            <input type="text" class="form-control" id="newUserName" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Prénom</label>
                            <input type="text" class="form-control" id="newUserPrenom" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" id="newUserEmail" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mot de passe</label>
                            <input type="password" class="form-control" id="newUserPassword" 
                                   minlength="8" required 
                                   pattern=".{8,}"
                                   title="Le mot de passe doit contenir au moins 8 caractères">
                            <div class="form-text text-muted">
                                Le mot de passe doit contenir exactement 8 caractères
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Statut</label>
                            <select class="form-select" id="newUserStatus">
                                <option value="actif">Actif</option>
                                <option value="inactif">Inactif</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-primary" onclick="ajouterUtilisateur()">
                        <i class="fas fa-plus me-2"></i>Ajouter
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editDishModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Modifier le plat</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editDishForm">
                        <input type="hidden" id="editDishId">
                        <div class="mb-3">
                            <label class="form-label">Nom du plat*</label>
                            <input type="text" class="form-control" id="editDishName" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Catégorie*</label>
                            <select class="form-select" id="editDishCategory" required>
                                <option value="entree">Entrée</option>
                                <option value="main">Plat principal</option>
                                <option value="dessert">Dessert</option>
                                <option value="drink">Boisson</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Prix (€)*</label>
                            <input type="number" step="0.01" min="0" class="form-control" id="editDishPrice" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" id="editDishDescription" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Disponibilité</label>
                            <select class="form-select" id="editDishDisponibilite">
                                <option value="disponible">Disponible</option>
                                <option value="pas disponible">Pas disponible</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nouvelle image</label>
                            <input type="file" class="form-control" id="editDishImage" accept="image/*">
                            <div id="currentImage" class="mt-2">
                                <img src="" alt="Image actuelle" class="img-thumbnail" style="max-height: 100px;">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-primary" onclick="sauvegarderPlat()">
                        <i class="fas fa-save me-2"></i>Sauvegarder
                    </button>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
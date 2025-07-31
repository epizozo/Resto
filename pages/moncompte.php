<?php
session_start();
require_once '../config/database.php';

// Fonction de vérification de session
function checkSession($conn, $userId) {
    try {
        // Log pour débogage
        error_log("Vérification de session pour l'utilisateur ID: $userId");
        
        // Vérifier si la session est valide
        $stmt = $conn->prepare("
            SELECT COUNT(*) 
            FROM sessions_utilisateur s
            INNER JOIN utilisateurs u ON s.id_utilisateur = u.id_utilisateur
            WHERE s.id_utilisateur = ? 
            AND s.date_expiration > NOW()
            AND u.statut = 'actif'
        ");
        $stmt->execute([$userId]);
        
        $result = $stmt->fetchColumn() > 0;
        error_log("Résultat de la vérification: " . ($result ? "true" : "false"));
        
        return $result;
    } catch(PDOException $e) {
        error_log("Erreur de vérification de session : " . $e->getMessage());
        return false;
    }
}

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id']) || !checkSession($conn, $_SESSION['user_id'])) {
    error_log("Redirection vers login.php - Session ID: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'non défini'));
    header('Location: login.php');
    exit;
}

// Récupérer les informations de l'utilisateur
try {
    // Récupérer les informations de l'utilisateur
    $stmt = $conn->prepare("
        SELECT nom, email, DATE_FORMAT(date_creation, '%Y') as annee_inscription 
        FROM utilisateurs 
        WHERE id_utilisateur = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if ($user) {
        $nom_complet = $user['nom'];
    } else {
        $error = "Impossible de récupérer vos informations.";
    }

    // Traitement du formulaire de mise à jour
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nom = htmlspecialchars($_POST['nom']);
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

        try {
            $stmt = $conn->prepare("
                UPDATE utilisateurs 
                SET nom = ?, email = ?, date_mise_a_jour = CURRENT_TIMESTAMP 
                WHERE id_utilisateur = ?
            ");
            $stmt->execute([$nom, $email, $_SESSION['user_id']]);
            
            header('Location: moncompte.php?success=1');
            exit;
        } catch(PDOException $e) {
            $error = "Une erreur est survenue lors de la mise à jour.";
        }
    }
} catch(PDOException $e) {
    error_log("Erreur : " . $e->getMessage());
    $error = "Une erreur est survenue.";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Compte - KODJO</title>
    <link rel="icon" type="image/jpg" href="../assets/img/hamburger.png" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-dark@4/dark.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --primary-color: #ff6b6b;
            --secondary-color: #4ecdc4;
        }

        /* Styles Navbar */
        .custom-navbar {
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
            padding: 1rem 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            transform: translateY(0);
        }

        .navbar-brand {
            font-size: 1.8rem;
            font-weight: 700;
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            transition: transform 0.3s;
        }

        .navbar-brand:hover {
            transform: scale(1.05);
        }

        .nav-link {
            position: relative;
            margin: 0 0.5rem;
            padding: 0.5rem 1rem;
            color: white !important;
            font-weight: 500;
            font-size: 0.9rem;
            transition: all 0.3s;
            border-radius: 25px;
        }

        .nav-link-text {
            position: relative;
            display: inline-block;
            transition: transform 0.3s;
        }

        .nav-link:hover .nav-link-text {
            transform: translateY(-2px);
        }

        .nav-link-border {
            position: absolute;
            bottom: 0;
            left: 50%;
            width: 0;
            height: 2px;
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            transition: all 0.3s;
            transform: translateX(-50%);
        }

        .nav-link:hover .nav-link-border,
        .nav-link.active .nav-link-border {
            width: 80%;
        }

        .nav-link.active {
            background: rgba(255,255,255,0.1);
        }

        .btn-signup {
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            border-radius: 30px;
            padding: 0.5rem 1.5rem !important;
            color: white !important;
            font-size: 0.9rem;
            transition: all 0.3s;
            border: none;
            margin-left: 1rem;
        }

        .btn-signup:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 107, 107, 0.4);
        }

        .navbar-toggler {
            border: none;
            padding: 0.5rem;
        }

        .navbar-toggler:focus {
            box-shadow: none;
            outline: none;
        }

        .navbar-toggler-icon {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba(255, 255, 255, 0.8)' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
        }

        @media (max-width: 991.98px) {
            .navbar-nav {
                padding: 1rem 0;
            }
            
            .nav-link {
                margin: 0.5rem 0;
            }
            
            .btn-signup {
                margin: 0.5rem 0;
                text-align: center;
            }
        }

        /* Animation Scroll Navbar */
        .custom-navbar.scrolled {
            padding: 0.5rem 0;
            background: rgba(0, 0, 0, 0.95);
        }

        /* Styles pour la page compte */
        .account-section {
            margin-top: 100px;
            padding: 50px 0;
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)),
                        url('../assets/img/side-view-smiley-people-with-delicious-food.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            min-height: calc(100vh - 100px);
        }

        .account-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 5px 25px rgba(0,0,0,0.2);
            transition: all 0.3s ease;
        }

        .profile-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .profile-img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            margin-bottom: 1rem;
            border: 5px solid var(--primary-color);
            padding: 5px;
        }

        .nav-pills .nav-link {
            color: #333;
            border-radius: 50px;
            padding: 15px 25px;
            margin: 10px 0;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            background: rgba(255, 255, 255, 0.9);
        }

        .nav-pills .nav-link:hover {
            transform: translateX(10px);
            background: rgba(255, 107, 107, 0.1);
        }

        .nav-pills .nav-link.active {
            background: var(--primary-color);
            color: white;
            transform: translateX(10px);
            box-shadow: 0 5px 15px rgba(255, 107, 107, 0.3);
        }

        .nav-pills .nav-link i {
            font-size: 1.2rem;
            margin-right: 15px;
            transition: all 0.3s;
        }

        .nav-pills .nav-link:hover i {
            transform: scale(1.2);
        }

        @media (max-width: 768px) {
            .account-card {
                margin-bottom: 20px;
            }

            .nav-pills .nav-link {
                padding: 12px 20px;
                font-size: 0.9rem;
            }
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(255,107,107,0.25);
        }

        .btn-primary {
            background: var(--primary-color);
            border: none;
            border-radius: 50px;
            padding: 10px 30px;
            transition: all 0.3s;
        }

        .btn-primary:hover {
            background: var(--secondary-color);
            transform: translateY(-3px);
        }

        .settings-buttons {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .btn-logout, .btn-delete {
            position: relative;
            padding: 15px 30px;
            border-radius: 50px;
            border: none;
            color: white;
            font-weight: 600;
            overflow: hidden;
            transition: all 0.3s ease;
            width: 100%;
            text-align: left;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .btn-logout {
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
        }

        .btn-delete {
            background: linear-gradient(45deg, #ff4b4b, #ff7676);
        }

        .btn-logout:hover, .btn-delete:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .btn-icon {
            width: 35px;
            height: 35px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .btn-logout:hover .btn-icon, .btn-delete:hover .btn-icon {
            transform: rotate(360deg);
        }

        .form-control:disabled {
            background-color: #f8f9fa;
            cursor: not-allowed;
        }

        #saveBtn {
            display: none;
        }

        .gap-2 {
            gap: 0.5rem;
        }

        .btn-success {
            background-color: #28a745;
            color: white;
        }

        .btn-success:hover {
            background-color: #218838;
            transform: translateY(-3px);
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg sticky-top custom-navbar">
        <div class="container">
            <a class="navbar-brand" href="../index.html">
                <i class="fas fa-utensils me-2"></i>KODJO
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../index.html">
                            <span class="nav-link-text">Accueil</span>
                            <span class="nav-link-border"></span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="menu.html">
                            <span class="nav-link-text">Recettes</span>
                            <span class="nav-link-border"></span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../index.html#about">
                            <span class="nav-link-text">À propos</span>
                            <span class="nav-link-border"></span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">
                            <span class="nav-link-text">Contact</span>
                            <span class="nav-link-border"></span>
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">
                            <i class="fas fa-user me-1"></i>
                            <span class="nav-link-text">Connexion</span>
                            <span class="nav-link-border"></span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn-signup" href="signup.php">    
                            <i class="fas fa-user-plus me-1"></i>Inscription
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Section Compte -->
    <section class="account-section">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <div class="account-card">
                        <div class="profile-header">
                            <img src="../assets/img/moncompte.png" alt="Photo de profil" class="profile-img">
                            <h4><?php echo htmlspecialchars($nom_complet); ?></h4>
                            <p class="text-muted">Membre depuis <?php echo htmlspecialchars($user['annee_inscription']); ?></p>
                        </div>
                        <div class="nav flex-column nav-pills" role="tablist">
                            <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#profile">
                                <i class="fas fa-user"></i>Mon Profil
                            </button>
                            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#settings">
                                <i class="fas fa-cog"></i>Paramètres
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="profile">
                            <div class="account-card">
                                <h3 class="mb-4">Mon Profil</h3>
                                <?php if(isset($_GET['success'])): ?>
                                    <div class="alert alert-success">
                                        Vos informations ont été mises à jour avec succès!
                                    </div>
                                <?php endif; ?>
                                <?php if(isset($error)): ?>
                                    <div class="alert alert-danger">
                                        <?php echo $error; ?>
                                    </div>
                                <?php endif; ?>
                                <form method="POST" id="profileForm">
                                    <div class="mb-3">
                                        <label class="form-label">Nom complet</label>
                                        <input type="text" name="nom" class="form-control" 
                                               value="<?php echo htmlspecialchars($user['nom']); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" name="email" class="form-control" 
                                               value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button type="button" id="editBtn" class="btn btn-primary" onclick="toggleEdit()">
                                            <i class="fas fa-edit me-2"></i>Modifier
                                        </button>
                                        <button type="submit" id="saveBtn" class="btn btn-success">
                                            <i class="fas fa-save me-2"></i>Sauvegarder
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="settings">
                            <div class="account-card">
                                <h3 class="mb-4">Paramètres du compte</h3>
                                <div class="settings-buttons">
                                    <button class="btn-logout" onclick="handleLogout()">
                                        <span>Déconnexion</span>
                                        <span class="btn-icon">
                                            <i class="fas fa-sign-out-alt"></i>
                                        </span>
                                    </button>
                                    <button class="btn-delete" onclick="handleDelete()">
                                        <span>Supprimer mon compte</span>
                                        <span class="btn-icon">
                                            <i class="fas fa-trash-alt"></i>
                                        </span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        async function handleLogout() {
            try {
                const response = await fetch('logout.php');
                if (response.ok) {
                    window.location.href = '../index.html';
                }
            } catch (error) {
                console.error('Erreur lors de la déconnexion:', error);
            }
        }

        async function handleDelete() {
            const result = await Swal.fire({
                title: 'Êtes-vous sûr ?',
                text: "Cette action est irréversible. Votre compte sera définitivement supprimé !",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ff4b4b',
                cancelButtonColor: '#4ecdc4',
                confirmButtonText: 'Oui, supprimer',
                cancelButtonText: 'Annuler'
            });

            if (result.isConfirmed) {
                try {
                    const response = await fetch('delete_account.php', {
                        method: 'POST'
                    });
                    
                    if (response.ok) {
                        await Swal.fire({
                            title: 'Compte supprimé !',
                            text: 'Votre compte a été supprimé avec succès.',
                            icon: 'success',
                            confirmButtonColor: '#4ecdc4'
                        });
                        window.location.href = '../index.html';
                    }
                } catch (error) {
                    console.error('Erreur lors de la suppression:', error);
                    Swal.fire({
                        title: 'Erreur !',
                        text: 'Une erreur est survenue lors de la suppression du compte.',
                        icon: 'error',
                        confirmButtonColor: '#ff4b4b'
                    });
                }
            }
        }

        let isEditing = false;

        function toggleEdit() {
            const inputs = document.querySelectorAll('.form-control');
            const editBtn = document.getElementById('editBtn');
            const saveBtn = document.getElementById('saveBtn');
            
            isEditing = !isEditing;
            
            inputs.forEach(input => {
                input.disabled = !isEditing;
            });
            
            if (isEditing) {
                editBtn.style.display = 'none';
                saveBtn.style.display = 'block';
            } else {
                editBtn.style.display = 'block';
                saveBtn.style.display = 'none';
            }
        }

        function initForm() {
            const inputs = document.querySelectorAll('.form-control');
            const saveBtn = document.getElementById('saveBtn');
            
            inputs.forEach(input => {
                input.disabled = true;
            });
            
            saveBtn.style.display = 'none';
        }

        // Initialiser le formulaire au chargement
        document.addEventListener('DOMContentLoaded', initForm);
    </script>
</body>
</html>
<?php
$conn = require_once '../config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom_complet = htmlspecialchars($_POST['nom_complet']);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $sujet = htmlspecialchars($_POST['sujet']);
    $message = htmlspecialchars($_POST['message']);
    
    try {
        $sql = "INSERT INTO messages_contact (nom_complet, email, sujet, message) 
                VALUES (:nom_complet, :email, :sujet, :message)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            'nom_complet' => $nom_complet,
            'email' => $email,
            'sujet' => $sujet,
            'message' => $message
        ]);
        
        echo 'success';
        exit;
    } catch(PDOException $e) {
        echo 'error';
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contactez KODJO - Restaurant Africain</title>
    <link rel="icon" type="image/jpg" href="../assets/img/hamburger.png" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
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

        /* Styles Formulaire */
        .contact-header {
            background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('../assets/img/r4.jpg');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 100px 0;
            text-align: center;
            margin-top: 76px;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(255,107,107,0.25);
        }

        .btn-contact {
            background: var(--primary-color);
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 50px;
            transition: all 0.3s ease;
        }

        .btn-contact:hover {
            background: var(--secondary-color);
            transform: translateY(-3px);
            color: white;
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
                        <a class="nav-link active" href="#">
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
                        <a class="nav-link btn-signup" href="ignup.html">
                            <i class="fas fa-user-plus me-1"></i>Inscription
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- En-tête -->
    <header class="contact-header">
        <div class="container">
            <h1 class="display-4 fw-bold mb-4">Contactez KODJO</h1>
            <p class="lead">Nous sommes à votre écoute pour toute question ou réservation</p>
        </div>
    </header>

    <!-- Formulaire de contact -->
    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <form class="p-5 bg-white rounded-3 shadow" method="POST">
                        <h3 class="mb-4 text-center">Envoyez-nous un message</h3>
                        <div class="mb-3">
                            <label for="nom_complet" class="form-label">Nom complet</label>
                            <input type="text" class="form-control" id="nom_complet" name="nom_complet" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="sujet" class="form-label">Sujet</label>
                            <input type="text" class="form-control" id="sujet" name="sujet" required>
                        </div>
                        <div class="mb-3">
                            <label for="message" class="form-label">Message</label>
                            <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                        </div>
                        <div class="text-center">
                            <button type="submit" class="btn btn-contact">Envoyer le message</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
    window.addEventListener('scroll', function() {
        const navbar = document.querySelector('.custom-navbar');
        if (window.scrollY > 100) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });
    </script>
    <script>
    document.querySelector('form').addEventListener('submit', function(e) {
        e.preventDefault(); // Empêche la soumission normale du formulaire
        
        const formData = new FormData(this);
        const submitButton = this.querySelector('button[type="submit"]');
        
        // Désactive le bouton pendant l'envoi
        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Envoi...';
        
        fetch(window.location.href, {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            if(data.includes('success')) {
                Swal.fire({
                    icon: 'success',
                    title: 'Succès!',
                    text: 'Votre message a été envoyé avec succès!',
                    confirmButtonColor: '#ff6b6b'
                }).then(() => {
                    // Réinitialise le formulaire après succès
                    this.reset();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur!',
                    text: 'Une erreur s\'est produite lors de l\'envoi du message.',
                    confirmButtonColor: '#ff6b6b'
                });
            }
        })
        .catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'Erreur!',
                text: 'Une erreur s\'est produite lors de l\'envoi du message.',
                confirmButtonColor: '#ff6b6b'
            });
        })
        .finally(() => {
            // Réactive le bouton après l'envoi
            submitButton.disabled = false;
            submitButton.innerHTML = 'Envoyer le message';
        });
    });
    </script>
</body>
</html>
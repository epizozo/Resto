<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    session_start();

    try {
        require_once '../config/database.php';

        // Récupération des données POST JSON
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Données JSON invalides');
        }

        // Récupération et validation des données
        $email = filter_var($data['email'] ?? '', FILTER_SANITIZE_EMAIL);
        $password = $data['password'] ?? '';
        $nom = htmlspecialchars($data['nom'] ?? '', ENT_QUOTES, 'UTF-8');
        $prenom = htmlspecialchars($data['prenom'] ?? '', ENT_QUOTES, 'UTF-8');

        // Validation des champs
        if (empty($email)) {
            throw new Exception('L\'email est obligatoire');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Format d\'email invalide');
        }

        if (empty($password)) {
            throw new Exception('Le mot de passe est obligatoire');
        }

        if (strlen($password) < 8) {
            throw new Exception('Le mot de passe doit contenir au moins 8 caractères');
        }

        if (empty($nom)) {
            throw new Exception('Le nom est obligatoire');
        }

        if (empty($prenom)) {
            throw new Exception('Le prénom est obligatoire');
        }

        // Vérification si l'email existe déjà
        $stmt = $conn->prepare("SELECT id_utilisateur FROM utilisateurs WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() > 0) {
            throw new Exception('Cet email est déjà utilisé');
        }

        // Hashage du mot de passe
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // Insertion du nouvel utilisateur
        $stmt = $conn->prepare("
            INSERT INTO utilisateurs (
                email, 
                mot_de_passe_hash, 
                nom, 
                prenom, 
                statut,
                date_creation
            ) VALUES (
                ?, 
                ?, 
                ?, 
                ?, 
                'actif',
                NOW()
            )
        ");

        $result = $stmt->execute([
            $email,
            $password_hash,
            $nom,
            $prenom
        ]);

        // Création de la session utilisateur après l'insertion
        if ($result) {
            $id_utilisateur = $conn->lastInsertId();
            $jeton = bin2hex(random_bytes(32));
            
            $stmt = $conn->prepare("
                INSERT INTO sessions_utilisateur (
                    id_utilisateur, 
                    jeton_session, 
                    adresse_ip, 
                    navigateur, 
                    date_expiration
                ) VALUES (
                    ?, 
                    ?, 
                    ?, 
                    ?, 
                    DATE_ADD(NOW(), INTERVAL 24 HOUR)
                )
            ");
            
            $stmt->execute([
                $id_utilisateur,
                $jeton,
                $_SERVER['REMOTE_ADDR'],
                $_SERVER['HTTP_USER_AGENT']
            ]);
        }

        // Réponse JSON en cas de succès
        echo json_encode([
            'success' => true,
            'message' => 'Inscription réussie avec succès',
            'token' => $jeton
        ]);
        exit;

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Erreur de base de données : ' . $e->getMessage()
        ]);
        exit;
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resto - Création de compte</title>
    <link rel="icon" type="image/jpg" href="../assets/img/hamburger.png" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
    <link href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-dark@4/dark.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #ff6b6b;
            --secondary-color: #4ecdc4;
        }

        body {
            background: url('https://images.unsplash.com/photo-1504674900247-0877df9cc836?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80') no-repeat center center fixed;
            background-size: cover;
            min-height: 100vh;
            position: relative;
            overflow: hidden;
            margin: 0;
            padding: 0;
            background-attachment: fixed;
            perspective: 1000px;
        }

        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 0;
        }

        .signup-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
            box-sizing: border-box;
        }

        .signup-container {
            width: 100%;
            max-width: 500px;
            padding: 40px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            position: relative;
            z-index: 1;
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255,255,255,0.3);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .signup-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            border-radius: 20px 20px 0 0;
        }

        .signup-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
        }

        .signup-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .signup-header h2 {
            font-weight: 700;
            color: #333;
            margin-bottom: 10px;
            font-size: 2rem;
        }

        .signup-header p {
            color: #666;
        }

        .form-control {
            height: 50px;
            border-radius: 10px;
            padding-left: 20px;
            border: 1px solid #ddd;
            transition: all 0.3s;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(255, 107, 107, 0.25);
            transform: translateY(-2px);
        }

        .input-group-text {
            background: #f8f9fa;
            border-right: none;
            border-radius: 10px 0 0 10px !important;
        }

        .input-group .form-control {
            border-left: none;
            border-radius: 0 10px 10px 0 !important;
        }

        .btn-signup {
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            border: none;
            height: 50px;
            border-radius: 10px;
            font-weight: 600;
            letter-spacing: 1px;
            transition: all 0.3s;
            margin-top: 10px;
        }

        .btn-signup:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 107, 107, 0.4);
        }

        .signup-footer {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }

        .signup-footer a {
            color: var(--primary-color);
            text-decoration: none;
            transition: all 0.3s;
            font-weight: 500;
        }

        .signup-footer a:hover {
            color: var(--secondary-color);
            text-decoration: underline;
        }

        .food-sticker {
            position: absolute;
            z-index: 0;
            user-select: none;
            pointer-events: none;
            animation: float 6s ease-in-out infinite;
            opacity: 0.8;
            width: 80px;
            height: 80px;
            object-fit: contain;
        }

        @keyframes float {
            0% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(5deg); }
            100% { transform: translateY(0px) rotate(0deg); }
        }

        .sticker-1 { top: 10%; left: 5%; animation-delay: 0s; }
        .sticker-2 { top: 20%; right: 5%; animation-delay: 1s; }
        .sticker-3 { bottom: 15%; left: 8%; animation-delay: 2s; }
        .sticker-4 { bottom: 25%; right: 10%; animation-delay: 3s; }

        @media (max-width: 768px) {
            .signup-container {
                padding: 30px;
                max-width: 90%;
            }
            .food-sticker {
                width: 60px;
                height: 60px;
            }
        }
    </style>
</head>
<body>
    <img src="https://cdn-icons-png.flaticon.com/512/5787/5787100.png" class="food-sticker sticker-1" alt="Burger">
    <img src="https://cdn-icons-png.flaticon.com/512/5787/5787132.png" class="food-sticker sticker-2" alt="Pizza">
    <img src="https://cdn-icons-png.flaticon.com/512/5787/5787096.png" class="food-sticker sticker-3" alt="Taco">
    <img src="https://cdn-icons-png.flaticon.com/512/5787/5787120.png" class="food-sticker sticker-4" alt="Sushi">

    <div class="signup-wrapper">
        <div class="signup-container" data-aos="fade-up">
            <div class="signup-header" data-aos="fade-down" data-aos-delay="200">
                <h2>Créer un compte</h2>
                <p>Rejoignez notre communauté culinaire</p>
            </div>

            <form id="signupForm" data-aos="fade-up" data-aos-delay="400">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="firstName" class="form-label">Prénom</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text" class="form-control" id="firstName" placeholder="Votre prénom" required>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="lastName" class="form-label">Nom</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text" class="form-control" id="lastName" placeholder="Votre nom" required>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Adresse email</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        <input type="email" class="form-control" id="email" placeholder="votre@email.com" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Mot de passe</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" class="form-control" id="password" placeholder="Créez un mot de passe" required minlength="8">
                    </div>
                    <small class="text-muted">Minimum 8 caractères</small>
                </div>

                <div class="mb-3">
                    <label for="confirmPassword" class="form-label">Confirmer le mot de passe</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" class="form-control" id="confirmPassword" placeholder="Confirmez votre mot de passe" required>
                    </div>
                </div>

                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" id="terms" required>
                    <label class="form-check-label" for="terms">
                        J'accepte les <a href="#" class="text-decoration-none">conditions d'utilisation</a>
                    </label>
                </div>

                <button type="submit" class="btn btn-signup w-100 mb-3">
                    <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                    S'inscrire
                </button>

                <div class="signup-footer">
                    <p>Vous avez déjà un compte ? <a href="login.php">Se connecter</a></p>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script>
        // Initialisation des animations
        AOS.init({
            duration: 1000,
            once: true
        });

        // Gestion de l'inscription
        document.getElementById('signupForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const submitBtn = e.target.querySelector('button[type="submit"]');
            const spinner = submitBtn.querySelector('.spinner-border');
            
            try {
                // Validation des données
                const email = document.getElementById('email').value;
                const password = document.getElementById('password').value;
                const confirmPassword = document.getElementById('confirmPassword').value;
                const firstName = document.getElementById('firstName').value;
                const lastName = document.getElementById('lastName').value;
                
                if (password !== confirmPassword) {
                    throw new Error('Les mots de passe ne correspondent pas');
                }

                if (!document.getElementById('terms').checked) {
                    throw new Error('Vous devez accepter les conditions d\'utilisation');
                }

                // Activation du loader
                submitBtn.disabled = true;
                spinner.classList.remove('d-none');

                // Envoi des données au serveur
                const response = await fetch(window.location.href, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        email: email,
                        password: password,
                        nom: lastName,
                        prenom: firstName
                    })
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Erreur lors de l\'inscription');
                }

                // Succès de l'inscription
                await Swal.fire({
                    icon: 'success',
                    title: 'Félicitations !',
                    text: 'Votre compte a été créé avec succès',
                    confirmButtonColor: '#4ecdc4'
                });

                // Redirection vers la page de connexion
                window.location.href = 'login.php';

            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: error.message,
                    confirmButtonColor: '#ff6b6b'
                });
            } finally {
                submitBtn.disabled = false;
                spinner.classList.add('d-none');
            }
        });

        // Ajout d'animations supplémentaires
        document.addEventListener('DOMContentLoaded', function() {
            const foodIcons = [
                'https://cdn-icons-png.flaticon.com/512/5787/5787079.png',
                'https://cdn-icons-png.flaticon.com/512/5787/5787087.png',
                'https://cdn-icons-png.flaticon.com/512/5787/5787113.png',
                'https://cdn-icons-png.flaticon.com/512/5787/5787141.png'
            ];

            foodIcons.forEach((icon, index) => {
                const sticker = document.createElement('img');
                sticker.src = icon;
                sticker.className = 'food-sticker';
                sticker.style.top = `${Math.random() * 80 + 10}%`;
                sticker.style.left = `${Math.random() * 80 + 10}%`;
                sticker.style.animationDelay = `${index}s`;
                document.body.appendChild(sticker);
            });
        });
    </script>
</body>
</html>
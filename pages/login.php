<?php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    try {
        // Récupération des données du formulaire
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);

        // Validation
        if (empty($email) || empty($password)) {
            throw new Exception("Veuillez remplir tous les champs");
        }

        // Vérification de l'utilisateur
        $stmt = $conn->prepare("
            SELECT id_utilisateur, email, mot_de_passe_hash, nom, prenom, statut 
            FROM utilisateurs 
            WHERE email = ?
        ");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            throw new Exception("Email ou mot de passe incorrect");
        }

        // Vérification du mot de passe
        if (!password_verify($password, $user['mot_de_passe_hash'])) {
            throw new Exception("Email ou mot de passe incorrect");
        }

        // Vérification du statut
        if ($user['statut'] !== 'actif') {
            throw new Exception("Ce compte est désactivé");
        }

        // Création de la session
        $_SESSION['user'] = [
            'id' => $user['id_utilisateur'],
            'email' => $user['email'],
            'nom' => $user['nom'],
            'prenom' => $user['prenom']
        ];

        // Option "Se souvenir de moi"
        if ($remember) {
            $token = bin2hex(random_bytes(32));
            setcookie('remember_token', $token, time() + 30*24*60*60, '/', '', true, true);
            
            $stmt = $conn->prepare("
                UPDATE utilisateurs 
                SET remember_token = ?,
                    token_expiry = DATE_ADD(NOW(), INTERVAL 30 DAY)
                WHERE id_utilisateur = ?
            ");
            $stmt->execute([$token, $user['id_utilisateur']]);
        }

        // Mise à jour de la dernière connexion
        $stmt = $conn->prepare("
            UPDATE utilisateurs 
            SET derniere_connexion = NOW() 
            WHERE id_utilisateur = ?
        ");
        $stmt->execute([$user['id_utilisateur']]);

        // Insertion dans la table sessions_utilisateur
        $stmt = $conn->prepare("
            INSERT INTO sessions_utilisateur (
                id_utilisateur, 
                jeton_session, 
                adresse_ip, 
                navigateur, 
                date_expiration
            ) VALUES (?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 24 HOUR))
        ");
        
        $jeton = bin2hex(random_bytes(32));
        $stmt->execute([
            $user['id_utilisateur'],
            $jeton,
            $_SERVER['REMOTE_ADDR'],
            $_SERVER['HTTP_USER_AGENT']
        ]);

        // Après une connexion réussie dans login.php
        $token = bin2hex(random_bytes(32));
        $stmt = $conn->prepare("
            INSERT INTO sessions_utilisateur 
            (id_utilisateur, jeton_session, adresse_ip, navigateur, date_expiration) 
            VALUES (?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 1 DAY))
        ");
        $stmt->execute([
            $user['id_utilisateur'],
            $token,
            $_SERVER['REMOTE_ADDR'],
            $_SERVER['HTTP_USER_AGENT']
        ]);

        $_SESSION['user_id'] = $user['id_utilisateur'];
        $_SESSION['token'] = $token;

        echo json_encode([
            'success' => true,
            'message' => 'Connexion réussie',
            'redirect' => '../index.html',
            'token' => $jeton,
            'user' => [
                'prenom' => $user['prenom'],
                'nom' => $user['nom']
            ]
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

// Vérification du cookie "Se souvenir de moi"
if (!isset($_SESSION['user']) && isset($_COOKIE['remember_token'])) {
    try {
        $stmt = $conn->prepare("
            SELECT id_utilisateur, email, nom, prenom 
            FROM utilisateurs 
            WHERE remember_token = ? 
            AND token_expiry > NOW() 
            AND statut = 'actif'
        ");
        $stmt->execute([$_COOKIE['remember_token']]);
        $user = $stmt->fetch();

        if ($user) {
            $_SESSION['user'] = [
                'id' => $user['id_utilisateur'],
                'email' => $user['email'],
                'nom' => $user['nom'],
                'prenom' => $user['prenom']
            ];
        } else {
            // Suppression du cookie si token invalide ou expiré
            setcookie('remember_token', '', time() - 3600, '/');
        }
    } catch (Exception $e) {
        // Suppression du cookie en cas d'erreur
        setcookie('remember_token', '', time() - 3600, '/');
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resto - Connexion</title>
    <link rel="icon" type="image/jpg" href="../assets/img/hamburger.png" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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

        .login-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
            box-sizing: border-box;
        }

        .login-container {
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
        }

        .login-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            border-radius: 20px 20px 0 0;
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-header h2 {
            font-weight: 700;
            color: #333;
            margin-bottom: 10px;
            font-size: 2rem;
        }

        .login-header p {
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

        .btn-login {
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            border: none;
            height: 50px;
            border-radius: 10px;
            font-weight: 600;
            letter-spacing: 1px;
            transition: all 0.3s;
            margin-top: 10px;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 107, 107, 0.4);
        }

        .login-footer {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }

        .login-footer a {
            color: var(--primary-color);
            text-decoration: none;
            transition: all 0.3s;
            font-weight: 500;
        }

        .login-footer a:hover {
            color: var(--secondary-color);
            text-decoration: underline;
        }

        /* Stickers de nourriture flottants */
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

        /* Positions des stickers */
        .sticker-1 {
            top: 10%;
            left: 5%;
            animation-delay: 0s;
        }

        .sticker-2 {
            top: 20%;
            right: 5%;
            animation-delay: 1s;
        }

        .sticker-3 {
            bottom: 15%;
            left: 8%;
            animation-delay: 2s;
        }

        .sticker-4 {
            bottom: 25%;
            right: 10%;
            animation-delay: 3s;
        }

        /* Style pour le mode mobile */
        @media (max-width: 768px) {
            .login-container {
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
    <!-- Stickers de nourriture flottants -->
    <img src="https://cdn-icons-png.flaticon.com/512/5787/5787100.png" class="food-sticker sticker-1" alt="Burger">
    <img src="https://cdn-icons-png.flaticon.com/512/5787/5787132.png" class="food-sticker sticker-2" alt="Pizza">
    <img src="https://cdn-icons-png.flaticon.com/512/5787/5787096.png" class="food-sticker sticker-3" alt="Taco">
    <img src="https://cdn-icons-png.flaticon.com/512/5787/5787120.png" class="food-sticker sticker-4" alt="Sushi">

    <div class="login-wrapper">
        <div class="login-container">
            <div class="login-header">
                <h2>Connexion</h2>
                <p>Connectez-vous pour accéder à votre compte</p>
            </div>

            <form id="loginForm">
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
                        <input type="password" class="form-control" id="password" placeholder="Votre mot de passe" required>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="remember">
                        <label class="form-check-label" for="remember">Se souvenir de moi</label>
                    </div>
                    <a href="#" class="text-decoration-none">Mot de passe oublié ?</a>
                </div>

                <button type="submit" class="btn btn-login w-100 mb-3">
                    <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                    Se connecter
                </button>

                <div class="login-footer">
                    <p>Vous n'avez pas de compte ? <a href="signup.php">S'inscrire</a></p>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Ajout de plus de stickers de nourriture dynamiquement
        document.addEventListener('DOMContentLoaded', function() {
            const foodIcons = [
                'https://cdn-icons-png.flaticon.com/512/5787/5787079.png', // Donut
                'https://cdn-icons-png.flaticon.com/512/5787/5787087.png', // Hotdog
                'https://cdn-icons-png.flaticon.com/512/5787/5787113.png', // Ramen
                'https://cdn-icons-png.flaticon.com/512/5787/5787141.png'  // Ice cream
            ];

            const container = document.body;
            
            // Ajoute 4 stickers supplémentaires
            for (let i = 0; i < 4; i++) {
                const sticker = document.createElement('img');
                sticker.src = foodIcons[i];
                sticker.className = 'food-sticker';
                sticker.alt = 'Food item';
                sticker.style.width = `${Math.random() * 50 + 50}px`;
                sticker.style.top = `${Math.random() * 80 + 10}%`;
                sticker.style.left = `${Math.random() * 80 + 10}%`;
                sticker.style.animationDelay = `${i}s`;
                sticker.style.opacity = '0.7';
                
                container.appendChild(sticker);
            }
        });

        // Remplacez le code de gestion du formulaire par celui-ci
        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const form = this;
            const submitBtn = form.querySelector('button[type="submit"]');
            const spinner = submitBtn.querySelector('.spinner-border');
            
            try {
                // Désactiver le bouton et afficher le spinner
                submitBtn.disabled = true;
                spinner.classList.remove('d-none');

                const formData = new FormData();
                formData.append('email', form.querySelector('#email').value);
                formData.append('password', form.querySelector('#password').value);
                formData.append('remember', form.querySelector('#remember').checked);

                const response = await fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (!data.success) {
                    throw new Error(data.message);
                }

                // Stockage du token
                if (data.token) {
                    localStorage.setItem('userToken', data.token);
                }

                // Animation de chargement avec message de bienvenue
                let timerInterval;
                await Swal.fire({
                    title: 'Connexion réussie !',
                    html: `<b>Bienvenue ${data.user.prenom} ${data.user.nom}</b><br>
                           <div class="progress mt-3">
                             <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                  role="progressbar" 
                                  style="width: 0%"></div>
                           </div>`,
                    timer: 2000,
                    timerProgressBar: true,
                    showConfirmButton: false,
                    didOpen: () => {
                        const progressBar = Swal.getHtmlContainer().querySelector('.progress-bar');
                        timerInterval = setInterval(() => {
                            const timeLeft = Swal.getTimerLeft();
                            const progress = ((2000 - timeLeft) / 2000) * 100;
                            progressBar.style.width = `${progress}%`;
                        }, 50);
                    },
                    willClose: () => {
                        clearInterval(timerInterval);
                    }
                });

                // Redirection
                window.location.href = '../index.html';

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
    </script>
</body>
</html>
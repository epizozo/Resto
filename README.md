# Projet Resto - Système de Gestion de Restaurant

## 📋 Description
Système complet de gestion de restaurant comprenant une interface client et un back-office administrateur.

## 🚀 Fonctionnalités

### Interface Client
- Page d'accueil interactive
- Catalogue des plats par catégories
- Système d'authentification utilisateur
- Profils personnalisés
- Formulaire de contact

### Panel Administrateur
- Gestion des utilisateurs
- Gestion du menu et des plats
- Suivi des messages
- Tableau de bord statistiques

## 🛠 Technologies Utilisées
- PHP 8.x
- MySQL
- HTML5/CSS3
- JavaScript
- Bootstrap 5
- PDO

## 📦 Installation

1. Cloner le repository :
```bash
git clone https://github.com/T0b0i7/Resto.git
```

2. Configurer la base de données :
- Importer le fichier `resto_db.sql`
- Modifier les paramètres de connexion dans `config/database.php`

3. Configurer le serveur web :
- Pointer le DocumentRoot vers le dossier `public`
- Activer le module rewrite d'Apache

## 🔒 Configuration Requise
- PHP >= 8.0
- MySQL >= 5.7
- Apache/Nginx
- Extension PHP PDO
- Extension PHP GD

## 👥 Rôles Utilisateurs
- **Admin** : Accès complet au système
- **Client** : Navigation catalogue, profil personnel

## 🔐 Sécurité
- Validation des données
- Protection contre les injections SQL
- Hashage des mots de passe
- Gestion des sessions sécurisée

## 📝 Structure du Projet
```
Resto/
├── admin/         # Interface administration
├── assets/        # Ressources statiques
├── config/        # Configuration
├── includes/      # Fichiers inclus
├── pages/         # Pages du site
└── public/        # Point d'entrée
```

## 🤝 Contribution
Les contributions sont les bienvenues ! Veuillez suivre ces étapes :
1. Forker le projet
2. Créer une branche (`git checkout -b feature/AjoutFonctionnalite`)
3. Commit (`git commit -m 'Ajout nouvelle fonctionnalité'`)
4. Push (`git push origin feature/AjoutFonctionnalite`)
5. Ouvrir une Pull Request

## 📄 Licence
Ce projet est sous licence MIT.

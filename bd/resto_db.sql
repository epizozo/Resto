-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 10, 2025 at 05:40 AM
-- Server version: 10.4.24-MariaDB
-- PHP Version: 8.1.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `resto_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `messages_contact`
--

CREATE TABLE `messages_contact` (
  `id_message` int(11) NOT NULL,
  `nom_complet` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sujet` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_envoi` timestamp NOT NULL DEFAULT current_timestamp(),
  `statut` enum('non_lu','lu','repondu','archivé') COLLATE utf8mb4_unicode_ci DEFAULT 'non_lu',
  `id_utilisateur` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `messages_contact`
--

INSERT INTO `messages_contact` (`id_message`, `nom_complet`, `email`, `sujet`, `message`, `date_envoi`, `statut`, `id_utilisateur`) VALUES
(1, 'fgfh', 'abattieucher@gmail.com', 'fghfhf', 'ffhghf', '2025-04-10 01:21:08', 'non_lu', NULL),
(2, 'vhghg', 'abattieucher@gmail.com', 'vn,n,', 'v,bv', '2025-04-10 01:21:24', 'non_lu', NULL),
(3, 'vhghg', 'abattieucher@gmail.com', 'vn,n,', 'v,bv', '2025-04-10 01:21:35', 'non_lu', NULL),
(4, 'vhghg', 'abattieucher@gmail.com', 'vn,n,', 'v,bv', '2025-04-10 01:48:41', 'non_lu', NULL),
(5, 'fgfh', 'abattieucher@gmail.com', 'fghfhf', 'ffhghf', '2025-04-10 01:48:49', 'non_lu', NULL),
(6, 'hffghj', 'stobirama808@gmail.com', 'ngv,g', 'gj', '2025-04-10 02:02:43', 'non_lu', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `plats`
--

CREATE TABLE `plats` (
  `id_plat` int(11) NOT NULL,
  `nom` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `prix` decimal(10,2) NOT NULL,
  `categorie` enum('entree','main','dessert','drink') COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `statut` enum('actif','inactif') COLLATE utf8mb4_unicode_ci DEFAULT 'actif',
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_modification` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `disponibilite` enum('disponible','pas disponible') COLLATE utf8mb4_unicode_ci DEFAULT 'disponible'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `plats`
--

INSERT INTO `plats` (`id_plat`, `nom`, `description`, `prix`, `categorie`, `image`, `statut`, `date_creation`, `date_modification`, `disponibilite`) VALUES
(1, 'cfcfb', 'cbcfgcfg', '656.00', 'drink', '67f731a47d1b8.jpg', 'actif', '2025-04-10 02:43:18', '2025-04-10 02:49:08', 'pas disponible');

-- --------------------------------------------------------

--
-- Table structure for table `sessions_utilisateur`
--

CREATE TABLE `sessions_utilisateur` (
  `id_session` int(11) NOT NULL,
  `id_utilisateur` int(11) NOT NULL,
  `jeton_session` varchar(255) NOT NULL,
  `adresse_ip` varchar(45) NOT NULL,
  `navigateur` text DEFAULT NULL,
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_expiration` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `sessions_utilisateur`
--

INSERT INTO `sessions_utilisateur` (`id_session`, `id_utilisateur`, `jeton_session`, `adresse_ip`, `navigateur`, `date_creation`, `date_expiration`) VALUES
(1, 1, '870755111fbd9808ae903744fe4c2e7a9d255e49bad0294cdf4f237fa89bad43', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', '2025-04-10 00:41:24', '2025-04-11 00:41:24'),
(2, 2, 'ce4d313093e58d3b694c5e9434a323acaa41ddc2f5a7b1b2b10e4866d66e917c', '::1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Mobile Safari/537.36', '2025-04-10 00:45:20', '2025-04-11 00:45:20'),
(6, 2, '9938cbf5b9283b2c64b7b4c355ba04995c0d9d5658f098f27f47a29088e5f392', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', '2025-04-10 01:06:15', '2025-04-11 01:06:15');

-- --------------------------------------------------------

--
-- Table structure for table `utilisateurs`
--

CREATE TABLE `utilisateurs` (
  `id_utilisateur` int(11) NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mot_de_passe_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nom` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `prenom` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telephone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `statut` enum('actif','inactif') COLLATE utf8mb4_unicode_ci DEFAULT 'actif',
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_mise_a_jour` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `derniere_connexion` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `token_expiry` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `utilisateurs`
--

INSERT INTO `utilisateurs` (`id_utilisateur`, `email`, `mot_de_passe_hash`, `nom`, `prenom`, `telephone`, `statut`, `date_creation`, `date_mise_a_jour`, `derniere_connexion`, `remember_token`, `token_expiry`) VALUES
(1, 'stobirama808@gmail.com', '$2y$10$TfC57DPqAGQxmRuKyKYGw.sA/3mDBjMery/ZQO8pbiqMldBd.BCue', 'ABATTI', 'Eucher', NULL, 'actif', '2025-04-10 00:41:24', '2025-04-10 00:41:24', NULL, NULL, NULL),
(2, 'admin@mail.com', '$2y$10$tuVziwRD05ze30A3/hKKGuz1HxTOaBfCmvh6Ave4oi0/FNu.6QO/e', 'ABATTI Eucher', 'Eucher', NULL, 'actif', '2025-04-10 00:45:20', '2025-04-10 02:19:29', '2025-04-10 01:06:15', 'f9341fdfdaeee0664284405c8595cc8583e9356dcbbaa4dc3d2f50d057f0d5f8', '2025-05-10 01:06:15');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `messages_contact`
--
ALTER TABLE `messages_contact`
  ADD PRIMARY KEY (`id_message`),
  ADD KEY `id_utilisateur` (`id_utilisateur`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_statut` (`statut`),
  ADD KEY `idx_date` (`date_envoi`);

--
-- Indexes for table `plats`
--
ALTER TABLE `plats`
  ADD PRIMARY KEY (`id_plat`),
  ADD KEY `idx_categorie` (`categorie`),
  ADD KEY `idx_statut` (`statut`);

--
-- Indexes for table `sessions_utilisateur`
--
ALTER TABLE `sessions_utilisateur`
  ADD PRIMARY KEY (`id_session`),
  ADD UNIQUE KEY `jeton_session` (`jeton_session`),
  ADD KEY `id_utilisateur` (`id_utilisateur`),
  ADD KEY `idx_jeton` (`jeton_session`),
  ADD KEY `idx_expiration` (`date_expiration`);

--
-- Indexes for table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  ADD PRIMARY KEY (`id_utilisateur`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_remember_token` (`remember_token`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `messages_contact`
--
ALTER TABLE `messages_contact`
  MODIFY `id_message` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `plats`
--
ALTER TABLE `plats`
  MODIFY `id_plat` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `sessions_utilisateur`
--
ALTER TABLE `sessions_utilisateur`
  MODIFY `id_session` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  MODIFY `id_utilisateur` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `messages_contact`
--
ALTER TABLE `messages_contact`
  ADD CONSTRAINT `messages_contact_ibfk_1` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateurs` (`id_utilisateur`) ON DELETE SET NULL;

--
-- Constraints for table `sessions_utilisateur`
--
ALTER TABLE `sessions_utilisateur`
  ADD CONSTRAINT `sessions_utilisateur_ibfk_1` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateurs` (`id_utilisateur`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

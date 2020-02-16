-- phpMyAdmin SQL Dump
-- version 4.7.9
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le :  Dim 16 fév. 2020 à 17:15
-- Version du serveur :  5.7.21
-- Version de PHP :  7.0.29

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données :  `trip_manager`
--

-- --------------------------------------------------------

--
-- Structure de la table `events`
--

DROP TABLE IF EXISTS `events`;
CREATE TABLE IF NOT EXISTS `events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `groupe` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `date` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `modified` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1=Active, 0=Block',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Déchargement des données de la table `events`
--

INSERT INTO `events` (`id`, `groupe`, `title`, `date`, `created`, `modified`, `status`) VALUES
(19, '13', 'Disponibilité de 5', '24-06-2018;2018-07-03+2018-07-14;2018-07-28+2018-07-14;2018-07-28+2018-07-14;2018-07-28', '24-06-2018', '04-07-2018', 1),
(24, '13', 'Disponibilité de 6', '2018-7-4;2018-07-06+2018-07-09;2018-07-10+2018-07-09;2018-07-19+2018-7-4;2018-07-06+2018-07-09;2018-07-10+2018-07-09;2018-07-19', '2018-07-04', '05-07-2018', 1),
(25, '15', 'Disponibilité de 5', '2018-9-3;2018-9-9', '2018-09-01', '2018-09-01', 1);

-- --------------------------------------------------------

--
-- Structure de la table `groupe`
--

DROP TABLE IF EXISTS `groupe`;
CREATE TABLE IF NOT EXISTS `groupe` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) NOT NULL,
  `membres` json NOT NULL,
  `infos` text NOT NULL,
  `events` json NOT NULL,
  `couleur` json NOT NULL,
  `activites` json NOT NULL,
  `creator` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=16 DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `groupe`
--

INSERT INTO `groupe` (`id`, `nom`, `membres`, `infos`, `events`, `couleur`, `activites`, `creator`) VALUES
(13, 'Doudou', '[\"5\", \"6\"]', '', '[null]', '{\"5\": \"#ff0080\", \"6\": \"#0000ff\"}', '{\"CUISINE\": {\"5\": \"3\", \"6\": \"2\", \"creator\": \"5\"}, \"VAISSELLE\": {\"5\": \"5\", \"6\": \"5\", \"creator\": \"6\"}}', 5),
(14, 'Albubu', '[\"5\"]', '', 'null', '{\"5\": \"#ff0080\"}', '{\"VAISSELLE\": {\"5\": \"5\", \"creator\": \"5\"}}', 5),
(15, 'Doudou2', '[\"5\", \"6\"]', '', '[null, \"25\"]', '{\"5\": \"#ff0080\", \"6\": \"#0000ff\"}', '{\"CUISINE\": {\"5\": \"3\", \"6\": \"2\", \"creator\": \"5\"}, \"VAISSELLE\": {\"5\": \"3\", \"6\": \"5\", \"creator\": \"6\"}}', 5);

-- --------------------------------------------------------

--
-- Structure de la table `personne`
--

DROP TABLE IF EXISTS `personne`;
CREATE TABLE IF NOT EXISTS `personne` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) NOT NULL,
  `prenom` varchar(255) NOT NULL,
  `mail` varchar(255) NOT NULL,
  `pass` varchar(255) NOT NULL,
  `photo` varchar(255) NOT NULL,
  `amis` json NOT NULL,
  `groupe` json NOT NULL,
  `notifs` json NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `personne`
--

INSERT INTO `personne` (`id`, `nom`, `prenom`, `mail`, `pass`, `photo`, `amis`, `groupe`, `notifs`) VALUES
(5, 'VAILLANT', 'MARGAUX', 'gago.vaillant@laposte.net', '$2y$10$Y.t9gLISkIVtEfCgJF12iuacu2mIIEYoldH2yRBfPW3n6ADhEzhVi', 'ressources/5.jpg', '[\"6\"]', '[\"13\", \"14\", \"15\"]', 'null'),
(6, 'CADORET', 'VINCENT', 'vincentcadoret@gmail.com', '$2y$10$Prm.I/cl/4R4nh8ITynnROQbgK8hRmVbOB2ErHEJx9l5N.Tk04AnW', 'ressources/6.png', '[\"5\"]', '[\"13\", \"15\"]', 'null');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

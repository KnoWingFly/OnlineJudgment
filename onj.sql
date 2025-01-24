-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jan 24, 2025 at 03:01 PM
-- Server version: 8.3.0
-- PHP Version: 8.2.18

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `onj`
--

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

DROP TABLE IF EXISTS `announcements`;
CREATE TABLE IF NOT EXISTS `announcements` (
  `id` int NOT NULL AUTO_INCREMENT,
  `time` int DEFAULT NULL,
  `msg` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chat`
--

DROP TABLE IF EXISTS `chat`;
CREATE TABLE IF NOT EXISTS `chat` (
  `id` int NOT NULL AUTO_INCREMENT,
  `userid` int DEFAULT NULL,
  `time` int DEFAULT NULL,
  `msg` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK2` (`userid`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `chat`
--

INSERT INTO `chat` (`id`, `userid`, `time`, `msg`) VALUES
(1, 1, 1736686791, 'sad'),
(2, 1, 1736687788, 'wikwok'),
(3, 2, 1736688046, 'test');

-- --------------------------------------------------------

--
-- Table structure for table `problems`
--

DROP TABLE IF EXISTS `problems`;
CREATE TABLE IF NOT EXISTS `problems` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `time_limit` double NOT NULL,
  `points` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `problems`
--

INSERT INTO `problems` (`id`, `title`, `time_limit`, `points`, `created_at`) VALUES
(1, 'test', 12, 12, '2025-01-24 14:16:38'),
(2, '213', 12, 123, '2025-01-24 14:22:18');

-- --------------------------------------------------------

--
-- Stand-in structure for view `scores`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `scores`;
CREATE TABLE IF NOT EXISTS `scores` (
`ranks` int
,`username` varchar(50)
,`score` int
);

-- --------------------------------------------------------

--
-- Table structure for table `submissions`
--

DROP TABLE IF EXISTS `submissions`;
CREATE TABLE IF NOT EXISTS `submissions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `userid` int DEFAULT NULL,
  `problemid` int DEFAULT NULL,
  `status` int DEFAULT NULL,
  `time` int DEFAULT NULL,
  `execution_time` float DEFAULT '0',
  `filename` varchar(255) DEFAULT NULL,
  `error_details` text,
  PRIMARY KEY (`id`),
  KEY `FK1` (`userid`)
) ENGINE=MyISAM AUTO_INCREMENT=209 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `submissions`
--

INSERT INTO `submissions` (`id`, `userid`, `problemid`, `status`, `time`, `execution_time`, `filename`, `error_details`) VALUES
(208, 1, 1, 0, 1737730688, 2.027, 'resto_dudut_2025-01-24_15-58-04.cpp', NULL),
(207, 1, 2, 5, 1737730656, 0, 'resto_dudut_2025-01-24_15-57-22.cpp', NULL),
(206, 1, 2, 5, 1737730631, 0, 'resto_dudut_2025-01-24_15-56-56.cpp', NULL),
(205, 1, 1, 2, 1737730605, 0.065, 'generator_2025-01-24_15-55-53.cpp', NULL),
(204, 1, 1, 127, 1737730438, 0, 'generator_2025-01-24_15-53-57.cpp', NULL),
(203, 1, 1, 127, 1737730389, 0, 'generator_2025-01-24_15-53-06.cpp', NULL),
(202, 1, 1, 127, 1737729920, 0, 'generator_2025-01-24_15-45-17.cpp', NULL),
(201, 1, 1, 127, 1737729077, 0, 'generator_2025-01-24_15-31-14.cpp', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

DROP TABLE IF EXISTS `system_settings`;
CREATE TABLE IF NOT EXISTS `system_settings` (
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text NOT NULL,
  `setting_type` varchar(20) NOT NULL,
  `description` text,
  PRIMARY KEY (`setting_key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`setting_key`, `setting_value`, `setting_type`, `description`) VALUES
('db_user', 'root', 'text', 'Database username'),
('db_name', 'onj', 'text', 'Database name'),
('points', '10,10,10,10,10', 'array', 'Problem point values'),
('start_time', '2025-01-13 00:00', 'datetime', 'Contest start time'),
('end_time', '2025-03-13 08:00', 'datetime', 'Contest end time'),
('leader_interval', '10000', 'number', 'Leaderboard refresh interval (ms)'),
('chat_interval', '5000', 'number', 'Chat refresh interval (ms)'),
('code_dir', 'code', 'text', 'Code submission directory'),
('problem_dir', 'problems', 'text', 'Problems directory');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL,
  `firstname` varchar(50) DEFAULT NULL,
  `lastname` varchar(50) DEFAULT NULL,
  `college` varchar(50) DEFAULT NULL,
  `ranks` int DEFAULT '0',
  `score` int DEFAULT '0',
  `is_admin` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `firstname`, `lastname`, `college`, `ranks`, `score`, `is_admin`) VALUES
(1, 'admin', 'onj', NULL, NULL, NULL, 1, 10, 1),
(2, 'KnowingFly', 'admin123', 'Fernando', 'Sunarto', 'UMN', 2, 0, 0),
(3, 'test123', 'admin123', 'test', '123', 'UMN', 3, 0, 0),
(4, 'Ucok', 'admin123', 'Uc', 'ok', 'UMN', 4, 0, 0);

-- --------------------------------------------------------

--
-- Structure for view `scores`
--
DROP TABLE IF EXISTS `scores`;

DROP VIEW IF EXISTS `scores`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `scores`  AS SELECT `u`.`ranks` AS `ranks`, `u`.`username` AS `username`, `u`.`score` AS `score` FROM (`users` `u` left join `submissions` on((`u`.`id` = `submissions`.`userid`))) WHERE (((`u`.`score` > 0) AND (`submissions`.`status` = 0) AND (`submissions`.`time` = (select max(`submissions`.`time`) from `submissions` where ((`submissions`.`status` = 0) AND (`submissions`.`userid` = `u`.`id`))))) OR ((`u`.`score` = 0) AND (((select count(0) from `submissions` where (`submissions`.`userid` = `u`.`id`)) = 0) OR (`submissions`.`time` = (select max(`submissions`.`time`) from `submissions` where (`submissions`.`userid` = `u`.`id`)))))) ORDER BY `u`.`score` DESC, `submissions`.`time` ASC, `u`.`username` ASC ;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

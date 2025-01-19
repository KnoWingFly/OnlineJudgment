-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jan 19, 2025 at 05:34 AM
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
  `time_limit` float NOT NULL,
  `memory_limit` int NOT NULL,
  `created_at` datetime NOT NULL,
  `points` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `problems`
--

INSERT INTO `problems` (`id`, `title`, `time_limit`, `memory_limit`, `created_at`, `points`) VALUES
(1, 'asd', 123, 123, '2025-01-17 19:55:44', 10),
(3, 'asd123', 12, 12, '2025-01-17 21:02:52', 10);

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
  PRIMARY KEY (`id`),
  KEY `FK1` (`userid`)
) ENGINE=MyISAM AUTO_INCREMENT=125 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `submissions`
--

INSERT INTO `submissions` (`id`, `userid`, `problemid`, `status`, `time`, `execution_time`, `filename`) VALUES
(121, 3, 1, 0, 1736942532, 1.057, 'jawaban1_2025-01-15_13-02-08.cpp'),
(124, 1, 1, 0, 1737118606, 0.015, 'addsum.pertanyaan_2025-01-17_13-56-45.cpp'),
(123, 2, 1, 0, 1736956299, 3.06, 'jawaban1.java');

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
('start_time', '2025-01-10 00:00', 'datetime', 'Contest start time'),
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
(2, 'KnowingFly', 'admin123', 'Fernando', 'Sunarto', 'UMN', 2, 10, 0),
(3, 'test123', 'admin123', 'test', '123', 'UMN', 3, 10, 0),
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

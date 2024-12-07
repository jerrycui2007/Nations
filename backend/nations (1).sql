-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Nov 24, 2024 at 05:19 PM
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
-- Database: `nations`
--

-- --------------------------------------------------------

--
-- Table structure for table `alliances`
--

DROP TABLE IF EXISTS `alliances`;
CREATE TABLE IF NOT EXISTS `alliances` (
  `alliance_id` int NOT NULL AUTO_INCREMENT,
  `leader_id` int NOT NULL,
  `flag_link` varchar(255) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text NOT NULL,
  `date_created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`alliance_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `alliances`
--

INSERT INTO `alliances` (`alliance_id`, `leader_id`, `flag_link`, `name`, `description`, `date_created`) VALUES
(1, 7, 'https://i.ibb.co/7p7wqnZ/Bastion.webp', 'The Last Bastion', 'The Last Bastion alliance', '2024-11-16 17:07:02');

-- --------------------------------------------------------

--
-- Table structure for table `alliance_join_requests`
--

DROP TABLE IF EXISTS `alliance_join_requests`;
CREATE TABLE IF NOT EXISTS `alliance_join_requests` (
  `user_id` int NOT NULL,
  `alliance_id` int NOT NULL,
  `requester_nation_name` varchar(255) NOT NULL,
  `date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `battles`
--

DROP TABLE IF EXISTS `battles`;
CREATE TABLE IF NOT EXISTS `battles` (
  `battle_id` int NOT NULL AUTO_INCREMENT,
  `mission_id` int NOT NULL DEFAULT '0',
  `is_multiplayer` tinyint(1) NOT NULL,
  `continent` varchar(255) NOT NULL,
  `battle_name` varchar(255) NOT NULL,
  `defender_name` varchar(255) NOT NULL,
  `attacker_name` varchar(255) NOT NULL,
  `defender_initial_strength` int DEFAULT NULL,
  `attacker_initial_strength` int NOT NULL,
  `defender_division_id` int NOT NULL,
  `attacker_division_id` int NOT NULL,
  `date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_over` tinyint(1) NOT NULL DEFAULT '0',
  `winner_name` varchar(255) NOT NULL,
  PRIMARY KEY (`battle_id`)
) ENGINE=MyISAM AUTO_INCREMENT=68 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `battles`
--

INSERT INTO `battles` (`battle_id`, `mission_id`, `is_multiplayer`, `continent`, `battle_name`, `defender_name`, `attacker_name`, `defender_initial_strength`, `attacker_initial_strength`, `defender_division_id`, `attacker_division_id`, `date`, `is_over`, `winner_name`) VALUES
(67, 30, 0, 'zaheria', 'Suppressing the Khev Minosk', 'Khev Minosk', 'Mission Skipper', 154, 33, 86, 43, '2024-11-24 12:17:17', 0, ''),
(66, 29, 0, 'zaheria', 'Suppressing the Khev Minosk', 'Khev Minosk', 'Mission Skipper', 121, 33, 85, 43, '2024-11-24 12:06:47', 1, 'Khev Minosk'),
(65, 28, 0, 'zaheria', 'Supply Raid', 'Bihadj Insurgents', 'Mission Skipper', 156, 22, 84, 43, '2024-11-24 11:59:36', 1, 'Bihadj Insurgents'),
(64, 27, 0, 'westberg', 'Homeland Offence', 'Oldenburg Defence', 'Mission Skipper', 108, 33, 83, 43, '2024-11-24 11:55:54', 1, 'Oldenburg Defence'),
(63, 26, 0, 'westberg', 'Foreign Affairs', 'Secret Service', 'Mission Skipper', 140, 33, 82, 43, '2024-11-24 11:50:56', 1, 'Secret Service'),
(62, 19, 0, 'zaheria', 'Supply Raid', 'Bihadj Insurgents', 'Mission Skipper', 144, 33, 81, 43, '2024-11-24 11:43:27', 1, 'Bihadj Insurgents'),
(61, 19, 0, 'zaheria', 'Suppressing the Khev Minosk', 'Khev Minosk', '76th Army Rangers', 154, 78, 80, 8, '2024-11-24 11:40:42', 1, '76th Army Rangers'),
(60, 25, 0, 'amarino', 'Jungle Fever', 'Followers of Black Horn', 'Mission Skipper', 196, 22, 79, 43, '2024-11-24 11:33:56', 1, 'Followers of Black Horn'),
(59, 25, 0, 'amarino', 'Jungle Fever', 'Followers of Black Horn', 'Mission Skipper', 168, 22, 78, 43, '2024-11-24 11:20:55', 1, 'Mission Skipper'),
(58, 19, 0, 'zaheria', 'Suppressing the Khev Minosk', 'Khev Minosk', 'Mission Skipper', 143, 22, 77, 43, '2024-11-24 11:13:08', 1, '');

-- --------------------------------------------------------

--
-- Table structure for table `battle_reports`
--

DROP TABLE IF EXISTS `battle_reports`;
CREATE TABLE IF NOT EXISTS `battle_reports` (
  `battle_report_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `battle_id` int NOT NULL,
  `date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `message` text NOT NULL,
  `visible` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`battle_report_id`)
) ENGINE=MyISAM AUTO_INCREMENT=51 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `battle_reports`
--

INSERT INTO `battle_reports` (`battle_report_id`, `user_id`, `battle_id`, `date`, `message`, `visible`) VALUES
(1, 7, 17, '2024-11-23 14:21:21', '<p class=\'unit-destroyed friendly\'>AK-47 Infantry (Oodlistan ISR Insurgents) was destroyed in combat!</p><p class=\'unit-destroyed friendly\'>AK-47 Infantry (Oodlistan ISR Insurgents) was destroyed in combat!</p><p class=\'unit-destroyed friendly\'>AK-47 Infantry (Oodlistan ISR Insurgents) was destroyed in combat!</p><p class=\'unit-destroyed friendly\'>AK-47 Infantry (Oodlistan ISR Insurgents) was destroyed in combat!</p><p class=\'unit-destroyed friendly\'>AK-47 Infantry (Oodlistan ISR Insurgents) was destroyed in combat!</p><p class=\'unit-destroyed friendly\'>AK-47 Infantry (Oodlistan ISR Insurgents) was destroyed in combat!</p><p class=\'unit-destroyed friendly\'>AK-47 Infantry (Oodlistan ISR Insurgents) was destroyed in combat!</p><p class=\'unit-destroyed friendly\'>AK-47 Infantry (Oodlistan ISR Insurgents) was destroyed in combat!</p><h3>Battle Conclusion</h3><p class=\'battle-result\'>Oodlistan ISR Insurgents retreated from battle due to overwhelming enemy strength!</p><h4>Experience Gained</h4><ul class=\'xp-gains\'><li> gained 22 experience points</li><li> gained 22 experience points</li></ul>', 1),
(2, 7, 18, '2024-11-23 14:31:02', '<p class=\'unit-destroyed friendly\'>AK-47 Infantry (Oodlistan ISR Insurgents) was destroyed in combat!</p><p class=\'unit-destroyed friendly\'>AK-47 Infantry (Oodlistan ISR Insurgents) was destroyed in combat!</p><p class=\'unit-destroyed enemy\'>Arctic Camoflauge Expert (76th Army Rangers) was destroyed in combat!</p><p class=\'unit-destroyed friendly\'>AK-47 Infantry (Oodlistan ISR Insurgents) was destroyed in combat!</p><p class=\'unit-destroyed friendly\'>AK-47 Infantry (Oodlistan ISR Insurgents) was destroyed in combat!</p><p class=\'unit-destroyed friendly\'>AK-47 Infantry (Oodlistan ISR Insurgents) was destroyed in combat!</p><p class=\'unit-destroyed friendly\'>AK-47 Infantry (Oodlistan ISR Insurgents) was destroyed in combat!</p><p class=\'unit-destroyed friendly\'>AK-47 Infantry (Oodlistan ISR Insurgents) was destroyed in combat!</p><p class=\'unit-destroyed friendly\'>AK-47 Infantry (Oodlistan ISR Insurgents) was destroyed in combat!</p>', 1),
(3, 7, 19, '2024-11-23 14:37:53', '<p class=\'unit-destroyed friendly\'>AK-47 Infantry (Oodlistan ISR Insurgents) was destroyed in combat!</p><p class=\'unit-destroyed friendly\'>AK-47 Infantry (Oodlistan ISR Insurgents) was destroyed in combat!</p><p class=\'unit-destroyed friendly\'>AK-47 Infantry (Oodlistan ISR Insurgents) was destroyed in combat!</p><p class=\'unit-destroyed friendly\'>AK-47 Infantry (Oodlistan ISR Insurgents) was destroyed in combat!</p><p class=\'unit-destroyed friendly\'>AK-47 Infantry (Oodlistan ISR Insurgents) was destroyed in combat!</p><p class=\'unit-destroyed friendly\'>AK-47 Infantry (Oodlistan ISR Insurgents) was destroyed in combat!</p><p class=\'unit-destroyed friendly\'>AK-47 Infantry (Oodlistan ISR Insurgents) was destroyed in combat!</p>', 0),
(4, 7, 20, '2024-11-23 14:42:20', '<p class=\'unit-destroyed friendly\'>AK-47 Infantry (Oodlistan ISR Insurgents) was destroyed in combat!</p><p class=\'unit-destroyed friendly\'>AK-47 Infantry (Oodlistan ISR Insurgents) was destroyed in combat!</p><p class=\'unit-destroyed friendly\'>AK-47 Infantry (Oodlistan ISR Insurgents) was destroyed in combat!</p><p class=\'unit-destroyed friendly\'>AK-47 Infantry (Oodlistan ISR Insurgents) was destroyed in combat!</p><p class=\'unit-destroyed friendly\'>AK-47 Infantry (Oodlistan ISR Insurgents) was destroyed in combat!</p><p class=\'unit-destroyed friendly\'>AK-47 Infantry (Oodlistan ISR Insurgents) was destroyed in combat!</p><p class=\'unit-destroyed friendly\'>AK-47 Infantry (Oodlistan ISR Insurgents) was destroyed in combat!</p><h3>Battle Conclusion</h3><p class=\'battle-result\'>Oodlistan ISR Insurgents retreated from battle due to overwhelming enemy strength!</p>', 1),
(5, 7, 22, '2024-11-23 16:45:12', '<h3>Riot Control</h3><p>A Westberg member of the United Nations is experiencing serious riot problems after a somewhat shady election. As a fellow UN member, it is our duty to help them.</p><p class=\'battle-start\'>76th Army Rangers has engaged Westberg Rioters!</p><p class=\'unit-destroyed friendly\'>Rioter (Westberg Rioters) was destroyed in combat!</p><p class=\'unit-destroyed friendly\'>Rioter (Westberg Rioters) was destroyed in combat!</p><p class=\'unit-destroyed friendly\'>Rioter (Westberg Rioters) was destroyed in combat!</p><p class=\'unit-destroyed friendly\'>Rioter (Westberg Rioters) was destroyed in combat!</p><p class=\'unit-destroyed friendly\'>Rioter (Westberg Rioters) was destroyed in combat!</p><p class=\'unit-destroyed friendly\'>Rioter (Westberg Rioters) was destroyed in combat!</p><p class=\'unit-destroyed friendly\'>Rioter (Westberg Rioters) was destroyed in combat!</p><p class=\'unit-destroyed friendly\'>Rioter (Westberg Rioters) was destroyed in combat!</p><p class=\'unit-destroyed friendly\'>Rioter (Westberg Rioters) was destroyed in combat!</p><p class=\'unit-destroyed friendly\'>Rioter (Westberg Rioters) was destroyed in combat!</p><p class=\'unit-destroyed friendly\'>Rioter (Westberg Rioters) was destroyed in combat!</p><h3>Battle Conclusion</h3><p class=\'battle-result\'>Westberg Rioters retreated from battle due to overwhelming enemy strength!</p>', 1),
(6, 7, 23, '2024-11-23 18:29:29', '<h3>Foreign Affairs</h3><p>Hey! Look over there, Mr. President! *Boom!* *Boom!* Take the cash and run, dammit!</p><p class=\'battle-start\'>76th Army Rangers has engaged Secret Service!</p><p class=\'unit-destroyed friendly\'>Secret Agent (Secret Service) was destroyed in combat!</p><p class=\'unit-destroyed friendly\'>Secret Agent (Secret Service) was destroyed in combat!</p><p class=\'unit-destroyed friendly\'>Secret Agent (Secret Service) was destroyed in combat!</p><p class=\'unit-destroyed friendly\'>Secret Agent (Secret Service) was destroyed in combat!</p><p class=\'unit-destroyed enemy\'>Arctic Camoflauge Expert (76th Army Rangers) was destroyed in combat!</p><p class=\'unit-destroyed friendly\'>Secret Agent (Secret Service) was destroyed in combat!</p><p class=\'unit-destroyed enemy\'>Arctic Camoflauge Expert (76th Army Rangers) was destroyed in combat!</p><p class=\'unit-destroyed enemy\'>Infantry (76th Army Rangers) was destroyed in combat!</p><p class=\'unit-destroyed friendly\'>Secret Agent (Secret Service) was destroyed in combat!</p><p class=\'unit-destroyed friendly\'>Secret Agent (Secret Service) was destroyed in combat!</p><p class=\'unit-destroyed friendly\'>Secret Agent (Secret Service) was destroyed in combat!</p><p class=\'unit-destroyed friendly\'>Secret Agent (Secret Service) was destroyed in combat!</p><p class=\'unit-destroyed friendly\'>Secret Agent (Secret Service) was destroyed in combat!</p><p class=\'unit-destroyed friendly\'>Secret Agent (Secret Service) was destroyed in combat!</p><h3>Battle Conclusion</h3><p class=\'battle-result\'>76th Army Rangers emerged victorious!</p>', 1),
(7, 7, 24, '2024-11-23 18:43:39', '<h3>Supply Raid</h3><p>Strategically attacking supply depots of Bihadj outposts can prove valuable.</p><p class=\'battle-start\'>76th Army Rangers has engaged Bihadj Insurgents!</p><p class=\'unit-destroyed friendly\'>AK-47 Infantry (Bihadj Insurgents) was destroyed in combat!</p><p class=\'unit-destroyed friendly\'>AK-47 Infantry (Bihadj Insurgents) was destroyed in combat!</p><p class=\'unit-destroyed friendly\'>RPG Infantry (Bihadj Insurgents) was destroyed in combat!</p><p class=\'unit-destroyed friendly\'>AK-47 Infantry (Bihadj Insurgents) was destroyed in combat!</p><p class=\'unit-destroyed friendly\'>RPG Infantry (Bihadj Insurgents) was destroyed in combat!</p><p class=\'unit-destroyed friendly\'>AK-47 Infantry (Bihadj Insurgents) was destroyed in combat!</p><p class=\'unit-destroyed friendly\'>AK-47 Infantry (Bihadj Insurgents) was destroyed in combat!</p><p class=\'unit-destroyed friendly\'>AK-47 Infantry (Bihadj Insurgents) was destroyed in combat!</p><p class=\'unit-destroyed friendly\'>RPG Infantry (Bihadj Insurgents) was destroyed in combat!</p><p class=\'unit-destroyed enemy\'>Rioter (76th Army Rangers) was destroyed in combat!</p><p class=\'unit-destroyed friendly\'>AK-47 Infantry (Bihadj Insurgents) was destroyed in combat!</p><p class=\'unit-destroyed friendly\'>AK-47 Infantry (Bihadj Insurgents) was destroyed in combat!</p><p class=\'unit-destroyed friendly\'>AK-47 Infantry (Bihadj Insurgents) was destroyed in combat!</p><p class=\'unit-destroyed friendly\'>AK-47 Infantry (Bihadj Insurgents) was destroyed in combat!</p><p class=\'unit-destroyed friendly\'>RPG Infantry (Bihadj Insurgents) was destroyed in combat!</p><p class=\'unit-destroyed friendly\'>Desert Fox Bodyguard (Bihadj Insurgents) was destroyed in combat!</p><h3>Battle Conclusion</h3><p class=\'battle-result\'>76th Army Rangers emerged victorious!</p>', 1),
(8, 7, 25, '2024-11-24 09:13:10', '<h3>Arctic Drill</h3><p>Every now and then, The United Nations host a military exercise in the northernmost regions of Tind. Even though the exercise is infamous for accidental deaths, we should join in to strengthen our relations with the Union.</p><p class=\'battle-start\'>Mission Skipper has engaged UN Peacekeepers!</p><h3>Battle Conclusion</h3><p class=\'battle-result\'>Mission Skipper retreated from battle due to overwhelming enemy strength!</p>', 1),
(9, 7, 26, '2024-11-24 09:18:20', '<h3>Supply Raid</h3><p>Strategically attacking supply depots of Bihadj outposts can prove valuable.</p><p class=\'battle-start\'>Mission Skipper has engaged Bihadj Insurgents!</p>', 0),
(10, 7, 27, '2024-11-24 09:20:11', '<h3>Suppressing the Khev Minosk</h3><p>We need to stop the advancements of the terror group Khev Minosk. Strike with force.</p><p class=\'battle-start\'>Mission Skipper has engaged Khev Minosk!</p>', 0),
(11, 7, 28, '2024-11-24 09:24:15', '<h3>Suppressing the Khev Minosk</h3><p>We need to stop the advancements of the terror group Khev Minosk. Strike with force.</p><p class=\'battle-start\'>Mission Skipper has engaged Khev Minosk!</p>', 0),
(12, 7, 29, '2024-11-24 09:32:17', '<h3>Suppressing the Khev Minosk</h3><p>We need to stop the advancements of the terror group Khev Minosk. Strike with force.</p><p class=\'battle-start\'>Mission Skipper has engaged Khev Minosk!</p><h3>Battle Conclusion</h3><p class=\'battle-result\'>Mission Skipper retreated from battle due to overwhelming enemy strength!</p>', 1),
(13, 7, 30, '2024-11-24 09:35:04', '<h3>Little Brother Wants Out</h3><p>Diplomatic negotiations over a small dependent region in San Sebastian had recently escalated into a armed conflict between state and regional rebels. As a fellow member of the United Nations, we are obliged to help.</p><p class=\'battle-start\'>Mission Skipper has engaged Tyrian Rebels!</p><p class=\'unit-destroyed enemy\'>Medic (Mission Skipper) was destroyed in combat!</p><h3>Battle Conclusion</h3><p class=\'battle-result\'>Mission Skipper retreated from battle due to overwhelming enemy strength!</p>', 1),
(14, 7, 31, '2024-11-24 09:39:55', '<h3>Jungle Fever</h3><p>The feared Followers of Black Horn is one of the reasons why southern Amarino is in such an unstable state. They are deadly when confronted in the jungle, but they have to be stopped.</p><p class=\'battle-start\'>Mission Skipper has engaged Followers of Black Horn!</p><h3>Battle Conclusion</h3><p class=\'battle-result\'>Mission Skipper retreated from battle due to overwhelming enemy strength!</p>', 1),
(15, 7, 32, '2024-11-24 09:40:31', '<h3>Supply Raid</h3><p>Strategically attacking supply depots of Bihadj outposts can prove valuable.</p><p class=\'battle-start\'>Mission Skipper has engaged Bihadj Insurgents!</p><h3>Battle Conclusion</h3><p class=\'battle-result\'>Mission Skipper retreated from battle due to overwhelming enemy strength!</p>', 1),
(16, 7, 33, '2024-11-24 09:41:19', '<h3>Supply Raid</h3><p>Strategically attacking supply depots of Bihadj outposts can prove valuable.</p><p class=\'battle-start\'>Mission Skipper has engaged Bihadj Insurgents!</p><p class=\'unit-destroyed enemy\'>Medic (Mission Skipper) was destroyed in combat!</p><h3>Battle Conclusion</h3><p class=\'battle-result\'>Mission Skipper retreated from battle due to overwhelming enemy strength!</p>', 1),
(17, 7, 34, '2024-11-24 09:44:33', '<h3>Jungle Fever</h3><p>The feared Followers of Black Horn is one of the reasons why southern Amarino is in such an unstable state. They are deadly when confronted in the jungle, but they have to be stopped.</p><p class=\'battle-start\'>Mission Skipper has engaged Followers of Black Horn!</p><p class=\'unit-destroyed enemy\'>Medic (Mission Skipper) was destroyed in combat!</p><h3>Battle Conclusion</h3><p class=\'battle-result\'>Mission Skipper retreated from battle due to overwhelming enemy strength!</p>', 1),
(18, 7, 35, '2024-11-24 09:46:51', '<h3>Supply Raid</h3><p>Strategically attacking supply depots of Bihadj outposts can prove valuable.</p><p class=\'battle-start\'>Mission Skipper has engaged Bihadj Insurgents!</p><p class=\'unit-destroyed enemy\'>Medic (Mission Skipper) was destroyed in combat!</p><p class=\'unit-destroyed enemy\'>Medic (Mission Skipper) was destroyed in combat!</p><h3>Battle Conclusion</h3><p class=\'battle-result\'>Mission Skipper retreated from battle due to overwhelming enemy strength!</p>', 1),
(19, 7, 36, '2024-11-24 09:49:26', '<h3>Supply Raid</h3><p>Strategically attacking supply depots of Bihadj outposts can prove valuable.</p><p class=\'battle-start\'>Mission Skipper has engaged Bihadj Insurgents!</p><p class=\'unit-destroyed enemy\'>Infantry (Mission Skipper) was destroyed in combat!</p><h3>Battle Conclusion</h3><p class=\'battle-result\'>Mission Skipper retreated from battle due to overwhelming enemy strength!</p>', 1),
(20, 7, 37, '2024-11-24 09:51:37', '<h3>Supply Raid</h3><p>Strategically attacking supply depots of Bihadj outposts can prove valuable.</p><p class=\'battle-start\'>Mission Skipper has engaged Bihadj Insurgents!</p><h3>Battle Conclusion</h3><p class=\'battle-result\'>Mission Skipper retreated from battle due to overwhelming enemy strength!</p>', 1),
(21, 7, 38, '2024-11-24 09:52:19', '<h3>Little Brother Wants Out</h3><p>Diplomatic negotiations over a small dependent region in San Sebastian had recently escalated into a armed conflict between state and regional rebels. As a fellow member of the United Nations, we are obliged to help.</p><p class=\'battle-start\'>Mission Skipper has engaged Tyrian Rebels!</p><p class=\'unit-destroyed enemy\'>Infantry (Mission Skipper) was destroyed in combat!</p><h3>Battle Conclusion</h3><p class=\'battle-result\'>Mission Skipper retreated from battle due to overwhelming enemy strength!</p>', 1),
(22, 7, 39, '2024-11-24 09:55:50', '<h3>Jungle Fever</h3><p>The feared Followers of Black Horn is one of the reasons why southern Amarino is in such an unstable state. They are deadly when confronted in the jungle, but they have to be stopped.</p><p class=\'battle-start\'>Mission Skipper has engaged Followers of Black Horn!</p><h3>Battle Conclusion</h3><p class=\'battle-result\'>Mission Skipper retreated from battle due to overwhelming enemy strength!</p>', 1),
(23, 7, 40, '2024-11-24 09:56:29', '<h3>Homeland Offence</h3><p>To be frank, we don&#039;t like Oldenburg. It&#039;s an irrelevant little nation on the continent of Westberg, and it&#039;s mere existence is annoying. We should attack them a bit.</p><p class=\'battle-start\'>Mission Skipper has engaged Oldenburg Defence!</p><p class=\'unit-destroyed enemy\'>Medic (Mission Skipper) was destroyed in combat!</p><p class=\'unit-destroyed friendly\'>National Guard (Oldenburg Defence) was destroyed in combat!</p><p class=\'unit-destroyed enemy\'>Infantry (Mission Skipper) was destroyed in combat!</p><h3>Battle Conclusion</h3><p class=\'battle-result\'>Mission Skipper retreated from battle due to overwhelming enemy strength!</p>', 1),
(24, 7, 41, '2024-11-24 10:00:02', '<h3>It&#039;s the Least We Can Do</h3><p>The Union of Nations is expecting us to contribute in the fight against the Bihadj Terrorists.</p><p class=\'battle-start\'>Mission Skipper has engaged Bihadj Insurgents!</p><h3>Battle Conclusion</h3><p class=\'battle-result\'>Mission Skipper retreated from battle due to overwhelming enemy strength!</p>', 1),
(25, 7, 42, '2024-11-24 10:00:56', '<h3>Foreign Affairs</h3><p>Hey! Look over there, Mr. President! *Boom!* *Boom!* Take the cash and run, dammit!</p><p class=\'battle-start\'>Mission Skipper has engaged Secret Service!</p><h3>Battle Conclusion</h3><p class=\'battle-result\'>Mission Skipper retreated from battle due to overwhelming enemy strength!</p>', 1),
(26, 7, 43, '2024-11-24 10:01:30', '<h3>Little Brother Wants Out</h3><p>Diplomatic negotiations over a small dependent region in San Sebastian had recently escalated into a armed conflict between state and regional rebels. As a fellow member of the United Nations, we are obliged to help.</p><p class=\'battle-start\'>Mission Skipper has engaged Tyrian Rebels!</p><p class=\'unit-destroyed enemy\'>Infantry (Mission Skipper) was destroyed in combat!</p><h3>Battle Conclusion</h3><p class=\'battle-result\'>Mission Skipper retreated from battle due to overwhelming enemy strength!</p>', 1),
(27, 7, 44, '2024-11-24 10:04:29', '<h3>Supply Raid</h3><p>Strategically attacking supply depots of Bihadj outposts can prove valuable.</p><p class=\'battle-start\'>Mission Skipper has engaged Bihadj Insurgents!</p><h3>Battle Conclusion</h3><p class=\'battle-result\'>Mission Skipper retreated from battle due to overwhelming enemy strength!</p>', 1),
(28, 7, 45, '2024-11-24 10:10:20', '<h3>It&#039;s the Least We Can Do</h3><p>The Union of Nations is expecting us to contribute in the fight against the Bihadj Terrorists.</p><p class=\'battle-start\'>Mission Skipper has engaged Bihadj Insurgents!</p><h3>Battle Conclusion</h3><p class=\'battle-result\'>Mission Skipper retreated from battle due to overwhelming enemy strength!</p>', 1),
(29, 7, 46, '2024-11-24 10:12:25', '<h3>Homeland Offence</h3><p>To be frank, we don&#039;t like Oldenburg. It&#039;s an irrelevant little nation on the continent of Westberg, and it&#039;s mere existence is annoying. We should attack them a bit.</p><p class=\'battle-start\'>Mission Skipper has engaged Oldenburg Defence!</p><h3>Battle Conclusion</h3><p class=\'battle-result\'>Mission Skipper retreated from battle due to overwhelming enemy strength!</p>', 1),
(30, 7, 47, '2024-11-24 10:15:33', '<h3>Homeland Offence</h3><p>To be frank, we don&#039;t like Oldenburg. It&#039;s an irrelevant little nation on the continent of Westberg, and it&#039;s mere existence is annoying. We should attack them a bit.</p><p class=\'battle-start\'>Mission Skipper has engaged Oldenburg Defence!</p><h3>Battle Conclusion</h3><p class=\'battle-result\'>Mission Skipper retreated from battle due to overwhelming enemy strength!</p>', 1),
(31, 7, 48, '2024-11-24 10:19:28', '<h3>Supply Raid</h3><p>Strategically attacking supply depots of Bihadj outposts can prove valuable.</p><p class=\'battle-start\'>Mission Skipper has engaged Bihadj Insurgents!</p><h3>Battle Conclusion</h3><p class=\'battle-result\'>Mission Skipper retreated from battle due to overwhelming enemy strength!</p>', 1),
(32, 7, 49, '2024-11-24 10:19:50', '<h3>Homeland Offence</h3><p>To be frank, we don&#039;t like Oldenburg. It&#039;s an irrelevant little nation on the continent of Westberg, and it&#039;s mere existence is annoying. We should attack them a bit.</p><p class=\'battle-start\'>Mission Skipper has engaged Oldenburg Defence!</p><h3>Battle Conclusion</h3><p class=\'battle-result\'>Mission Skipper retreated from battle due to overwhelming enemy strength!</p>', 1),
(33, 7, 50, '2024-11-24 10:22:47', '<h3>Foreign Affairs</h3><p>Hey! Look over there, Mr. President! *Boom!* *Boom!* Take the cash and run, dammit!</p><p class=\'battle-start\'>Mission Skipper has engaged Secret Service!</p>', 0),
(34, 7, 51, '2024-11-24 10:26:57', '<h3>Foreign Affairs</h3><p>Hey! Look over there, Mr. President! *Boom!* *Boom!* Take the cash and run, dammit!</p><p class=\'battle-start\'>Mission Skipper has engaged Secret Service!</p><h3>Battle Conclusion</h3><p class=\'battle-result\'>Mission Skipper emerged victorious!</p>', 1),
(35, 7, 52, '2024-11-24 10:27:39', '<h3>Homeland Offence</h3><p>To be frank, we don&#039;t like Oldenburg. It&#039;s an irrelevant little nation on the continent of Westberg, and it&#039;s mere existence is annoying. We should attack them a bit.</p><p class=\'battle-start\'>Mission Skipper has engaged Oldenburg Defence!</p><h3>Battle Conclusion</h3><p class=\'battle-result\'>Mission Skipper emerged victorious!</p>', 1),
(36, 7, 53, '2024-11-24 10:28:57', '<h3>Jungle Fever</h3><p>The feared Followers of Black Horn is one of the reasons why southern Amarino is in such an unstable state. They are deadly when confronted in the jungle, but they have to be stopped.</p><p class=\'battle-start\'>Mission Skipper has engaged Followers of Black Horn!</p><h3>Battle Conclusion</h3><p class=\'battle-result\'>Mission Skipper emerged victorious!</p>', 1),
(37, 7, 54, '2024-11-24 10:30:05', '<h3>Jungle Fever</h3><p>The feared Followers of Black Horn is one of the reasons why southern Amarino is in such an unstable state. They are deadly when confronted in the jungle, but they have to be stopped.</p><p class=\'battle-start\'>Mission Skipper has engaged Followers of Black Horn!</p><h3>Battle Conclusion</h3><p class=\'battle-result\'>Mission Skipper retreated from battle due to overwhelming enemy strength!</p>', 1),
(38, 7, 55, '2024-11-24 10:57:56', '<h3>Homeland Offence</h3><p>To be frank, we don&#039;t like Oldenburg. It&#039;s an irrelevant little nation on the continent of Westberg, and it&#039;s mere existence is annoying. We should attack them a bit.</p><p class=\'battle-start\'>Mission Skipper has engaged Oldenburg Defence!</p>', 0),
(39, 7, 56, '2024-11-24 11:04:42', '<h3>Homeland Offence</h3><p>To be frank, we don&#039;t like Oldenburg. It&#039;s an irrelevant little nation on the continent of Westberg, and it&#039;s mere existence is annoying. We should attack them a bit.</p><p class=\'battle-start\'>Mission Skipper has engaged Oldenburg Defence!</p>', 0),
(40, 7, 57, '2024-11-24 11:07:22', '<h3>Homeland Offence</h3><p>To be frank, we don&#039;t like Oldenburg. It&#039;s an irrelevant little nation on the continent of Westberg, and it&#039;s mere existence is annoying. We should attack them a bit.</p><p class=\'battle-start\'>Mission Skipper has engaged Oldenburg Defence!</p>', 0),
(41, 7, 58, '2024-11-24 11:13:08', '<h3>Suppressing the Khev Minosk</h3><p>We need to stop the advancements of the terror group Khev Minosk. Strike with force.</p><p class=\'battle-start\'>Mission Skipper has engaged Khev Minosk!</p><h3>Battle Conclusion</h3><p class=\'battle-result\'> emerged victorious!</p><h3>Battle Conclusion</h3><p class=\'battle-result\'> emerged victorious!</p><h3>Battle Conclusion</h3><p class=\'battle-result\'> emerged victorious!</p><h3>Battle Conclusion</h3><p class=\'battle-result\'> emerged victorious!</p><h3>Battle Conclusion</h3><p class=\'battle-result\'> emerged victorious!</p><h3>Battle Conclusion</h3><p class=\'battle-result\'> emerged victorious!</p>', 1),
(42, 7, 59, '2024-11-24 11:20:55', '<h3>Jungle Fever</h3><p>The feared Followers of Black Horn is one of the reasons why southern Amarino is in such an unstable state. They are deadly when confronted in the jungle, but they have to be stopped.</p><p class=\'battle-start\'>Mission Skipper has engaged Followers of Black Horn!</p><h3>Battle Conclusion</h3><p class=\'battle-result\'> emerged victorious!</p><h3>Battle Conclusion</h3><p class=\'battle-result\'> emerged victorious!</p><h3>Battle Conclusion</h3><p class=\'battle-result\'> emerged victorious!</p><h3>Battle Conclusion</h3><p class=\'battle-result\'> emerged victorious!</p><h3>Battle Conclusion</h3><p class=\'battle-result\'> emerged victorious!</p><h3>Battle Conclusion</h3><p class=\'battle-result\'> emerged victorious!</p><h3>Battle Conclusion</h3><p class=\'battle-result\'> emerged victorious!</p><h3>Battle Conclusion</h3><p class=\'battle-result\'> emerged victorious!</p>', 1),
(43, 7, 60, '2024-11-24 11:33:56', '<h3>Jungle Fever</h3><p>The feared Followers of Black Horn is one of the reasons why southern Amarino is in such an unstable state. They are deadly when confronted in the jungle, but they have to be stopped.</p><p class=\'battle-start\'>Mission Skipper has engaged Followers of Black Horn!</p><h3>Battle Conclusion</h3><p class=\'battle-result\'>Followers of Black Horn emerged victorious!</p>', 1),
(44, 7, 61, '2024-11-24 11:40:42', '<h3>Suppressing the Khev Minosk</h3><p>We need to stop the advancements of the terror group Khev Minosk. Strike with force.</p><p class=\'battle-start\'>76th Army Rangers has engaged Khev Minosk!</p><p class=\'unit-destroyed friendly\'>AK-47 Infantry (Khev Minosk) was destroyed in combat!</p><p class=\'unit-destroyed friendly\'>AK-47 Infantry (Khev Minosk) was destroyed in combat!</p><p class=\'unit-destroyed friendly\'>AK-47 Infantry (Khev Minosk) was destroyed in combat!</p><p class=\'unit-destroyed friendly\'>RPG Infantry (Khev Minosk) was destroyed in combat!</p><p class=\'unit-destroyed friendly\'>AK-47 Infantry (Khev Minosk) was destroyed in combat!</p><p class=\'unit-destroyed enemy\'>Medic (76th Army Rangers) was destroyed in combat!</p><p class=\'unit-destroyed friendly\'>AK-47 Infantry (Khev Minosk) was destroyed in combat!</p><p class=\'unit-destroyed friendly\'>RPG Infantry (Khev Minosk) was destroyed in combat!</p><p class=\'unit-destroyed friendly\'>AK-47 Infantry (Khev Minosk) was destroyed in combat!</p><p class=\'unit-destroyed friendly\'>AK-47 Infantry (Khev Minosk) was destroyed in combat!</p><p class=\'unit-destroyed friendly\'>RPG Infantry (Khev Minosk) was destroyed in combat!</p><p class=\'unit-destroyed friendly\'>AK-47 Infantry (Khev Minosk) was destroyed in combat!</p><p class=\'unit-destroyed friendly\'>AK-47 Infantry (Khev Minosk) was destroyed in combat!</p><p class=\'unit-destroyed friendly\'>AK-47 Infantry (Khev Minosk) was destroyed in combat!</p><h3>Battle Conclusion</h3><p class=\'battle-result\'>76th Army Rangers emerged victorious!</p>', 1),
(45, 7, 62, '2024-11-24 11:43:27', '<h3>Supply Raid</h3><p>Strategically attacking supply depots of Bihadj outposts can prove valuable.</p><p class=\'battle-start\'>Mission Skipper has engaged Bihadj Insurgents!</p><p class=\'unit-destroyed enemy\'>Infantry (Mission Skipper) was destroyed in combat!</p><h3>Battle Conclusion</h3><p class=\'battle-result\'>Mission Skipper retreated from battle due to overwhelming enemy strength!</p>', 1),
(46, 7, 63, '2024-11-24 11:50:56', '<h3>Foreign Affairs</h3><p>Hey! Look over there, Mr. President! *Boom!* *Boom!* Take the cash and run, dammit!</p><p class=\'battle-start\'>Mission Skipper has engaged Secret Service!</p><p class=\'unit-destroyed enemy\'>Infantry (Mission Skipper) was destroyed in combat!</p><h3>Battle Conclusion</h3><p class=\'battle-result\'>Mission Skipper retreated from battle due to overwhelming enemy strength!</p>', 1),
(47, 7, 64, '2024-11-24 11:55:54', '<h3>Homeland Offence</h3><p>To be frank, we don&#039;t like Oldenburg. It&#039;s an irrelevant little nation on the continent of Westberg, and it&#039;s mere existence is annoying. We should attack them a bit.</p><p class=\'battle-start\'>Mission Skipper has engaged Oldenburg Defence!</p><p class=\'unit-destroyed enemy\'>Infantry (Mission Skipper) was destroyed in combat!</p><h3>Battle Conclusion</h3><p class=\'battle-result\'>Oldenburg Defence emerged victorious!</p>', 1),
(48, 7, 65, '2024-11-24 11:59:36', '<h3>Supply Raid</h3><p>Strategically attacking supply depots of Bihadj outposts can prove valuable.</p><p class=\'battle-start\'>Mission Skipper has engaged Bihadj Insurgents!</p><h3>Battle Conclusion</h3><p class=\'battle-result\'>Bihadj Insurgents emerged victorious!</p>', 1),
(49, 7, 66, '2024-11-24 12:06:47', '<h3>Suppressing the Khev Minosk</h3><p>We need to stop the advancements of the terror group Khev Minosk. Strike with force.</p><p class=\'battle-start\'>Mission Skipper has engaged Khev Minosk!</p><p class=\'unit-destroyed enemy\'>Infantry (Mission Skipper) was destroyed in combat!</p><h3>Battle Conclusion</h3><p class=\'battle-result\'>Khev Minosk emerged victorious!</p>', 1),
(50, 7, 67, '2024-11-24 12:17:17', '<h3>Suppressing the Khev Minosk</h3><p>We need to stop the advancements of the terror group Khev Minosk. Strike with force.</p><p class=\'battle-start\'>Mission Skipper has engaged Khev Minosk!</p><p class=\'unit-destroyed enemy\'>Infantry (Mission Skipper) was destroyed in combat!</p>', 0);

-- --------------------------------------------------------

--
-- Table structure for table `buffs`
--

DROP TABLE IF EXISTS `buffs`;
CREATE TABLE IF NOT EXISTS `buffs` (
  `buff_id` int NOT NULL AUTO_INCREMENT,
  `unit_id` int NOT NULL,
  `description` text NOT NULL,
  `buff_type` varchar(255) NOT NULL,
  `value` decimal(10,2) NOT NULL,
  `target` varchar(255) NOT NULL,
  PRIMARY KEY (`buff_id`)
) ENGINE=MyISAM AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `buffs`
--

INSERT INTO `buffs` (`buff_id`, `unit_id`, `description`, `buff_type`, `value`, `target`) VALUES
(5, 10, 'Reduces damage to friendly infantry units by 25%', 'FriendlyDamageReductionMultiplier', 0.75, 'Infantry'),
(7, 12, 'Reduces damage to friendly infantry units by 25%', 'FriendlyDamageReductionMultiplier', 0.75, 'Infantry'),
(9, 72, '1.33x all combat stats in Zaheria', 'AllStatsMultiplier', 1.34, 'zaheria'),
(10, 73, '1.33x all combat stats in Zaheria', 'AllStatsMultiplier', 1.34, 'zaheria');

-- --------------------------------------------------------

--
-- Table structure for table `buildings`
--

DROP TABLE IF EXISTS `buildings`;
CREATE TABLE IF NOT EXISTS `buildings` (
  `id` int NOT NULL,
  `geologist_building` int NOT NULL DEFAULT '0',
  `zoologist_building` int NOT NULL DEFAULT '0',
  `herbalist_building` int NOT NULL DEFAULT '0',
  `marine_biologist_building` int NOT NULL DEFAULT '0',
  `barracks` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `buildings`
--

INSERT INTO `buildings` (`id`, `geologist_building`, `zoologist_building`, `herbalist_building`, `marine_biologist_building`, `barracks`) VALUES
(7, 2, 1, 1, 1, 1),
(9, 0, 0, 0, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `building_queue`
--

DROP TABLE IF EXISTS `building_queue`;
CREATE TABLE IF NOT EXISTS `building_queue` (
  `queue_position` int NOT NULL AUTO_INCREMENT,
  `id` int NOT NULL,
  `building_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `level` int NOT NULL,
  `minutes_left` int NOT NULL,
  PRIMARY KEY (`queue_position`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `combat_reports`
--

DROP TABLE IF EXISTS `combat_reports`;
CREATE TABLE IF NOT EXISTS `combat_reports` (
  `combat_report_id` int NOT NULL AUTO_INCREMENT,
  `battle_id` int NOT NULL,
  `time` datetime NOT NULL,
  `message` text NOT NULL,
  `attacker_unit_id` int NOT NULL,
  `defender_unit_id` int NOT NULL,
  `combat_roll` int NOT NULL,
  `maneuver_modifier` int NOT NULL,
  `damage` int NOT NULL,
  PRIMARY KEY (`combat_report_id`)
) ENGINE=MyISAM AUTO_INCREMENT=773 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `combat_reports`
--

INSERT INTO `combat_reports` (`combat_report_id`, `battle_id`, `time`, `message`, `attacker_unit_id`, `defender_unit_id`, `combat_roll`, `maneuver_modifier`, `damage`) VALUES
(772, 67, '2024-11-24 12:17:33', 'AK-47 Infantry (Khev Minosk) fired at Infantry (Mission Skipper), dealing 29 damage. Infantry (Mission Skipper) was destroyed!', 757, 735, 43, 0, 29),
(770, 67, '2024-11-24 12:17:24', 'AK-47 Infantry (Khev Minosk) exchanged fire with Infantry (Mission Skipper), causing 23 damage.', 754, 735, 11, 0, 23),
(771, 67, '2024-11-24 12:17:29', 'AK-47 Infantry (Khev Minosk) exchanged fire with Infantry (Mission Skipper), causing 25 damage.', 751, 736, 37, 0, 25),
(769, 66, '2024-11-24 12:14:03', 'AK-47 Infantry (Khev Minosk) fired at Infantry (Mission Skipper), dealing 29 damage.', 741, 732, 32, 0, 29),
(766, 66, '2024-11-24 12:13:50', 'Infantry (Mission Skipper) fired at AK-47 Infantry (Khev Minosk), dealing 26 damage.', 732, 742, 40, 0, 26),
(767, 66, '2024-11-24 12:13:54', 'Infantry (Mission Skipper) engaged RPG Infantry (Khev Minosk) in combat, inflicting 23 damage.', 732, 747, 40, 0, 23),
(768, 66, '2024-11-24 12:13:59', 'RPG Infantry (Khev Minosk) exchanged fire with Infantry (Mission Skipper), causing 28 damage.', 746, 731, 28, 0, 28),
(764, 66, '2024-11-24 12:13:40', 'RPG Infantry (Khev Minosk) exchanged fire with Infantry (Mission Skipper), causing 24 damage.', 747, 733, 47, 0, 24),
(765, 66, '2024-11-24 12:13:44', 'AK-47 Infantry (Khev Minosk) exchanged fire with Infantry (Mission Skipper), causing 28 damage. Infantry (Mission Skipper) was destroyed!', 744, 733, 35, 0, 28),
(763, 66, '2024-11-24 12:13:34', 'AK-47 Infantry (Khev Minosk) made glancing contact with Infantry (Mission Skipper), dealing 12 damage.', 739, 733, 5, 0, 12),
(762, 65, '2024-11-24 11:59:50', 'AK-47 Infantry (Bihadj Insurgents) engaged Infantry (Mission Skipper) in combat, inflicting 28 damage.', 721, 695, 14, 0, 28),
(761, 64, '2024-11-24 11:57:22', 'Infantry (Mission Skipper) exchanged fire with National Guard (Oldenburg Defence), causing 29 damage.', 693, 708, 49, 1, 29),
(759, 64, '2024-11-24 11:57:16', 'National Guard (Oldenburg Defence) fired at Infantry (Mission Skipper), dealing 16 damage.', 707, 695, 73, -1, 16),
(760, 64, '2024-11-24 11:57:19', 'National Guard (Oldenburg Defence) attacked Infantry (Mission Skipper), dealing 15 damage.', 710, 693, 78, -1, 15),
(758, 64, '2024-11-24 11:57:11', 'Infantry (Mission Skipper) exchanged fire with National Guard (Oldenburg Defence), causing 28 damage.', 693, 715, 15, 1, 28),
(757, 64, '2024-11-24 11:57:05', 'National Guard (Oldenburg Defence) attacked Infantry (Mission Skipper), dealing 14 damage. Infantry (Mission Skipper) was destroyed!', 716, 694, 65, -1, 14),
(756, 64, '2024-11-24 11:57:00', 'Infantry (Mission Skipper) fired at National Guard (Oldenburg Defence), dealing 27 damage.', 693, 707, 36, 1, 27),
(755, 64, '2024-11-24 11:56:55', 'Infantry (Mission Skipper) barely grazed National Guard (Oldenburg Defence), causing 12 damage.', 694, 707, 7, 1, 12),
(751, 64, '2024-11-24 11:56:37', 'National Guard (Oldenburg Defence) engaged Infantry (Mission Skipper) in combat, inflicting 14 damage.', 709, 695, 86, -1, 14),
(752, 64, '2024-11-24 11:56:40', 'National Guard (Oldenburg Defence) exchanged fire with Infantry (Mission Skipper), causing 13 damage.', 707, 693, 89, -1, 13),
(753, 64, '2024-11-24 11:56:47', 'Medic (Oldenburg Defence) attacked Infantry (Mission Skipper), dealing 2 damage.', 706, 694, 14, 0, 2),
(754, 64, '2024-11-24 11:56:50', 'National Guard (Oldenburg Defence) exchanged fire with Infantry (Mission Skipper), causing 12 damage.', 713, 693, 73, -1, 12),
(750, 64, '2024-11-24 11:56:33', 'Infantry (Mission Skipper) exchanged fire with National Guard (Oldenburg Defence), causing 25 damage.', 695, 710, 27, 1, 25),
(747, 64, '2024-11-24 11:56:20', 'National Guard (Oldenburg Defence) exchanged fire with Infantry (Mission Skipper), causing 11 damage.', 708, 694, 81, -1, 11),
(748, 64, '2024-11-24 11:56:24', 'Medic (Oldenburg Defence) engaged Infantry (Mission Skipper) in combat, inflicting 1 damage.', 706, 693, 41, 0, 1),
(749, 64, '2024-11-24 11:56:28', 'Infantry (Mission Skipper) exchanged fire with Medic (Oldenburg Defence), causing 26 damage.', 693, 706, 25, 0, 26),
(746, 64, '2024-11-24 11:56:17', 'Infantry (Mission Skipper) fired at National Guard (Oldenburg Defence) and missed, but shrapnel did 12 damage.', 694, 712, 5, 1, 12),
(745, 64, '2024-11-24 11:56:13', 'National Guard (Oldenburg Defence) engaged Infantry (Mission Skipper) in combat, inflicting 18 damage.', 715, 694, 43, -1, 18),
(738, 62, '2024-11-24 11:44:41', 'Mission Skipper has retreated from battle!', 0, 0, 0, 0, 0),
(739, 63, '2024-11-24 11:51:04', 'Secret Agent (Secret Service) coordinated an attack on Infantry (Mission Skipper), causing 34 damage.', 698, 679, 84, 1, 34),
(740, 63, '2024-11-24 11:51:08', 'Secret Agent (Secret Service) executed a perfect ambushed on Infantry (Mission Skipper), dealing 49 damage.', 696, 693, 97, 1, 49),
(741, 63, '2024-11-24 11:51:13', 'Secret Agent (Secret Service) outmaneuvered Infantry (Mission Skipper), dealing 37 damage. Infantry (Mission Skipper) was destroyed!', 701, 679, 55, 1, 37),
(742, 63, '2024-11-24 11:51:19', 'Mission Skipper has retreated from battle!', 0, 0, 0, 0, 0),
(743, 64, '2024-11-24 11:56:04', 'National Guard (Oldenburg Defence) exchanged fire with Infantry (Mission Skipper), causing 12 damage.', 712, 694, 46, -1, 12),
(744, 64, '2024-11-24 11:56:09', 'Infantry (Mission Skipper) engaged National Guard (Oldenburg Defence) in combat, inflicting 27 damage.', 694, 716, 31, 1, 27),
(736, 62, '2024-11-24 11:44:32', 'RPG Infantry (Bihadj Insurgents) attacked Infantry (Mission Skipper), dealing 26 damage.', 691, 679, 38, 0, 26),
(737, 62, '2024-11-24 11:44:36', 'RPG Infantry (Bihadj Insurgents) attacked Infantry (Mission Skipper), dealing 24 damage.', 691, 677, 18, 0, 24),
(735, 62, '2024-11-24 11:43:46', 'AK-47 Infantry (Bihadj Insurgents) engaged Infantry (Mission Skipper) in combat, inflicting 25 damage. Infantry (Mission Skipper) was destroyed!', 681, 678, 15, 0, 25),
(733, 62, '2024-11-24 11:43:36', 'AK-47 Infantry (Bihadj Insurgents) engaged Infantry (Mission Skipper) in combat, inflicting 21 damage.', 685, 678, 19, 0, 21),
(734, 62, '2024-11-24 11:43:42', 'AK-47 Infantry (Bihadj Insurgents) exchanged fire with Infantry (Mission Skipper), causing 23 damage.', 680, 678, 43, 0, 23),
(730, 61, '2024-11-24 11:42:38', 'Desert Fox Bodyguard (76th Army Rangers) attacked AK-47 Infantry (Khev Minosk), dealing 139 damage. AK-47 Infantry (Khev Minosk) was destroyed!', 72, 673, 22, 2, 139),
(731, 61, '2024-11-24 11:42:39', 'Medic (76th Army Rangers) exchanged fire with AK-47 Infantry (Khev Minosk), causing 9 damage.', 10, 672, 9, 4, 9),
(732, 61, '2024-11-24 11:42:40', 'Desert Fox Bodyguard (76th Army Rangers) found a weak spot in AK-47 Infantry\'s (Khev Minosk) formation and struck hard for 130 damage. AK-47 Infantry (Khev Minosk) was destroyed!', 73, 672, 90, 4, 130),
(728, 61, '2024-11-24 11:42:36', 'Desert Fox Bodyguard (76th Army Rangers) exchanged fire with RPG Infantry (Khev Minosk), causing 87 damage. RPG Infantry (Khev Minosk) was destroyed!', 73, 674, 7, 4, 87),
(729, 61, '2024-11-24 11:42:37', 'Desert Fox Bodyguard (76th Army Rangers) exchanged fire with AK-47 Infantry (Khev Minosk), causing 135 damage. AK-47 Infantry (Khev Minosk) was destroyed!', 72, 668, 79, 2, 135),
(727, 61, '2024-11-24 11:42:35', 'A stray shot from AK-47 Infantry (Khev Minosk) caught Desert Fox Bodyguard (76th Army Rangers), doing 1 damage.', 673, 73, 13, -4, 1),
(726, 61, '2024-11-24 11:42:28', 'AK-47 Infantry (Khev Minosk) exchanged fire with Desert Fox Bodyguard (76th Army Rangers), causing 1 damage.', 668, 73, 28, -4, 1),
(724, 61, '2024-11-24 11:42:27', 'Desert Fox Bodyguard (76th Army Rangers) executed a perfect ambush on AK-47 Infantry (Khev Minosk), inflicting 129 damage. AK-47 Infantry (Khev Minosk) was destroyed!', 73, 664, 86, 4, 129),
(725, 61, '2024-11-24 11:42:28', 'In a brilliant tactical move, Desert Fox Bodyguard (76th Army Rangers) outmaneuvered AK-47 Infantry (Khev Minosk) and dealt 133 damage. AK-47 Infantry (Khev Minosk) was destroyed!', 73, 665, 93, 4, 133),
(719, 61, '2024-11-24 11:42:24', 'AK-47 Infantry (Khev Minosk) attacked Medic (76th Army Rangers), dealing 3 damage.', 672, 10, 31, -4, 3),
(720, 61, '2024-11-24 11:42:25', 'AK-47 Infantry (Khev Minosk) engaged Desert Fox Bodyguard (76th Army Rangers) in combat, inflicting 1 damage.', 664, 72, 82, -2, 1),
(721, 61, '2024-11-24 11:42:25', 'Desert Fox Bodyguard (76th Army Rangers) fired at RPG Infantry (Khev Minosk), dealing 137 damage. RPG Infantry (Khev Minosk) was destroyed!', 72, 675, 84, 2, 137),
(723, 61, '2024-11-24 11:42:26', 'A stray shot from Medic (76th Army Rangers) caught AK-47 Infantry (Khev Minosk), doing 1 damage.', 10, 664, 6, 4, 1),
(722, 61, '2024-11-24 11:42:26', 'AK-47 Infantry (Khev Minosk) attacked Desert Fox Bodyguard (76th Army Rangers), dealing 1 damage.', 668, 72, 20, -2, 1),
(718, 61, '2024-11-24 11:42:20', 'AK-47 Infantry (Khev Minosk) attacked Desert Fox Bodyguard (76th Army Rangers), dealing 1 damage.', 670, 72, 65, -2, 1),
(717, 61, '2024-11-24 11:42:19', 'A stray shot from AK-47 Infantry (Khev Minosk) caught Desert Fox Bodyguard (76th Army Rangers), doing 1 damage.', 673, 72, 8, -2, 1),
(715, 61, '2024-11-24 11:42:18', 'AK-47 Infantry (Khev Minosk) engaged Desert Fox Bodyguard (76th Army Rangers) in combat, inflicting 1 damage.', 665, 72, 90, -2, 1),
(716, 61, '2024-11-24 11:42:18', 'AK-47 Infantry (Khev Minosk) fired at Medic (76th Army Rangers), dealing 1 damage.', 673, 10, 81, -4, 1),
(713, 61, '2024-11-24 11:42:11', 'AK-47 Infantry (Khev Minosk) attacked Medic (76th Army Rangers), dealing 15 damage. Medic (76th Army Rangers) was destroyed!', 672, 12, 34, 0, 15),
(714, 61, '2024-11-24 11:42:17', 'Desert Fox Bodyguard (76th Army Rangers) engaged AK-47 Infantry (Khev Minosk) in combat, inflicting 88 damage. AK-47 Infantry (Khev Minosk) was destroyed!', 73, 663, 44, 4, 88),
(712, 61, '2024-11-24 11:42:10', 'RPG Infantry (Khev Minosk) exchanged fire with Medic (76th Army Rangers), causing 14 damage.', 674, 12, 57, 0, 14),
(711, 61, '2024-11-24 11:42:10', 'Medic (76th Army Rangers) attacked RPG Infantry (Khev Minosk), dealing 7 damage.', 12, 675, 67, 0, 7),
(710, 61, '2024-11-24 11:42:09', 'Medic (76th Army Rangers) attacked AK-47 Infantry (Khev Minosk), dealing 4 damage.', 10, 663, 79, 4, 4),
(709, 61, '2024-11-24 11:42:09', 'Desert Fox Bodyguard (76th Army Rangers) exchanged fire with AK-47 Infantry (Khev Minosk), causing 81 damage. AK-47 Infantry (Khev Minosk) was destroyed!', 73, 667, 16, 4, 81),
(708, 61, '2024-11-24 11:42:08', 'A stray shot from RPG Infantry (Khev Minosk) caught Desert Fox Bodyguard (76th Army Rangers), doing 1 damage.', 675, 72, 4, -2, 1),
(707, 61, '2024-11-24 11:42:07', 'AK-47 Infantry (Khev Minosk) exchanged fire with Medic (76th Army Rangers), causing 12 damage.', 670, 12, 49, 0, 12),
(706, 61, '2024-11-24 11:42:06', 'Desert Fox Bodyguard (76th Army Rangers) engaged RPG Infantry (Khev Minosk) in combat, inflicting 81 damage. RPG Infantry (Khev Minosk) was destroyed!', 73, 676, 81, 4, 81),
(705, 61, '2024-11-24 11:42:06', 'Medic (76th Army Rangers) fired at AK-47 Infantry (Khev Minosk), dealing 7 damage.', 10, 672, 18, 4, 7),
(704, 61, '2024-11-24 11:41:56', 'RPG Infantry (Khev Minosk) exchanged fire with Desert Fox Bodyguard (76th Army Rangers), causing 1 damage.', 675, 73, 20, -4, 1),
(703, 61, '2024-11-24 11:41:55', 'AK-47 Infantry (Khev Minosk) engaged Medic (76th Army Rangers) in combat, inflicting 13 damage.', 672, 12, 46, 0, 13),
(702, 61, '2024-11-24 11:41:54', 'A stray shot from RPG Infantry (Khev Minosk) caught Medic (76th Army Rangers), doing 1 damage.', 674, 10, 10, -4, 1),
(701, 61, '2024-11-24 11:41:53', 'Medic (76th Army Rangers) found a weak spot in RPG Infantry\'s (Khev Minosk) formation and struck hard for 13 damage.', 12, 675, 98, 0, 13),
(700, 61, '2024-11-24 11:41:52', 'AK-47 Infantry (Khev Minosk) found a weak spot in Medic\'s (76th Army Rangers) formation and struck hard for 6 damage.', 668, 10, 97, -4, 6),
(699, 61, '2024-11-24 11:41:52', 'AK-47 Infantry (Khev Minosk) engaged Desert Fox Bodyguard (76th Army Rangers) in combat, inflicting 1 damage.', 665, 72, 48, -2, 1),
(698, 61, '2024-11-24 11:41:51', 'RPG Infantry (Khev Minosk) fired at Desert Fox Bodyguard (76th Army Rangers), dealing 1 damage.', 676, 73, 74, -4, 1),
(697, 61, '2024-11-24 11:41:50', 'AK-47 Infantry (Khev Minosk) exchanged fire with Desert Fox Bodyguard (76th Army Rangers), causing 1 damage.', 663, 73, 41, -4, 1),
(696, 61, '2024-11-24 11:41:49', 'Medic (76th Army Rangers) fired at AK-47 Infantry (Khev Minosk), dealing 5 damage.', 10, 664, 85, 4, 5),
(695, 61, '2024-11-24 11:41:49', 'Desert Fox Bodyguard (76th Army Rangers) engaged AK-47 Infantry (Khev Minosk) in combat, inflicting 89 damage. AK-47 Infantry (Khev Minosk) was destroyed!', 73, 669, 75, 4, 89),
(694, 61, '2024-11-24 11:41:42', 'AK-47 Infantry (Khev Minosk) exchanged fire with Medic (76th Army Rangers), causing 3 damage.', 672, 10, 17, -4, 3),
(693, 61, '2024-11-24 11:41:39', 'AK-47 Infantry (Khev Minosk) engaged Medic (76th Army Rangers) in combat, inflicting 1 damage.', 672, 10, 42, -4, 1),
(692, 61, '2024-11-24 11:41:35', 'RPG Infantry (Khev Minosk) engaged Desert Fox Bodyguard (76th Army Rangers) in combat, inflicting 1 damage.', 676, 73, 85, -4, 1),
(691, 61, '2024-11-24 11:41:31', 'Medic (76th Army Rangers) exchanged fire with AK-47 Infantry (Khev Minosk), causing 8 damage.', 10, 665, 28, 4, 8),
(690, 61, '2024-11-24 11:41:21', 'Desert Fox Bodyguard (76th Army Rangers) exchanged fire with AK-47 Infantry (Khev Minosk), causing 88 damage. AK-47 Infantry (Khev Minosk) was destroyed!', 73, 671, 16, 4, 88),
(689, 61, '2024-11-24 11:40:57', 'Desert Fox Bodyguard (76th Army Rangers) exchanged fire with AK-47 Infantry (Khev Minosk), causing 138 damage. AK-47 Infantry (Khev Minosk) was destroyed!', 72, 666, 57, 2, 138),
(688, 59, '2024-11-24 11:31:50', 'Followers of Black Horn has retreated due to overwhelming enemy strength!', 0, 0, 0, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `commodities`
--

DROP TABLE IF EXISTS `commodities`;
CREATE TABLE IF NOT EXISTS `commodities` (
  `id` int NOT NULL,
  `money` int NOT NULL DEFAULT '10000',
  `food` int NOT NULL DEFAULT '1000',
  `power` int NOT NULL DEFAULT '1000',
  `building_materials` int NOT NULL DEFAULT '1000',
  `consumer_goods` int NOT NULL DEFAULT '1000',
  `metal` int NOT NULL DEFAULT '500',
  `ammunition` int NOT NULL DEFAULT '0',
  `fuel` int NOT NULL DEFAULT '0',
  `uranium` int NOT NULL DEFAULT '0',
  `whz` int NOT NULL DEFAULT '0',
  `apple_tree` int NOT NULL DEFAULT '0',
  `cactus` int NOT NULL DEFAULT '0',
  `mulberry` int NOT NULL DEFAULT '0',
  `coffea` int NOT NULL DEFAULT '0',
  `herbs` int NOT NULL DEFAULT '0',
  `tobacco_plant` int NOT NULL DEFAULT '0',
  `cotton` int NOT NULL DEFAULT '0',
  `oak_tree` int NOT NULL DEFAULT '0',
  `rubber_tree` int NOT NULL DEFAULT '0',
  `christmas_tree` int NOT NULL DEFAULT '0',
  `cocoa` int NOT NULL DEFAULT '0',
  `grapevine` int NOT NULL DEFAULT '0',
  `hops` int NOT NULL DEFAULT '0',
  `kingwood` int NOT NULL DEFAULT '0',
  `hemp` int NOT NULL DEFAULT '0',
  `beehive` int NOT NULL DEFAULT '0',
  `goat` int NOT NULL DEFAULT '0',
  `cow` int NOT NULL DEFAULT '0',
  `sheep` int NOT NULL DEFAULT '0',
  `boar` int NOT NULL DEFAULT '0',
  `yak` int NOT NULL DEFAULT '0',
  `buffalo` int NOT NULL DEFAULT '0',
  `elephant` int NOT NULL DEFAULT '0',
  `fox` int NOT NULL DEFAULT '0',
  `panther` int NOT NULL DEFAULT '0',
  `clam` int NOT NULL DEFAULT '0',
  `shrimp` int NOT NULL DEFAULT '0',
  `bass` int NOT NULL DEFAULT '0',
  `cod` int NOT NULL DEFAULT '0',
  `mackerel` int NOT NULL DEFAULT '0',
  `salmon` int NOT NULL DEFAULT '0',
  `piranha` int NOT NULL DEFAULT '0',
  `dolphin` int NOT NULL DEFAULT '0',
  `shark` int NOT NULL DEFAULT '0',
  `whale` int NOT NULL DEFAULT '0',
  `coal` int NOT NULL DEFAULT '0',
  `iron` int NOT NULL DEFAULT '0',
  `marble` int NOT NULL DEFAULT '0',
  `bauxite` int NOT NULL DEFAULT '0',
  `copper` int NOT NULL DEFAULT '0',
  `lead` int NOT NULL DEFAULT '0',
  `gold` int NOT NULL DEFAULT '0',
  `platinum` int NOT NULL DEFAULT '0',
  `silver` int NOT NULL DEFAULT '0',
  `saltpeter` int NOT NULL DEFAULT '0',
  `sulfur` int NOT NULL DEFAULT '0',
  `uraninite` int NOT NULL DEFAULT '0',
  `petroleum` int NOT NULL DEFAULT '0',
  `gemstone` int NOT NULL DEFAULT '0',
  `silicon` int NOT NULL DEFAULT '0',
  `stonesilver` int NOT NULL DEFAULT '0',
  `crude_deep_sea_oil` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `commodities`
--

INSERT INTO `commodities` (`id`, `money`, `food`, `power`, `building_materials`, `consumer_goods`, `metal`, `ammunition`, `fuel`, `uranium`, `whz`, `apple_tree`, `cactus`, `mulberry`, `coffea`, `herbs`, `tobacco_plant`, `cotton`, `oak_tree`, `rubber_tree`, `christmas_tree`, `cocoa`, `grapevine`, `hops`, `kingwood`, `hemp`, `beehive`, `goat`, `cow`, `sheep`, `boar`, `yak`, `buffalo`, `elephant`, `fox`, `panther`, `clam`, `shrimp`, `bass`, `cod`, `mackerel`, `salmon`, `piranha`, `dolphin`, `shark`, `whale`, `coal`, `iron`, `marble`, `bauxite`, `copper`, `lead`, `gold`, `platinum`, `silver`, `saltpeter`, `sulfur`, `uraninite`, `petroleum`, `gemstone`, `silicon`, `stonesilver`, `crude_deep_sea_oil`) VALUES
(7, 10019291, 9980785, 996894, 9971899, 982917, 9549, 5100, 50, 0, 0, 1085, 2065, 3, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 444, 432, 100, 0, 0, 0, 0, 0, 0, 0, 866, 869, 0, 0, 0, 0, 0, 0, 0, 0, 2274, 1866, 793, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(8, 69373, 135, 261, 1000, 873, 500, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(9, 46496, 721, 876, 1000, 1000, 502, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `daily_unit`
--

DROP TABLE IF EXISTS `daily_unit`;
CREATE TABLE IF NOT EXISTS `daily_unit` (
  `unit_type` varchar(255) NOT NULL,
  `id` int NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `daily_unit`
--

INSERT INTO `daily_unit` (`unit_type`, `id`) VALUES
('m2_bradley', 1);

-- --------------------------------------------------------

--
-- Table structure for table `divisions`
--

DROP TABLE IF EXISTS `divisions`;
CREATE TABLE IF NOT EXISTS `divisions` (
  `division_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `in_combat` tinyint(1) NOT NULL DEFAULT '0',
  `is_defence` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`division_id`)
) ENGINE=MyISAM AUTO_INCREMENT=87 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `divisions`
--

INSERT INTO `divisions` (`division_id`, `user_id`, `name`, `in_combat`, `is_defence`) VALUES
(4, 7, 'Rason Garrison', 0, 1),
(5, 8, 'Defence Division', 0, 1),
(6, 9, 'Defence Division', 0, 1),
(8, 7, '76th Army Rangers', 0, 0),
(47, 0, 'Khev Minosk', 1, 0),
(46, 0, 'Khev Minosk', 1, 0),
(45, 0, 'Bihadj Insurgents', 1, 0),
(41, 0, 'Bihadj Insurgents', 1, 0),
(40, 0, 'Bihadj Insurgents', 1, 0),
(43, 7, 'Mission Skipper', 1, 0),
(38, 0, 'Bihadj Insurgents', 1, 0),
(37, 0, 'Bihadj Insurgents', 1, 0),
(36, 0, 'Bihadj Insurgents', 1, 0),
(35, 0, 'Bihadj Insurgents', 1, 0),
(34, 0, 'Bihadj Insurgents', 1, 0),
(33, 0, 'Bihadj Insurgents', 1, 0),
(86, 0, 'Khev Minosk', 1, 0),
(85, 0, 'Khev Minosk', 0, 0),
(84, 0, 'Bihadj Insurgents', 0, 0),
(83, 0, 'Oldenburg Defence', 0, 0),
(82, 0, 'Secret Service', 0, 0),
(81, 0, 'Bihadj Insurgents', 0, 0),
(80, 0, 'Khev Minosk', 0, 0),
(69, 0, 'Secret Service', 1, 0),
(79, 0, 'Followers of Black Horn', 0, 0),
(78, 0, 'Followers of Black Horn', 0, 0),
(77, 0, 'Khev Minosk', 1, 0),
(74, 0, 'Oldenburg Defence', 1, 0),
(75, 0, 'Oldenburg Defence', 1, 0),
(76, 0, 'Oldenburg Defence', 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `factories`
--

DROP TABLE IF EXISTS `factories`;
CREATE TABLE IF NOT EXISTS `factories` (
  `id` int NOT NULL,
  `farm` int NOT NULL DEFAULT '1',
  `windmill` int NOT NULL DEFAULT '1',
  `quarry` int NOT NULL DEFAULT '0',
  `sandstone_quarry` int NOT NULL DEFAULT '0',
  `sawmill` int NOT NULL DEFAULT '0',
  `jungle_sawmill` int NOT NULL DEFAULT '0',
  `concrete_factory` int NOT NULL DEFAULT '0',
  `stationery_factory` int NOT NULL DEFAULT '0',
  `ciderworks` int NOT NULL DEFAULT '0',
  `sandy_soda_factory` int NOT NULL DEFAULT '0',
  `silk_factory` int NOT NULL DEFAULT '0',
  `beekeeper` int NOT NULL DEFAULT '0',
  `goat_shepherd` int NOT NULL DEFAULT '0',
  `clam_divers` int NOT NULL DEFAULT '0',
  `shrimp_trawler` int NOT NULL DEFAULT '0',
  `coal_power_plant` int NOT NULL DEFAULT '0',
  `iron_smelter` int NOT NULL DEFAULT '0',
  `stonemason` int NOT NULL DEFAULT '0',
  `hydro_plant` int NOT NULL DEFAULT '0',
  `hydro_dam` int NOT NULL DEFAULT '0',
  `coffee_plantation` int NOT NULL DEFAULT '0',
  `pharmacy` int NOT NULL DEFAULT '0',
  `tobacco_plantation` int NOT NULL DEFAULT '0',
  `dairy_farm` int NOT NULL DEFAULT '0',
  `clothing_factory` int NOT NULL DEFAULT '0',
  `bass_fishery` int NOT NULL DEFAULT '0',
  `cod_fishery` int NOT NULL DEFAULT '0',
  `aluminum_plant` int NOT NULL DEFAULT '0',
  `electrical_engineering_supply_factory` int NOT NULL DEFAULT '0',
  `battery_assembler` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `factories`
--

INSERT INTO `factories` (`id`, `farm`, `windmill`, `quarry`, `sandstone_quarry`, `sawmill`, `jungle_sawmill`, `concrete_factory`, `stationery_factory`, `ciderworks`, `sandy_soda_factory`, `silk_factory`, `beekeeper`, `goat_shepherd`, `clam_divers`, `shrimp_trawler`, `coal_power_plant`, `iron_smelter`, `stonemason`, `hydro_plant`, `hydro_dam`, `coffee_plantation`, `pharmacy`, `tobacco_plantation`, `dairy_farm`, `clothing_factory`, `bass_fishery`, `cod_fishery`, `aluminum_plant`, `electrical_engineering_supply_factory`, `battery_assembler`) VALUES
(7, 12, 5, 11, 3, 2, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 6, 1, 1, 0, 0, 0, 0, 0, 0, 0, 1, 2, 0, 1, 0),
(8, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(9, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `factory_queue`
--

DROP TABLE IF EXISTS `factory_queue`;
CREATE TABLE IF NOT EXISTS `factory_queue` (
  `queue_position` int NOT NULL AUTO_INCREMENT,
  `id` int NOT NULL,
  `factory_type` varchar(255) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `minutes_left` int NOT NULL,
  PRIMARY KEY (`queue_position`)
) ENGINE=MyISAM AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hidden_resources`
--

DROP TABLE IF EXISTS `hidden_resources`;
CREATE TABLE IF NOT EXISTS `hidden_resources` (
  `id` int NOT NULL,
  `money` int NOT NULL DEFAULT '0',
  `food` int NOT NULL DEFAULT '0',
  `power` int NOT NULL DEFAULT '0',
  `building_materials` int NOT NULL DEFAULT '0',
  `consumer_goods` int NOT NULL DEFAULT '0',
  `metal` int NOT NULL DEFAULT '0',
  `ammunition` int NOT NULL DEFAULT '0',
  `fuel` int NOT NULL DEFAULT '0',
  `uranium` int NOT NULL DEFAULT '0',
  `whz` int NOT NULL DEFAULT '0',
  `apple_tree` int NOT NULL DEFAULT '0',
  `cactus` int NOT NULL DEFAULT '0',
  `mulberry` int NOT NULL DEFAULT '0',
  `coffea` int NOT NULL DEFAULT '0',
  `herbs` int NOT NULL DEFAULT '0',
  `tobacco_plant` int NOT NULL DEFAULT '0',
  `cotton` int NOT NULL DEFAULT '0',
  `oak_tree` int NOT NULL DEFAULT '0',
  `rubber_tree` int NOT NULL DEFAULT '0',
  `christmas_tree` int NOT NULL DEFAULT '0',
  `cocoa` int NOT NULL DEFAULT '0',
  `grapevine` int NOT NULL DEFAULT '0',
  `hops` int NOT NULL DEFAULT '0',
  `kingwood` int NOT NULL DEFAULT '0',
  `hemp` int NOT NULL DEFAULT '0',
  `beehive` int NOT NULL DEFAULT '0',
  `goat` int NOT NULL DEFAULT '0',
  `cow` int NOT NULL DEFAULT '0',
  `sheep` int NOT NULL DEFAULT '0',
  `boar` int NOT NULL DEFAULT '0',
  `yak` int NOT NULL DEFAULT '0',
  `buffalo` int NOT NULL DEFAULT '0',
  `elephant` int NOT NULL DEFAULT '0',
  `fox` int NOT NULL DEFAULT '0',
  `panther` int NOT NULL DEFAULT '0',
  `clam` int NOT NULL DEFAULT '0',
  `shrimp` int NOT NULL DEFAULT '0',
  `bass` int NOT NULL DEFAULT '0',
  `cod` int NOT NULL DEFAULT '0',
  `mackerel` int NOT NULL DEFAULT '0',
  `salmon` int NOT NULL DEFAULT '0',
  `piranha` int NOT NULL DEFAULT '0',
  `dolphin` int NOT NULL DEFAULT '0',
  `shark` int NOT NULL DEFAULT '0',
  `whale` int NOT NULL DEFAULT '0',
  `coal` int NOT NULL DEFAULT '0',
  `iron` int NOT NULL DEFAULT '0',
  `marble` int NOT NULL DEFAULT '0',
  `bauxite` int NOT NULL DEFAULT '0',
  `copper` int NOT NULL DEFAULT '0',
  `lead` int NOT NULL DEFAULT '0',
  `gold` int NOT NULL DEFAULT '0',
  `platinum` int NOT NULL DEFAULT '0',
  `silver` int NOT NULL DEFAULT '0',
  `saltpeter` int NOT NULL DEFAULT '0',
  `sulfur` int NOT NULL DEFAULT '0',
  `uraninite` int NOT NULL DEFAULT '0',
  `petroleum` int NOT NULL DEFAULT '0',
  `gemstone` int NOT NULL DEFAULT '0',
  `silicon` int NOT NULL DEFAULT '0',
  `stonesilver` int NOT NULL DEFAULT '0',
  `crude_deep_sea_oil` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `hidden_resources`
--

INSERT INTO `hidden_resources` (`id`, `money`, `food`, `power`, `building_materials`, `consumer_goods`, `metal`, `ammunition`, `fuel`, `uranium`, `whz`, `apple_tree`, `cactus`, `mulberry`, `coffea`, `herbs`, `tobacco_plant`, `cotton`, `oak_tree`, `rubber_tree`, `christmas_tree`, `cocoa`, `grapevine`, `hops`, `kingwood`, `hemp`, `beehive`, `goat`, `cow`, `sheep`, `boar`, `yak`, `buffalo`, `elephant`, `fox`, `panther`, `clam`, `shrimp`, `bass`, `cod`, `mackerel`, `salmon`, `piranha`, `dolphin`, `shark`, `whale`, `coal`, `iron`, `marble`, `bauxite`, `copper`, `lead`, `gold`, `platinum`, `silver`, `saltpeter`, `sulfur`, `uraninite`, `petroleum`, `gemstone`, `silicon`, `stonesilver`, `crude_deep_sea_oil`) VALUES
(7, 10000, 1000, 1000, 1000, 1000, 500, 0, 0, 0, 0, 0, 0, 0, 4, 3, 4, 12, 0, 3, 0, 1, 1, 3, 0, 5, 346, 12, 690, 640, 453, 257, 184, 1867, 98, 77, 0, 0, 688, 689, 568, 610, 506, 196, 261, 68, 0, 0, 0, 342, 690, 293, 1186, 241, 1124, 1190, 1209, 1883, 1884, 987, 402, 71, 1865),
(8, 10000, 1000, 1000, 1000, 1000, 500, 0, 0, 0, 0, 0, 8, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `land`
--

DROP TABLE IF EXISTS `land`;
CREATE TABLE IF NOT EXISTS `land` (
  `id` int NOT NULL,
  `cleared_land` int NOT NULL DEFAULT '50',
  `urban_areas` int NOT NULL DEFAULT '60',
  `used_land` int NOT NULL DEFAULT '0',
  `forest` int NOT NULL DEFAULT '25',
  `mountain` int NOT NULL DEFAULT '0',
  `river` int NOT NULL DEFAULT '5',
  `lake` int NOT NULL DEFAULT '5',
  `grassland` int NOT NULL DEFAULT '0',
  `jungle` int NOT NULL DEFAULT '0',
  `desert` int NOT NULL DEFAULT '0',
  `tundra` int NOT NULL DEFAULT '0',
  `expanded_borders_today` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `land`
--

INSERT INTO `land` (`id`, `cleared_land`, `urban_areas`, `used_land`, `forest`, `mountain`, `river`, `lake`, `grassland`, `jungle`, `desert`, `tundra`, `expanded_borders_today`) VALUES
(7, 1, 84, 178, 44, 67, 84, 86, 42, 86, 151, 73, 0),
(8, 50, 60, 0, 25, 0, 5, 5, 0, 0, 0, 0, 0),
(9, 50, 60, 0, 25, 0, 5, 5, 0, 0, 0, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `missions`
--

DROP TABLE IF EXISTS `missions`;
CREATE TABLE IF NOT EXISTS `missions` (
  `mission_id` int NOT NULL AUTO_INCREMENT,
  `battle_id` int DEFAULT NULL,
  `user_id` int NOT NULL,
  `mission_type` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'incomplete',
  `rewards_claimed` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`mission_id`)
) ENGINE=MyISAM AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `missions`
--

INSERT INTO `missions` (`mission_id`, `battle_id`, `user_id`, `mission_type`, `status`, `rewards_claimed`) VALUES
(24, NULL, 9, 'its_the_least_we_can_do', 'incomplete', 0),
(23, NULL, 9, 'supply_raid', 'incomplete', 0),
(22, NULL, 8, 'foreign_affairs', 'incomplete', 0),
(25, NULL, 7, 'riot_control', 'incomplete', 1),
(21, NULL, 8, 'supply_raid', 'incomplete', 0),
(30, 67, 7, 'suppressing_the_khev_minosk', 'in_progress', 0);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
CREATE TABLE IF NOT EXISTS `notifications` (
  `notification_id` int NOT NULL AUTO_INCREMENT,
  `message` text NOT NULL,
  `type` varchar(255) NOT NULL,
  `date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`notification_id`)
) ENGINE=MyISAM AUTO_INCREMENT=74 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`notification_id`, `message`, `type`, `date`) VALUES
(1, 'Test notification 1         !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!', 'New Nation', '2024-11-14 19:30:39'),
(2, 'Test notification 1         !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!', 'New Nation', '2024-11-14 19:30:51'),
(3, 'Test notification 3', 'Trade', '2024-11-14 19:33:19'),
(4, 'Test notification 4', 'Trade', '2024-11-14 19:33:29'),
(5, 'The nation <a href=\'view.php?id=9\'>Rason 3</a> was founded by Justin Cheng 3.', 'New Nation', '2024-11-14 19:39:55'),
(6, '<a href=\'view.php?id=9\'>Rason</a> bought 1 of <img src=\'resources/food_icon.png\' alt=\'food\' title=\'Food\' class=\'resource-icon\'>Food from <a href=\'view.php?id=7\'>Rason 3</a> at 2 per unit, for a total of 2.', 'Trade', '2024-11-14 19:48:06'),
(7, '<a href=\'view.php?id=9\'>Rason</a> bought <img src=\'resources/power_icon.png\' alt=\'power\' title=\'Power\' class=\'resource-icon\'>1 from <a href=\'view.php?id=7\'>Rason 3</a> at <img src=\'resources/money_icon.png\' alt=\'money\' title=\'Money\' class=\'resource-icon\'>10 per unit, for a total of <img src=\'resources/money_icon.png\' alt=\'money\' title=\'Money\' class=\'resource-icon\'>10.', 'Trade', '2024-11-14 19:50:23'),
(8, '<a href=\'view.php?id=9\'>Rason 3</a> bought <img src=\'resources/power_icon.png\' alt=\'power\' title=\'Power\' class=\'resource-icon\'>1 from <a href=\'view.php?id=7\'>Rason</a> at <img src=\'resources/money_icon.png\' alt=\'money\' title=\'Money\' class=\'resource-icon\'>10 per unit, for a total of <img src=\'resources/money_icon.png\' alt=\'money\' title=\'Money\' class=\'resource-icon\'>10.', 'Trade', '2024-11-14 19:52:12'),
(9, '<a href=\'view.php?id=9\'>Rason 3</a> bought <img src=\'resources/power_icon.png\' alt=\'power\' title=\'Power\' class=\'resource-icon\'>1 from <a href=\'view.php?id=7\'>Rason</a> at <img src=\'resources/money_icon.png\' alt=\'money\' title=\'Money\' class=\'resource-icon\'>10 per unit, for a total of <img src=\'resources/money_icon.png\' alt=\'money\' title=\'Money\' class=\'resource-icon\'>10.', 'Trade', '2024-11-14 19:52:29'),
(10, '<a href=\'view.php?id=9\'>Rason 3</a> bought <img src=\'resources/power_icon.png\' alt=\'power\' title=\'Power\' class=\'resource-icon\'>1 from <a href=\'view.php?id=7\'>Rason</a> at <img src=\'resources/money_icon.png\' alt=\'money\' title=\'Money\' class=\'resource-icon\'>10 per unit, for a total of <img src=\'resources/money_icon.png\' alt=\'money\' title=\'Money\' class=\'resource-icon\'>10.', 'Trade', '2024-11-14 19:53:55'),
(11, '<a href=\'view.php?id=9\'>Rason 3</a> bought <img src=\'resources/power_icon.png\' alt=\'power\' title=\'Power\' class=\'resource-icon\'>1 from <a href=\'view.php?id=7\'>Rason</a> at <img src=\'resources/money_icon.png\' alt=\'money\' title=\'Money\' class=\'resource-icon\'>10 per unit, for a total of <img src=\'resources/money_icon.png\' alt=\'money\' title=\'Money\' class=\'resource-icon\'>10.', 'Trade', '2024-11-14 19:54:29'),
(12, '<a href=\'view.php?id=9\'>Rason 3</a> bought <img src=\'resources/metal_icon.png\' alt=\'metal\' title=\'Metal\' class=\'resource-icon\'>1 from <a href=\'view.php?id=7\'>Rason</a> at <img src=\'resources/money_icon.png\' alt=\'money\' title=\'Money\' class=\'resource-icon\'>120 per unit, for a total of <img src=\'resources/money_icon.png\' alt=\'money\' title=\'Money\' class=\'resource-icon\'>120.', 'Trade', '2024-11-14 19:55:05'),
(13, '<a href=\'view.php?id=9\'>Rason 3</a> bought <img src=\'resources/metal_icon.png\' alt=\'metal\' title=\'Metal\' class=\'resource-icon\'>1 from <a href=\'view.php?id=7\'>Rason</a> at <img src=\'resources/money_icon.png\' alt=\'money\' title=\'Money\' class=\'resource-icon\'>120 per unit, for a total of <img src=\'resources/money_icon.png\' alt=\'money\' title=\'Money\' class=\'resource-icon\'>120.', 'Trade', '2024-11-14 19:55:47'),
(14, '<a href=\'view.php?id=9\'>Rason 3</a> bought <img src=\'resources/food_icon.png\' alt=\'food\' title=\'Food\' class=\'resource-icon\'>1 from <a href=\'view.php?id=7\'>Rason</a> at <img src=\'resources/money_icon.png\' alt=\'money\' title=\'Money\' class=\'resource-icon\'>23 per unit, for a total of <img src=\'resources/money_icon.png\' alt=\'money\' title=\'Money\' class=\'resource-icon\'>23.', 'Trade', '2024-11-14 19:57:30'),
(15, '<a href=\'view.php?id=7\'>Rason 3</a> bought <img src=\'resources/food_icon.png\' alt=\'food\' title=\'Food\' class=\'resource-icon\'>1 from <a href=\'view.php?id=9\'>Rason</a> at <img src=\'resources/money_icon.png\' alt=\'money\' title=\'Money\' class=\'resource-icon\'>4 per unit, for a total of <img src=\'resources/money_icon.png\' alt=\'money\' title=\'Money\' class=\'resource-icon\'>4.', 'Trade', '2024-11-14 19:58:51'),
(16, 'The alliance The Last Bastion 2 was disbanded by Rason 2', 'International Relations', '2024-11-16 17:16:14'),
(17, '<a href=\"view.php?id=8\">Rason 2</a> became a member of <a href=\"alliance_view.php?id=1\">The Last Bastion</a>', 'International Relations', '2024-11-16 17:19:28'),
(18, '<a href=\'view.php?id=7\'>Rason</a> bought <img src=\'resources/power_icon.png\' alt=\'power\' title=\'Power\' class=\'resource-icon\'>1 from <a href=\'view.php?id=9\'>Rason 3</a> at <img src=\'resources/money_icon.png\' alt=\'money\' title=\'Money\' class=\'resource-icon\'>6 per unit, for a total of <img src=\'resources/money_icon.png\' alt=\'money\' title=\'Money\' class=\'resource-icon\'>6.', 'Trade', '2024-11-19 09:41:58'),
(19, '<a href=\'view.php?id=7\'></a> sent a division on a <a href=\'battle.php?battle_id=0\'>peacekeeping mission</a>', 'Conflict', '2024-11-22 22:49:56'),
(20, '<a href=\'view.php?id=7\'>Rason</a> sent a division on a <a href=\'battle.php?battle_id=13\'>Peacekeeping Mission.</a>', 'Conflict', '2024-11-23 13:16:44'),
(21, '<a href=\'view.php?id=7\'>Rason</a> sent a division on a <a href=\'battle.php?battle_id=14\'>Peacekeeping Mission.</a>', 'Conflict', '2024-11-23 13:19:18'),
(22, '<a href=\'view.php?id=7\'>Rason</a> sent a division on a <a href=\'battle.php?battle_id=15\'>Peacekeeping Mission.</a>', 'Conflict', '2024-11-23 13:29:19'),
(23, '<a href=\'view.php?id=7\'>Rason</a> sent a division on a <a href=\'battle.php?battle_id=16\'>Peacekeeping Mission.</a>', 'Conflict', '2024-11-23 13:51:12'),
(24, '<a href=\'view.php?id=7\'>Rason</a> sent a division on a <a href=\'battle.php?battle_id=17\'>Peacekeeping Mission.</a>', 'Conflict', '2024-11-23 14:21:21'),
(25, '<a href=\'view.php?id=7\'>Rason</a> sent a division on a <a href=\'battle.php?battle_id=18\'>Peacekeeping Mission.</a>', 'Conflict', '2024-11-23 14:31:02'),
(26, '<a href=\'view.php?id=7\'>Rason</a> sent a division on a <a href=\'battle.php?battle_id=19\'>Peacekeeping Mission.</a>', 'Conflict', '2024-11-23 14:37:53'),
(27, '<a href=\'view.php?id=7\'>Rason</a> sent a division on a <a href=\'battle.php?battle_id=20\'>Peacekeeping Mission.</a>', 'Conflict', '2024-11-23 14:42:20'),
(28, '<a href=\'view.php?id=7\'>Rason</a> sent a division on a <a href=\'battle.php?battle_id=22\'>Riot Control</a> mission.', 'Conflict', '2024-11-23 16:45:12'),
(29, '<a href=\'view.php?id=7\'>Rason</a> sent a division on a <a href=\'battle.php?battle_id=23\'>Foreign Affairs</a> mission.', 'Conflict', '2024-11-23 18:29:29'),
(30, '<a href=\'view.php?id=7\'>Rason</a> sent a division on a <a href=\'battle.php?battle_id=24\'>Supply Raid</a> mission.', 'Conflict', '2024-11-23 18:43:39'),
(31, '<a href=\'view.php?id=7\'>Rason</a> sent a division on a <a href=\'battle.php?battle_id=25\'>Arctic Drill</a> mission.', 'Conflict', '2024-11-24 09:13:10'),
(32, '<a href=\'view.php?id=7\'>Rason</a> sent a division on a <a href=\'battle.php?battle_id=26\'>Supply Raid</a> mission.', 'Conflict', '2024-11-24 09:18:20'),
(33, '<a href=\'view.php?id=7\'>Rason</a> sent a division on a <a href=\'battle.php?battle_id=27\'>Suppressing the Khev Minosk</a> mission.', 'Conflict', '2024-11-24 09:20:11'),
(34, '<a href=\'view.php?id=7\'>Rason</a> sent a division on a <a href=\'battle.php?battle_id=28\'>Suppressing the Khev Minosk</a> mission.', 'Conflict', '2024-11-24 09:24:15'),
(35, '<a href=\'view.php?id=7\'>Rason</a> sent a division on a <a href=\'battle.php?battle_id=29\'>Suppressing the Khev Minosk</a> mission.', 'Conflict', '2024-11-24 09:32:17'),
(36, '<a href=\'view.php?id=7\'>Rason</a> sent a division on a <a href=\'battle.php?battle_id=30\'>Little Brother Wants Out</a> mission.', 'Conflict', '2024-11-24 09:35:04'),
(37, '<a href=\'view.php?id=7\'>Rason</a> sent a division on a <a href=\'battle.php?battle_id=31\'>Jungle Fever</a> mission.', 'Conflict', '2024-11-24 09:39:55'),
(38, '<a href=\'view.php?id=7\'>Rason</a> sent a division on a <a href=\'battle.php?battle_id=32\'>Supply Raid</a> mission.', 'Conflict', '2024-11-24 09:40:31'),
(39, '<a href=\'view.php?id=7\'>Rason</a> sent a division on a <a href=\'battle.php?battle_id=33\'>Supply Raid</a> mission.', 'Conflict', '2024-11-24 09:41:19'),
(40, '<a href=\'view.php?id=7\'>Rason</a> sent a division on a <a href=\'battle.php?battle_id=34\'>Jungle Fever</a> mission.', 'Conflict', '2024-11-24 09:44:33'),
(41, '<a href=\'view.php?id=7\'>Rason</a> sent a division on a <a href=\'battle.php?battle_id=35\'>Supply Raid</a> mission.', 'Conflict', '2024-11-24 09:46:51'),
(42, '<a href=\'view.php?id=7\'>Rason</a> sent a division on a <a href=\'battle.php?battle_id=36\'>Supply Raid</a> mission.', 'Conflict', '2024-11-24 09:49:26'),
(43, '<a href=\'view.php?id=7\'>Rason</a> sent a division on a <a href=\'battle.php?battle_id=37\'>Supply Raid</a> mission.', 'Conflict', '2024-11-24 09:51:37'),
(44, '<a href=\'view.php?id=7\'>Rason</a> sent a division on a <a href=\'battle.php?battle_id=38\'>Little Brother Wants Out</a> mission.', 'Conflict', '2024-11-24 09:52:19'),
(45, '<a href=\'view.php?id=7\'>Rason</a> sent a division on a <a href=\'battle.php?battle_id=39\'>Jungle Fever</a> mission.', 'Conflict', '2024-11-24 09:55:50'),
(46, '<a href=\'view.php?id=7\'>Rason</a> sent a division on a <a href=\'battle.php?battle_id=40\'>Homeland Offence</a> mission.', 'Conflict', '2024-11-24 09:56:29'),
(47, '<a href=\'view.php?id=7\'>Rason</a> sent a division on a <a href=\'battle.php?battle_id=41\'>It&#039;s the Least We Can Do</a> mission.', 'Conflict', '2024-11-24 10:00:02'),
(48, '<a href=\'view.php?id=7\'>Rason</a> sent a division on a <a href=\'battle.php?battle_id=42\'>Foreign Affairs</a> mission.', 'Conflict', '2024-11-24 10:00:56'),
(49, '<a href=\'view.php?id=7\'>Rason</a> sent a division on a <a href=\'battle.php?battle_id=43\'>Little Brother Wants Out</a> mission.', 'Conflict', '2024-11-24 10:01:30'),
(50, '<a href=\'view.php?id=7\'>Rason</a> sent a division on a <a href=\'battle.php?battle_id=44\'>Supply Raid</a> mission.', 'Conflict', '2024-11-24 10:04:29'),
(51, '<a href=\'view.php?id=7\'>Rason</a> sent a division on a <a href=\'battle.php?battle_id=45\'>It&#039;s the Least We Can Do</a> mission.', 'Conflict', '2024-11-24 10:10:20'),
(52, '<a href=\'view.php?id=7\'>Rason</a> sent a division on a <a href=\'battle.php?battle_id=46\'>Homeland Offence</a> mission.', 'Conflict', '2024-11-24 10:12:25'),
(53, '<a href=\'view.php?id=7\'>Rason</a> sent a division on a <a href=\'battle.php?battle_id=47\'>Homeland Offence</a> mission.', 'Conflict', '2024-11-24 10:15:33'),
(54, '<a href=\'view.php?id=7\'>Rason</a> sent a division on a <a href=\'battle.php?battle_id=48\'>Supply Raid</a> mission.', 'Conflict', '2024-11-24 10:19:28'),
(55, '<a href=\'view.php?id=7\'>Rason</a> sent a division on a <a href=\'battle.php?battle_id=49\'>Homeland Offence</a> mission.', 'Conflict', '2024-11-24 10:19:50'),
(56, '<a href=\'view.php?id=7\'>Rason</a> sent a division on a <a href=\'battle.php?battle_id=50\'>Foreign Affairs</a> mission.', 'Conflict', '2024-11-24 10:22:47'),
(57, '<a href=\'view.php?id=7\'>Rason</a> sent a division on a <a href=\'battle.php?battle_id=51\'>Foreign Affairs</a> mission.', 'Conflict', '2024-11-24 10:26:57'),
(58, '<a href=\'view.php?id=7\'>Rason</a> sent a division on a <a href=\'battle.php?battle_id=52\'>Homeland Offence</a> mission.', 'Conflict', '2024-11-24 10:27:39'),
(59, '<a href=\'view.php?id=7\'>Rason</a> sent a division on a <a href=\'battle.php?battle_id=53\'>Jungle Fever</a> mission.', 'Conflict', '2024-11-24 10:28:57'),
(60, '<a href=\'view.php?id=7\'>Rason</a> sent a division on a <a href=\'battle.php?battle_id=54\'>Jungle Fever</a> mission.', 'Conflict', '2024-11-24 10:30:05'),
(61, '<a href=\'view.php?id=7\'>Rason</a> sent a division on a <a href=\'battle.php?battle_id=55\'>Homeland Offence</a> mission.', 'Conflict', '2024-11-24 10:57:56'),
(62, '<a href=\'view.php?id=7\'>Rason</a> sent a division on a <a href=\'battle.php?battle_id=56\'>Homeland Offence</a> mission.', 'Conflict', '2024-11-24 11:04:42'),
(63, '<a href=\'view.php?id=7\'>Rason</a> sent a division on a <a href=\'battle.php?battle_id=57\'>Homeland Offence</a> mission.', 'Conflict', '2024-11-24 11:07:22'),
(64, '<a href=\'view.php?id=7\'>Rason</a> sent a division on a <a href=\'battle.php?battle_id=58\'>Suppressing the Khev Minosk</a> mission.', 'Conflict', '2024-11-24 11:13:08'),
(65, '<a href=\'view.php?id=7\'>Rason</a> sent a division on a <a href=\'battle.php?battle_id=59\'>Jungle Fever</a> mission.', 'Conflict', '2024-11-24 11:20:55'),
(66, '<a href=\'view.php?id=7\'>Rason</a> sent a division on a <a href=\'battle.php?battle_id=60\'>Jungle Fever</a> mission.', 'Conflict', '2024-11-24 11:33:56'),
(67, '<a href=\'view.php?id=7\'>Rason</a> sent a division on a <a href=\'battle.php?battle_id=61\'>Suppressing the Khev Minosk</a> mission.', 'Conflict', '2024-11-24 11:40:42'),
(68, '<a href=\'view.php?id=7\'>Rason</a> sent a division on a <a href=\'battle.php?battle_id=62\'>Supply Raid</a> mission.', 'Conflict', '2024-11-24 11:43:27'),
(69, '<a href=\'view.php?id=7\'>Rason</a> sent a division on a <a href=\'battle.php?battle_id=63\'>Foreign Affairs</a> mission.', 'Conflict', '2024-11-24 11:50:56'),
(70, '<a href=\'view.php?id=7\'>Rason</a> sent a division on a <a href=\'battle.php?battle_id=64\'>Homeland Offence</a> mission.', 'Conflict', '2024-11-24 11:55:54'),
(71, '<a href=\'view.php?id=7\'>Rason</a> sent a division on a <a href=\'battle.php?battle_id=65\'>Supply Raid</a> mission.', 'Conflict', '2024-11-24 11:59:36'),
(72, '<a href=\'view.php?id=7\'>Rason</a> sent a division on a <a href=\'battle.php?battle_id=66\'>Suppressing the Khev Minosk</a> mission.', 'Conflict', '2024-11-24 12:06:47'),
(73, '<a href=\'view.php?id=7\'>Rason</a> sent a division on a <a href=\'battle.php?battle_id=67\'>Suppressing the Khev Minosk</a> mission.', 'Conflict', '2024-11-24 12:17:17');

-- --------------------------------------------------------

--
-- Table structure for table `production_capacity`
--

DROP TABLE IF EXISTS `production_capacity`;
CREATE TABLE IF NOT EXISTS `production_capacity` (
  `id` int NOT NULL,
  `farm` int NOT NULL DEFAULT '1',
  `windmill` int NOT NULL DEFAULT '1',
  `quarry` int NOT NULL DEFAULT '0',
  `sandstone_quarry` int NOT NULL DEFAULT '0',
  `sawmill` int NOT NULL DEFAULT '0',
  `jungle_sawmill` int NOT NULL DEFAULT '0',
  `concrete_factory` int NOT NULL DEFAULT '0',
  `stationery_factory` int NOT NULL DEFAULT '0',
  `ciderworks` int NOT NULL DEFAULT '0',
  `sandy_soda_factory` int NOT NULL DEFAULT '0',
  `silk_factory` int NOT NULL DEFAULT '0',
  `beekeeper` int NOT NULL DEFAULT '0',
  `goat_shepherd` int NOT NULL DEFAULT '0',
  `clam_divers` int NOT NULL DEFAULT '0',
  `shrimp_trawler` int NOT NULL DEFAULT '0',
  `coal_power_plant` int NOT NULL DEFAULT '0',
  `iron_smelter` int NOT NULL DEFAULT '0',
  `stonemason` int NOT NULL DEFAULT '0',
  `hydro_plant` int NOT NULL DEFAULT '0',
  `hydro_dam` int NOT NULL DEFAULT '0',
  `coffee_plantation` int NOT NULL DEFAULT '0',
  `pharmacy` int NOT NULL DEFAULT '0',
  `tobacco_plantation` int NOT NULL DEFAULT '0',
  `dairy_farm` int NOT NULL DEFAULT '0',
  `clothing_factory` int NOT NULL DEFAULT '0',
  `bass_fishery` int NOT NULL DEFAULT '0',
  `cod_fishery` int NOT NULL DEFAULT '0',
  `aluminum_plant` int NOT NULL DEFAULT '0',
  `electrical_engineering_supply_factory` int NOT NULL DEFAULT '0',
  `battery_assembler` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `production_capacity`
--

INSERT INTO `production_capacity` (`id`, `farm`, `windmill`, `quarry`, `sandstone_quarry`, `sawmill`, `jungle_sawmill`, `concrete_factory`, `stationery_factory`, `ciderworks`, `sandy_soda_factory`, `silk_factory`, `beekeeper`, `goat_shepherd`, `clam_divers`, `shrimp_trawler`, `coal_power_plant`, `iron_smelter`, `stonemason`, `hydro_plant`, `hydro_dam`, `coffee_plantation`, `pharmacy`, `tobacco_plantation`, `dairy_farm`, `clothing_factory`, `bass_fishery`, `cod_fishery`, `aluminum_plant`, `electrical_engineering_supply_factory`, `battery_assembler`) VALUES
(7, 22, 22, 22, 22, 22, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 21, 21, 21, 0, 0, 0, 0, 0, 0, 0, 15, 15, 0, 15, 0),
(8, 24, 24, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(9, 22, 22, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `trades`
--

DROP TABLE IF EXISTS `trades`;
CREATE TABLE IF NOT EXISTS `trades` (
  `trade_id` int NOT NULL AUTO_INCREMENT,
  `seller_id` int NOT NULL,
  `resource_offered` varchar(255) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `amount_offered` int NOT NULL,
  `price_per_unit` int NOT NULL,
  `date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`trade_id`)
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `trades`
--

INSERT INTO `trades` (`trade_id`, `seller_id`, `resource_offered`, `amount_offered`, `price_per_unit`, `date`) VALUES
(11, 9, 'food', 20, 5, '2024-11-19 09:30:20'),
(12, 9, 'power', 24, 6, '2024-11-19 09:38:42'),
(10, 9, 'food', 20, 4, '2024-11-19 09:30:14'),
(9, 9, 'food', 19, 4, '2024-11-14 19:58:26'),
(13, 7, 'food', 7, 8, '2024-11-19 09:40:12');

-- --------------------------------------------------------

--
-- Table structure for table `trade_history`
--

DROP TABLE IF EXISTS `trade_history`;
CREATE TABLE IF NOT EXISTS `trade_history` (
  `trade_id` int NOT NULL AUTO_INCREMENT,
  `seller_id` int NOT NULL,
  `buyer_id` int NOT NULL,
  `resource_offered` varchar(255) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `amount_offered` int NOT NULL,
  `price_per_unit` int NOT NULL,
  `date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_finished` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`trade_id`)
) ENGINE=MyISAM AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `trade_history`
--

INSERT INTO `trade_history` (`trade_id`, `seller_id`, `buyer_id`, `resource_offered`, `amount_offered`, `price_per_unit`, `date`, `date_finished`) VALUES
(1, 8, 7, 'food', 1, 3, '2024-10-27 21:48:17', '2024-10-27 21:48:17'),
(2, 7, 8, 'power', 3, 2, '2024-11-14 13:40:51', '2024-11-14 13:40:51'),
(3, 7, 9, 'power', 1, 2, '2024-11-14 19:46:04', '2024-11-14 19:46:04'),
(4, 7, 9, 'power', 1, 2, '2024-11-14 19:47:07', '2024-11-14 19:47:07'),
(5, 7, 9, 'food', 1, 2, '2024-11-14 19:48:06', '2024-11-14 19:48:06'),
(6, 7, 9, 'power', 1, 10, '2024-11-14 19:50:23', '2024-11-14 19:50:23'),
(7, 7, 9, 'power', 1, 10, '2024-11-14 19:52:12', '2024-11-14 19:52:12'),
(8, 7, 9, 'power', 1, 10, '2024-11-14 19:52:29', '2024-11-14 19:52:29'),
(9, 7, 9, 'power', 1, 10, '2024-11-14 19:53:55', '2024-11-14 19:53:55'),
(10, 7, 9, 'power', 1, 10, '2024-11-14 19:54:29', '2024-11-14 19:54:29'),
(11, 7, 9, 'metal', 1, 120, '2024-11-14 19:55:05', '2024-11-14 19:55:05'),
(12, 7, 9, 'metal', 1, 120, '2024-11-14 19:55:47', '2024-11-14 19:55:47'),
(13, 7, 9, 'food', 1, 23, '2024-11-14 19:57:30', '2024-11-14 19:57:30'),
(14, 9, 7, 'food', 1, 4, '2024-11-14 19:58:51', '2024-11-14 19:58:51'),
(15, 9, 7, 'power', 1, 6, '2024-11-19 09:41:58', '2024-11-19 09:41:58');

-- --------------------------------------------------------

--
-- Table structure for table `units`
--

DROP TABLE IF EXISTS `units`;
CREATE TABLE IF NOT EXISTS `units` (
  `unit_id` int NOT NULL AUTO_INCREMENT,
  `player_id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `custom_name` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `level` int NOT NULL DEFAULT '1',
  `xp` int NOT NULL DEFAULT '0',
  `division_id` int NOT NULL,
  `firepower` int NOT NULL,
  `armour` int NOT NULL,
  `maneuver` int NOT NULL,
  `max_hp` int DEFAULT NULL,
  `hp` int NOT NULL,
  `equipment_1_id` int NOT NULL DEFAULT '0',
  `equipment_2_id` int NOT NULL DEFAULT '0',
  `equipment_3_id` int NOT NULL DEFAULT '0',
  `equipment_4_id` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`unit_id`)
) ENGINE=MyISAM AUTO_INCREMENT=762 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `units`
--

INSERT INTO `units` (`unit_id`, `player_id`, `name`, `custom_name`, `type`, `level`, `xp`, `division_id`, `firepower`, `armour`, `maneuver`, `max_hp`, `hp`, `equipment_1_id`, `equipment_2_id`, `equipment_3_id`, `equipment_4_id`) VALUES
(724, 0, 'AK-47 Infantry', 'AK-47 Infantry', 'Infantry', 1, 4, 84, 3, 1, 2, 50, 50, 0, 0, 0, 0),
(10, 7, 'Medic', 'Medic', 'Infantry', 15, 297, 8, 1, 5, 6, 86, 86, 0, 0, 0, 0),
(240, 0, 'AK-47 Infantry', 'AK-47 Infantry', 'Infantry', 1, 0, 47, 3, 1, 2, 50, 50, 0, 0, 0, 0),
(734, 7, 'Infantry', 'Infantry', 'Infantry', 1, 0, 43, 3, 1, 2, 50, 21, 0, 0, 0, 0),
(218, 0, 'RPG Infantry', 'RPG Infantry', 'Infantry', 1, 0, 45, 3, 1, 2, 50, 50, 0, 0, 0, 0),
(219, 0, 'RPG Infantry', 'RPG Infantry', 'Infantry', 1, 0, 45, 3, 1, 2, 50, 50, 0, 0, 0, 0),
(220, 0, 'T-72 Tank', 'T-72 Tank', 'Armour', 1, 0, 45, 3, 4, 2, 130, 130, 0, 0, 0, 0),
(221, 0, 'Desert Fox Bodyguard', 'Desert Fox Bodyguard', 'Infantry', 1, 0, 45, 3, 2, 2, 50, 50, 0, 0, 0, 0),
(222, 0, 'AK-47 Infantry', 'AK-47 Infantry', 'Infantry', 1, 0, 46, 3, 1, 2, 50, 50, 0, 0, 0, 0),
(246, 0, 'RPG Infantry', 'RPG Infantry', 'Infantry', 1, 0, 47, 3, 1, 2, 50, 50, 0, 0, 0, 0),
(247, 0, 'RPG Infantry', 'RPG Infantry', 'Infantry', 1, 0, 47, 3, 1, 2, 50, 50, 0, 0, 0, 0),
(244, 0, 'AK-47 Infantry', 'AK-47 Infantry', 'Infantry', 1, 0, 47, 3, 1, 2, 50, 50, 0, 0, 0, 0),
(245, 0, 'RPG Infantry', 'RPG Infantry', 'Infantry', 1, 0, 47, 3, 1, 2, 50, 50, 0, 0, 0, 0),
(699, 0, 'Secret Agent', 'Secret Agent', 'Special Forces', 1, 6, 82, 4, 2, 3, 50, 50, 0, 0, 0, 0),
(698, 0, 'Secret Agent', 'Secret Agent', 'Special Forces', 1, 6, 82, 4, 2, 3, 50, 50, 0, 0, 0, 0),
(243, 0, 'AK-47 Infantry', 'AK-47 Infantry', 'Infantry', 1, 0, 47, 3, 1, 2, 50, 50, 0, 0, 0, 0),
(241, 0, 'AK-47 Infantry', 'AK-47 Infantry', 'Infantry', 1, 0, 47, 3, 1, 2, 50, 50, 0, 0, 0, 0),
(242, 0, 'AK-47 Infantry', 'AK-47 Infantry', 'Infantry', 1, 0, 47, 3, 1, 2, 50, 50, 0, 0, 0, 0),
(237, 0, 'AK-47 Infantry', 'AK-47 Infantry', 'Infantry', 1, 0, 47, 3, 1, 2, 50, 50, 0, 0, 0, 0),
(238, 0, 'AK-47 Infantry', 'AK-47 Infantry', 'Infantry', 1, 0, 47, 3, 1, 2, 50, 50, 0, 0, 0, 0),
(239, 0, 'AK-47 Infantry', 'AK-47 Infantry', 'Infantry', 1, 0, 47, 3, 1, 2, 50, 50, 0, 0, 0, 0),
(229, 0, 'AK-47 Infantry', 'AK-47 Infantry', 'Infantry', 1, 0, 46, 3, 1, 2, 50, 50, 0, 0, 0, 0),
(230, 0, 'AK-47 Infantry', 'AK-47 Infantry', 'Infantry', 1, 0, 46, 3, 1, 2, 50, 50, 0, 0, 0, 0),
(231, 0, 'RPG Infantry', 'RPG Infantry', 'Infantry', 1, 0, 46, 3, 1, 2, 50, 50, 0, 0, 0, 0),
(232, 0, 'RPG Infantry', 'RPG Infantry', 'Infantry', 1, 0, 46, 3, 1, 2, 50, 50, 0, 0, 0, 0),
(233, 0, 'RPG Infantry', 'RPG Infantry', 'Infantry', 1, 0, 46, 3, 1, 2, 50, 50, 0, 0, 0, 0),
(234, 0, 'AK-47 Infantry', 'AK-47 Infantry', 'Infantry', 1, 0, 47, 3, 1, 2, 50, 50, 0, 0, 0, 0),
(235, 0, 'AK-47 Infantry', 'AK-47 Infantry', 'Infantry', 1, 0, 47, 3, 1, 2, 50, 50, 0, 0, 0, 0),
(236, 0, 'AK-47 Infantry', 'AK-47 Infantry', 'Infantry', 1, 0, 47, 3, 1, 2, 50, 50, 0, 0, 0, 0),
(217, 0, 'RPG Infantry', 'RPG Infantry', 'Infantry', 1, 0, 45, 3, 1, 2, 50, 50, 0, 0, 0, 0),
(216, 0, 'AK-47 Infantry', 'AK-47 Infantry', 'Infantry', 1, 0, 45, 3, 1, 2, 50, 50, 0, 0, 0, 0),
(742, 0, 'AK-47 Infantry', 'AK-47 Infantry', 'Infantry', 1, 6, 85, 3, 1, 2, 50, 50, 0, 0, 0, 0),
(741, 0, 'AK-47 Infantry', 'AK-47 Infantry', 'Infantry', 1, 6, 85, 3, 1, 2, 50, 50, 0, 0, 0, 0),
(754, 0, 'AK-47 Infantry', 'AK-47 Infantry', 'Infantry', 1, 0, 86, 3, 1, 2, 50, 50, 0, 0, 0, 0),
(753, 0, 'AK-47 Infantry', 'AK-47 Infantry', 'Infantry', 1, 0, 86, 3, 1, 2, 50, 50, 0, 0, 0, 0),
(213, 0, 'AK-47 Infantry', 'AK-47 Infantry', 'Infantry', 1, 0, 45, 3, 1, 2, 50, 50, 0, 0, 0, 0),
(214, 0, 'AK-47 Infantry', 'AK-47 Infantry', 'Infantry', 1, 0, 45, 3, 1, 2, 50, 50, 0, 0, 0, 0),
(215, 0, 'AK-47 Infantry', 'AK-47 Infantry', 'Infantry', 1, 0, 45, 3, 1, 2, 50, 50, 0, 0, 0, 0),
(72, 7, 'Desert Fox Bodyguard', 'Desert Fox Bodyguard', 'Infantry', 15, 297, 8, 11, 5, 3, 60, 60, 0, 0, 0, 0),
(73, 7, 'Desert Fox Bodyguard', 'Desert Fox Bodyguard', 'Infantry', 15, 297, 8, 7, 5, 5, 72, 72, 0, 0, 0, 0),
(683, 0, 'AK-47 Infantry', 'AK-47 Infantry', 'Infantry', 1, 6, 81, 3, 1, 2, 50, 50, 0, 0, 0, 0),
(227, 0, 'AK-47 Infantry', 'AK-47 Infantry', 'Infantry', 1, 0, 46, 3, 1, 2, 50, 50, 0, 0, 0, 0),
(228, 0, 'AK-47 Infantry', 'AK-47 Infantry', 'Infantry', 1, 0, 46, 3, 1, 2, 50, 50, 0, 0, 0, 0),
(226, 0, 'AK-47 Infantry', 'AK-47 Infantry', 'Infantry', 1, 0, 46, 3, 1, 2, 50, 50, 0, 0, 0, 0),
(225, 0, 'AK-47 Infantry', 'AK-47 Infantry', 'Infantry', 1, 0, 46, 3, 1, 2, 50, 50, 0, 0, 0, 0),
(224, 0, 'AK-47 Infantry', 'AK-47 Infantry', 'Infantry', 1, 0, 46, 3, 1, 2, 50, 50, 0, 0, 0, 0),
(211, 0, 'AK-47 Infantry', 'AK-47 Infantry', 'Infantry', 1, 0, 45, 3, 1, 2, 50, 50, 0, 0, 0, 0),
(209, 0, 'AK-47 Infantry', 'AK-47 Infantry', 'Infantry', 1, 0, 45, 3, 1, 2, 50, 50, 0, 0, 0, 0),
(223, 0, 'AK-47 Infantry', 'AK-47 Infantry', 'Infantry', 1, 0, 46, 3, 1, 2, 50, 50, 0, 0, 0, 0),
(210, 0, 'AK-47 Infantry', 'AK-47 Infantry', 'Infantry', 1, 0, 45, 3, 1, 2, 50, 50, 0, 0, 0, 0),
(212, 0, 'AK-47 Infantry', 'AK-47 Infantry', 'Infantry', 1, 0, 45, 3, 1, 2, 50, 50, 0, 0, 0, 0),
(761, 0, 'RPG Infantry', 'RPG Infantry', 'Infantry', 1, 0, 86, 3, 1, 2, 50, 50, 0, 0, 0, 0),
(760, 0, 'RPG Infantry', 'RPG Infantry', 'Infantry', 1, 0, 86, 3, 1, 2, 50, 50, 0, 0, 0, 0),
(759, 0, 'RPG Infantry', 'RPG Infantry', 'Infantry', 1, 0, 86, 3, 1, 2, 50, 50, 0, 0, 0, 0),
(758, 0, 'AK-47 Infantry', 'AK-47 Infantry', 'Infantry', 1, 0, 86, 3, 1, 2, 50, 50, 0, 0, 0, 0),
(757, 0, 'AK-47 Infantry', 'AK-47 Infantry', 'Infantry', 1, 0, 86, 3, 1, 2, 50, 50, 0, 0, 0, 0),
(756, 0, 'AK-47 Infantry', 'AK-47 Infantry', 'Infantry', 1, 0, 86, 3, 1, 2, 50, 50, 0, 0, 0, 0),
(755, 0, 'AK-47 Infantry', 'AK-47 Infantry', 'Infantry', 1, 0, 86, 3, 1, 2, 50, 50, 0, 0, 0, 0),
(752, 0, 'AK-47 Infantry', 'AK-47 Infantry', 'Infantry', 1, 0, 86, 3, 1, 2, 50, 50, 0, 0, 0, 0),
(751, 0, 'AK-47 Infantry', 'AK-47 Infantry', 'Infantry', 1, 0, 86, 3, 1, 2, 50, 50, 0, 0, 0, 0),
(747, 0, 'RPG Infantry', 'RPG Infantry', 'Infantry', 1, 6, 85, 3, 1, 2, 50, 50, 0, 0, 0, 0),
(746, 0, 'RPG Infantry', 'RPG Infantry', 'Infantry', 1, 6, 85, 3, 1, 2, 50, 50, 0, 0, 0, 0),
(745, 0, 'RPG Infantry', 'RPG Infantry', 'Infantry', 1, 6, 85, 3, 1, 2, 50, 50, 0, 0, 0, 0),
(744, 0, 'AK-47 Infantry', 'AK-47 Infantry', 'Infantry', 1, 6, 85, 3, 1, 2, 50, 50, 0, 0, 0, 0),
(743, 0, 'AK-47 Infantry', 'AK-47 Infantry', 'Infantry', 1, 6, 85, 3, 1, 2, 50, 50, 0, 0, 0, 0),
(740, 0, 'AK-47 Infantry', 'AK-47 Infantry', 'Infantry', 1, 6, 85, 3, 1, 2, 50, 50, 0, 0, 0, 0),
(739, 0, 'AK-47 Infantry', 'AK-47 Infantry', 'Infantry', 1, 6, 85, 3, 1, 2, 50, 50, 0, 0, 0, 0),
(750, 0, 'AK-47 Infantry', 'AK-47 Infantry', 'Infantry', 1, 0, 86, 3, 1, 2, 50, 50, 0, 0, 0, 0),
(748, 0, 'AK-47 Infantry', 'AK-47 Infantry', 'Infantry', 1, 0, 86, 3, 1, 2, 50, 50, 0, 0, 0, 0),
(730, 0, 'Desert Fox Bodyguard', 'Desert Fox Bodyguard', 'Infantry', 1, 4, 84, 3, 2, 2, 50, 50, 0, 0, 0, 0),
(727, 0, 'RPG Infantry', 'RPG Infantry', 'Infantry', 1, 4, 84, 3, 1, 2, 50, 50, 0, 0, 0, 0),
(738, 0, 'AK-47 Infantry', 'AK-47 Infantry', 'Infantry', 1, 6, 85, 3, 1, 2, 50, 50, 0, 0, 0, 0),
(737, 0, 'AK-47 Infantry', 'AK-47 Infantry', 'Infantry', 1, 6, 85, 3, 1, 2, 50, 50, 0, 0, 0, 0),
(736, 7, 'Infantry', 'Infantry', 'Infantry', 1, 0, 43, 3, 1, 2, 50, 0, 0, 0, 0, 0),
(749, 0, 'AK-47 Infantry', 'AK-47 Infantry', 'Infantry', 1, 0, 86, 3, 1, 2, 50, 50, 0, 0, 0, 0),
(729, 0, 'Desert Fox Bodyguard', 'Desert Fox Bodyguard', 'Infantry', 1, 4, 84, 3, 2, 2, 50, 50, 0, 0, 0, 0),
(728, 0, 'T-72 Tank', 'T-72 Tank', 'Armour', 1, 4, 84, 3, 4, 2, 130, 130, 0, 0, 0, 0),
(726, 0, 'RPG Infantry', 'RPG Infantry', 'Infantry', 1, 4, 84, 3, 1, 2, 50, 50, 0, 0, 0, 0),
(725, 0, 'RPG Infantry', 'RPG Infantry', 'Infantry', 1, 4, 84, 3, 1, 2, 50, 50, 0, 0, 0, 0),
(723, 0, 'AK-47 Infantry', 'AK-47 Infantry', 'Infantry', 1, 4, 84, 3, 1, 2, 50, 50, 0, 0, 0, 0),
(722, 0, 'AK-47 Infantry', 'AK-47 Infantry', 'Infantry', 1, 4, 84, 3, 1, 2, 50, 50, 0, 0, 0, 0),
(721, 0, 'AK-47 Infantry', 'AK-47 Infantry', 'Infantry', 1, 4, 84, 3, 1, 2, 50, 50, 0, 0, 0, 0),
(720, 0, 'AK-47 Infantry', 'AK-47 Infantry', 'Infantry', 1, 4, 84, 3, 1, 2, 50, 50, 0, 0, 0, 0),
(719, 0, 'AK-47 Infantry', 'AK-47 Infantry', 'Infantry', 1, 4, 84, 3, 1, 2, 50, 50, 0, 0, 0, 0),
(718, 0, 'AK-47 Infantry', 'AK-47 Infantry', 'Infantry', 1, 4, 84, 3, 1, 2, 50, 50, 0, 0, 0, 0),
(717, 0, 'National Guard', 'National Guard', 'Infantry', 1, 6, 83, 2, 1, 1, 50, 50, 0, 0, 0, 0),
(716, 0, 'National Guard', 'National Guard', 'Infantry', 1, 6, 83, 2, 1, 1, 50, 50, 0, 0, 0, 0),
(715, 0, 'National Guard', 'National Guard', 'Infantry', 1, 6, 83, 2, 1, 1, 50, 50, 0, 0, 0, 0),
(714, 0, 'National Guard', 'National Guard', 'Infantry', 1, 6, 83, 2, 1, 1, 50, 50, 0, 0, 0, 0),
(713, 0, 'National Guard', 'National Guard', 'Infantry', 1, 6, 83, 2, 1, 1, 50, 50, 0, 0, 0, 0),
(712, 0, 'National Guard', 'National Guard', 'Infantry', 1, 6, 83, 2, 1, 1, 50, 50, 0, 0, 0, 0),
(711, 0, 'National Guard', 'National Guard', 'Infantry', 1, 6, 83, 2, 1, 1, 50, 50, 0, 0, 0, 0),
(710, 0, 'National Guard', 'National Guard', 'Infantry', 1, 6, 83, 2, 1, 1, 50, 50, 0, 0, 0, 0),
(709, 0, 'National Guard', 'National Guard', 'Infantry', 1, 6, 83, 2, 1, 1, 50, 50, 0, 0, 0, 0),
(708, 0, 'National Guard', 'National Guard', 'Infantry', 1, 6, 83, 2, 1, 1, 50, 50, 0, 0, 0, 0),
(707, 0, 'National Guard', 'National Guard', 'Infantry', 1, 6, 83, 2, 1, 1, 50, 50, 0, 0, 0, 0),
(706, 0, 'Medic', 'Medic', 'Infantry', 1, 6, 83, 1, 1, 2, 50, 50, 0, 0, 0, 0),
(705, 0, 'Secret Agent', 'Secret Agent', 'Special Forces', 1, 6, 82, 4, 2, 3, 50, 50, 0, 0, 0, 0),
(704, 0, 'Secret Agent', 'Secret Agent', 'Special Forces', 1, 6, 82, 4, 2, 3, 50, 50, 0, 0, 0, 0),
(703, 0, 'Secret Agent', 'Secret Agent', 'Special Forces', 1, 6, 82, 4, 2, 3, 50, 50, 0, 0, 0, 0),
(702, 0, 'Secret Agent', 'Secret Agent', 'Special Forces', 1, 6, 82, 4, 2, 3, 50, 50, 0, 0, 0, 0),
(701, 0, 'Secret Agent', 'Secret Agent', 'Special Forces', 1, 6, 82, 4, 2, 3, 50, 50, 0, 0, 0, 0),
(700, 0, 'Secret Agent', 'Secret Agent', 'Special Forces', 1, 6, 82, 4, 2, 3, 50, 50, 0, 0, 0, 0),
(697, 0, 'Secret Agent', 'Secret Agent', 'Special Forces', 1, 6, 82, 4, 2, 3, 50, 50, 0, 0, 0, 0),
(696, 0, 'Secret Agent', 'Secret Agent', 'Special Forces', 1, 6, 82, 4, 2, 3, 50, 50, 0, 0, 0, 0),
(735, 7, 'Infantry', 'Infantry', 'Infantry', 1, 0, 43, 3, 1, 2, 50, 0, 0, 0, 0, 0),
(687, 0, 'AK-47 Infantry', 'AK-47 Infantry', 'Infantry', 1, 6, 81, 3, 1, 2, 50, 50, 0, 0, 0, 0),
(682, 0, 'AK-47 Infantry', 'AK-47 Infantry', 'Infantry', 1, 6, 81, 3, 1, 2, 50, 50, 0, 0, 0, 0),
(692, 0, 'Desert Fox Bodyguard', 'Desert Fox Bodyguard', 'Infantry', 1, 6, 81, 3, 2, 2, 50, 50, 0, 0, 0, 0),
(691, 0, 'RPG Infantry', 'RPG Infantry', 'Infantry', 1, 6, 81, 3, 1, 2, 50, 50, 0, 0, 0, 0),
(690, 0, 'RPG Infantry', 'RPG Infantry', 'Infantry', 1, 6, 81, 3, 1, 2, 50, 50, 0, 0, 0, 0),
(689, 0, 'RPG Infantry', 'RPG Infantry', 'Infantry', 1, 6, 81, 3, 1, 2, 50, 50, 0, 0, 0, 0),
(688, 0, 'AK-47 Infantry', 'AK-47 Infantry', 'Infantry', 1, 6, 81, 3, 1, 2, 50, 50, 0, 0, 0, 0),
(522, 0, 'Secret Agent', 'Secret Agent', 'Special Forces', 1, 0, 69, 4, 2, 3, 50, 50, 0, 0, 0, 0),
(523, 0, 'Secret Agent', 'Secret Agent', 'Special Forces', 1, 0, 69, 4, 2, 3, 50, 50, 0, 0, 0, 0),
(524, 0, 'Secret Agent', 'Secret Agent', 'Special Forces', 1, 0, 69, 4, 2, 3, 50, 50, 0, 0, 0, 0),
(525, 0, 'Secret Agent', 'Secret Agent', 'Special Forces', 1, 0, 69, 4, 2, 3, 50, 26, 0, 0, 0, 0),
(526, 0, 'Secret Agent', 'Secret Agent', 'Special Forces', 1, 0, 69, 4, 2, 3, 50, 50, 0, 0, 0, 0),
(527, 0, 'Secret Agent', 'Secret Agent', 'Special Forces', 1, 0, 69, 4, 2, 3, 50, 50, 0, 0, 0, 0),
(528, 0, 'Secret Agent', 'Secret Agent', 'Special Forces', 1, 0, 69, 4, 2, 3, 50, 50, 0, 0, 0, 0),
(529, 0, 'Secret Agent', 'Secret Agent', 'Special Forces', 1, 0, 69, 4, 2, 3, 50, 50, 0, 0, 0, 0),
(530, 0, 'Secret Agent', 'Secret Agent', 'Special Forces', 1, 0, 69, 4, 2, 3, 50, 50, 0, 0, 0, 0),
(531, 0, 'Secret Agent', 'Secret Agent', 'Special Forces', 1, 0, 69, 4, 2, 3, 50, 50, 0, 0, 0, 0),
(532, 0, 'Secret Agent', 'Secret Agent', 'Special Forces', 1, 0, 69, 4, 2, 3, 50, 50, 0, 0, 0, 0),
(686, 0, 'AK-47 Infantry', 'AK-47 Infantry', 'Infantry', 1, 6, 81, 3, 1, 2, 50, 50, 0, 0, 0, 0),
(684, 0, 'AK-47 Infantry', 'AK-47 Infantry', 'Infantry', 1, 6, 81, 3, 1, 2, 50, 50, 0, 0, 0, 0),
(685, 0, 'AK-47 Infantry', 'AK-47 Infantry', 'Infantry', 1, 6, 81, 3, 1, 2, 50, 50, 0, 0, 0, 0),
(680, 0, 'AK-47 Infantry', 'AK-47 Infantry', 'Infantry', 1, 6, 81, 3, 1, 2, 50, 50, 0, 0, 0, 0),
(681, 0, 'AK-47 Infantry', 'AK-47 Infantry', 'Infantry', 1, 6, 81, 3, 1, 2, 50, 50, 0, 0, 0, 0),
(653, 0, 'Black Horn', 'Black Horn', 'Infantry', 1, 4, 79, 4, 1, 4, 50, 50, 0, 0, 0, 0),
(662, 0, 'Black Horn', 'Black Horn', 'Infantry', 1, 4, 79, 4, 1, 4, 50, 50, 0, 0, 0, 0),
(661, 0, 'Black Horn', 'Black Horn', 'Infantry', 1, 4, 79, 4, 1, 4, 50, 50, 0, 0, 0, 0),
(660, 0, 'Black Horn', 'Black Horn', 'Infantry', 1, 4, 79, 4, 1, 4, 50, 50, 0, 0, 0, 0),
(659, 0, 'Black Horn', 'Black Horn', 'Infantry', 1, 4, 79, 4, 1, 4, 50, 50, 0, 0, 0, 0),
(658, 0, 'Black Horn', 'Black Horn', 'Infantry', 1, 4, 79, 4, 1, 4, 50, 50, 0, 0, 0, 0),
(657, 0, 'Black Horn', 'Black Horn', 'Infantry', 1, 4, 79, 4, 1, 4, 50, 50, 0, 0, 0, 0),
(656, 0, 'Black Horn', 'Black Horn', 'Infantry', 1, 4, 79, 4, 1, 4, 50, 50, 0, 0, 0, 0),
(655, 0, 'Black Horn', 'Black Horn', 'Infantry', 1, 4, 79, 4, 1, 4, 50, 50, 0, 0, 0, 0),
(654, 0, 'Black Horn', 'Black Horn', 'Infantry', 1, 4, 79, 4, 1, 4, 50, 50, 0, 0, 0, 0),
(652, 0, 'Black Horn', 'Black Horn', 'Infantry', 1, 4, 79, 4, 1, 4, 50, 50, 0, 0, 0, 0),
(651, 0, 'Black Horn', 'Black Horn', 'Infantry', 1, 4, 79, 4, 1, 4, 50, 50, 0, 0, 0, 0),
(650, 0, 'Black Horn', 'Black Horn', 'Infantry', 1, 4, 79, 4, 1, 4, 50, 50, 0, 0, 0, 0),
(649, 0, 'Black Horn', 'Black Horn', 'Infantry', 1, 4, 79, 4, 1, 4, 50, 50, 0, 0, 0, 0),
(648, 0, 'Black Horn', 'Black Horn', 'Infantry', 1, 8, 78, 4, 1, 4, 50, 25, 0, 0, 0, 0),
(647, 0, 'Black Horn', 'Black Horn', 'Infantry', 1, 8, 78, 4, 1, 4, 50, 25, 0, 0, 0, 0),
(646, 0, 'Black Horn', 'Black Horn', 'Infantry', 1, 8, 78, 4, 1, 4, 50, 25, 0, 0, 0, 0),
(645, 0, 'Black Horn', 'Black Horn', 'Infantry', 1, 8, 78, 4, 1, 4, 50, 25, 0, 0, 0, 0),
(644, 0, 'Black Horn', 'Black Horn', 'Infantry', 1, 8, 78, 4, 1, 4, 50, 25, 0, 0, 0, 0),
(643, 0, 'Black Horn', 'Black Horn', 'Infantry', 1, 8, 78, 4, 1, 4, 50, 25, 0, 0, 0, 0),
(642, 0, 'Black Horn', 'Black Horn', 'Infantry', 1, 8, 78, 4, 1, 4, 50, 25, 0, 0, 0, 0),
(641, 0, 'Black Horn', 'Black Horn', 'Infantry', 1, 8, 78, 4, 1, 4, 50, 25, 0, 0, 0, 0),
(640, 0, 'Black Horn', 'Black Horn', 'Infantry', 1, 8, 78, 4, 1, 4, 50, 25, 0, 0, 0, 0),
(639, 0, 'Black Horn', 'Black Horn', 'Infantry', 1, 8, 78, 4, 1, 4, 50, 25, 0, 0, 0, 0),
(638, 0, 'Black Horn', 'Black Horn', 'Infantry', 1, 8, 78, 4, 1, 4, 50, 25, 0, 0, 0, 0),
(637, 0, 'Black Horn', 'Black Horn', 'Infantry', 1, 8, 78, 4, 1, 4, 50, 25, 0, 0, 0, 0),
(636, 0, 'RPG Infantry', 'RPG Infantry', 'Infantry', 1, 0, 77, 3, 1, 2, 50, 50, 0, 0, 0, 0),
(635, 0, 'RPG Infantry', 'RPG Infantry', 'Infantry', 1, 0, 77, 3, 1, 2, 50, 50, 0, 0, 0, 0),
(634, 0, 'RPG Infantry', 'RPG Infantry', 'Infantry', 1, 0, 77, 3, 1, 2, 50, 50, 0, 0, 0, 0),
(633, 0, 'RPG Infantry', 'RPG Infantry', 'Infantry', 1, 0, 77, 3, 1, 2, 50, 50, 0, 0, 0, 0),
(632, 0, 'AK-47 Infantry', 'AK-47 Infantry', 'Infantry', 1, 0, 77, 3, 1, 2, 50, 50, 0, 0, 0, 0),
(631, 0, 'AK-47 Infantry', 'AK-47 Infantry', 'Infantry', 1, 0, 77, 3, 1, 2, 50, 50, 0, 0, 0, 0),
(630, 0, 'AK-47 Infantry', 'AK-47 Infantry', 'Infantry', 1, 0, 77, 3, 1, 2, 50, 50, 0, 0, 0, 0),
(629, 0, 'AK-47 Infantry', 'AK-47 Infantry', 'Infantry', 1, 0, 77, 3, 1, 2, 50, 50, 0, 0, 0, 0),
(628, 0, 'AK-47 Infantry', 'AK-47 Infantry', 'Infantry', 1, 0, 77, 3, 1, 2, 50, 50, 0, 0, 0, 0),
(627, 0, 'AK-47 Infantry', 'AK-47 Infantry', 'Infantry', 1, 0, 77, 3, 1, 2, 50, 50, 0, 0, 0, 0),
(626, 0, 'AK-47 Infantry', 'AK-47 Infantry', 'Infantry', 1, 0, 77, 3, 1, 2, 50, 50, 0, 0, 0, 0),
(625, 0, 'AK-47 Infantry', 'AK-47 Infantry', 'Infantry', 1, 0, 77, 3, 1, 2, 50, 50, 0, 0, 0, 0),
(624, 0, 'AK-47 Infantry', 'AK-47 Infantry', 'Infantry', 1, 0, 77, 3, 1, 2, 50, 50, 0, 0, 0, 0),
(586, 0, 'Medic', 'Medic', 'Infantry', 1, 4, 74, 1, 1, 2, 50, 50, 0, 0, 0, 0),
(587, 0, 'Medic', 'Medic', 'Infantry', 1, 4, 74, 1, 1, 2, 50, 50, 0, 0, 0, 0),
(588, 0, 'National Guard', 'National Guard', 'Infantry', 1, 4, 74, 2, 1, 1, 50, 50, 0, 0, 0, 0),
(589, 0, 'National Guard', 'National Guard', 'Infantry', 1, 4, 74, 2, 1, 1, 50, 50, 0, 0, 0, 0),
(590, 0, 'National Guard', 'National Guard', 'Infantry', 1, 4, 74, 2, 1, 1, 50, 50, 0, 0, 0, 0),
(591, 0, 'National Guard', 'National Guard', 'Infantry', 1, 4, 74, 2, 1, 1, 50, 50, 0, 0, 0, 0),
(592, 0, 'National Guard', 'National Guard', 'Infantry', 1, 4, 74, 2, 1, 1, 50, 50, 0, 0, 0, 0),
(593, 0, 'National Guard', 'National Guard', 'Infantry', 1, 4, 74, 2, 1, 1, 50, 50, 0, 0, 0, 0),
(594, 0, 'National Guard', 'National Guard', 'Infantry', 1, 4, 74, 2, 1, 1, 50, 50, 0, 0, 0, 0),
(595, 0, 'National Guard', 'National Guard', 'Infantry', 1, 4, 74, 2, 1, 1, 50, 50, 0, 0, 0, 0),
(596, 0, 'National Guard', 'National Guard', 'Infantry', 1, 4, 74, 2, 1, 1, 50, 50, 0, 0, 0, 0),
(597, 0, 'National Guard', 'National Guard', 'Infantry', 1, 4, 74, 2, 1, 1, 50, 50, 0, 0, 0, 0),
(598, 0, 'Medic', 'Medic', 'Infantry', 1, 0, 75, 1, 1, 2, 50, 50, 0, 0, 0, 0),
(599, 0, 'Medic', 'Medic', 'Infantry', 1, 0, 75, 1, 1, 2, 50, 50, 0, 0, 0, 0),
(600, 0, 'National Guard', 'National Guard', 'Infantry', 1, 0, 75, 2, 1, 1, 50, 50, 0, 0, 0, 0),
(601, 0, 'National Guard', 'National Guard', 'Infantry', 1, 0, 75, 2, 1, 1, 50, 50, 0, 0, 0, 0),
(602, 0, 'National Guard', 'National Guard', 'Infantry', 1, 0, 75, 2, 1, 1, 50, 50, 0, 0, 0, 0),
(603, 0, 'National Guard', 'National Guard', 'Infantry', 1, 0, 75, 2, 1, 1, 50, 50, 0, 0, 0, 0),
(604, 0, 'National Guard', 'National Guard', 'Infantry', 1, 0, 75, 2, 1, 1, 50, 50, 0, 0, 0, 0),
(605, 0, 'National Guard', 'National Guard', 'Infantry', 1, 0, 75, 2, 1, 1, 50, 50, 0, 0, 0, 0),
(606, 0, 'National Guard', 'National Guard', 'Infantry', 1, 0, 75, 2, 1, 1, 50, 50, 0, 0, 0, 0),
(607, 0, 'National Guard', 'National Guard', 'Infantry', 1, 0, 75, 2, 1, 1, 50, 50, 0, 0, 0, 0),
(608, 0, 'National Guard', 'National Guard', 'Infantry', 1, 0, 75, 2, 1, 1, 50, 28, 0, 0, 0, 0),
(609, 0, 'National Guard', 'National Guard', 'Infantry', 1, 0, 75, 2, 1, 1, 50, 22, 0, 0, 0, 0),
(610, 0, 'National Guard', 'National Guard', 'Infantry', 1, 0, 75, 2, 1, 1, 50, 50, 0, 0, 0, 0),
(611, 0, 'Medic', 'Medic', 'Infantry', 1, 0, 76, 1, 1, 2, 50, 50, 0, 0, 0, 0),
(612, 0, 'National Guard', 'National Guard', 'Infantry', 1, 0, 76, 2, 1, 1, 50, 50, 0, 0, 0, 0),
(613, 0, 'National Guard', 'National Guard', 'Infantry', 1, 0, 76, 2, 1, 1, 50, 50, 0, 0, 0, 0),
(614, 0, 'National Guard', 'National Guard', 'Infantry', 1, 0, 76, 2, 1, 1, 50, 50, 0, 0, 0, 0),
(615, 0, 'National Guard', 'National Guard', 'Infantry', 1, 0, 76, 2, 1, 1, 50, 50, 0, 0, 0, 0),
(616, 0, 'National Guard', 'National Guard', 'Infantry', 1, 0, 76, 2, 1, 1, 50, 50, 0, 0, 0, 0),
(617, 0, 'National Guard', 'National Guard', 'Infantry', 1, 0, 76, 2, 1, 1, 50, 50, 0, 0, 0, 0),
(618, 0, 'National Guard', 'National Guard', 'Infantry', 1, 0, 76, 2, 1, 1, 50, 50, 0, 0, 0, 0),
(619, 0, 'National Guard', 'National Guard', 'Infantry', 1, 0, 76, 2, 1, 1, 50, 23, 0, 0, 0, 0),
(620, 0, 'National Guard', 'National Guard', 'Infantry', 1, 0, 76, 2, 1, 1, 50, 50, 0, 0, 0, 0),
(621, 0, 'National Guard', 'National Guard', 'Infantry', 1, 0, 76, 2, 1, 1, 50, 50, 0, 0, 0, 0),
(622, 0, 'National Guard', 'National Guard', 'Infantry', 1, 0, 76, 2, 1, 1, 50, 50, 0, 0, 0, 0),
(623, 0, 'National Guard', 'National Guard', 'Infantry', 1, 0, 76, 2, 1, 1, 50, 50, 0, 0, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `unit_queue`
--

DROP TABLE IF EXISTS `unit_queue`;
CREATE TABLE IF NOT EXISTS `unit_queue` (
  `queue_position` int NOT NULL AUTO_INCREMENT,
  `id` int NOT NULL,
  `unit_type` varchar(255) NOT NULL,
  `minutes_left` int NOT NULL,
  PRIMARY KEY (`queue_position`)
) ENGINE=MyISAM AUTO_INCREMENT=55 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `unit_queue`
--

INSERT INTO `unit_queue` (`queue_position`, `id`, `unit_type`, `minutes_left`) VALUES
(54, 7, 'infantry', 40),
(53, 7, 'infantry', 40),
(52, 7, 'infantry', 40);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `country_name` varchar(30) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `leader_name` varchar(30) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `email` varchar(100) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `password` char(60) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `population` int NOT NULL DEFAULT '50000',
  `tier` int NOT NULL DEFAULT '1',
  `gp` int NOT NULL,
  `flag` varchar(255) CHARACTER SET ascii COLLATE ascii_bin NOT NULL DEFAULT 'https://i.ibb.co/P9D2Wr7/a2a2qfnvhkf71.jpg',
  `description` text NOT NULL,
  `alliance_id` int NOT NULL DEFAULT '0',
  `continent` varchar(255) DEFAULT NULL,
  `creationDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `country_name`, `leader_name`, `email`, `password`, `population`, `tier`, `gp`, `flag`, `description`, `alliance_id`, `continent`, `creationDate`) VALUES
(7, 'Rason', 'Justin Cheng', 'justincheng432@gmail.com', '$2y$10$Y1PCDJY66K0VGBx.suAQCeGemojplwBGm7zSsGpMoz6U2Vn1UuXIG', 81764, 2, 305, 'https://i.ibb.co/mXg13rh/Flagof-Rason.webp', 'Rason is a country led by Justin Cheng', 1, 'zaheria', '2024-10-21 13:12:41'),
(8, 'Rason 2', 'Justin Cheng 2', 'jerrycui07@gmail.com', '$2y$10$P0ufsZwCedQSKu4NEYW8ceHvDdHXeQWoIXBuVf34yHMSfW25PU1IC', 59239, 1, 75, 'https://i.ibb.co/P9D2Wr7/a2a2qfnvhkf71.jpg', '', 1, NULL, '2024-10-27 19:19:29'),
(9, 'Rason 3', 'Justin Cheng 3', 'aef@AW.cpm', '$2y$10$uVO4zalJBTxlIRWj7OJM4OWKsCEfoBxC3yjpbr.3KuVqEZ.IFxCHO', 55520, 1, 71, 'https://i.ibb.co/P9D2Wr7/a2a2qfnvhkf71.jpg', '', 0, 'tind', '2024-11-14 19:39:55');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

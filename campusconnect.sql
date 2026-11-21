-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:8889
-- Generation Time: Nov 16, 2026 at 01:49 PM
-- Server version: 8.0.44
-- PHP Version: 8.3.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `campusconnect`
--

-- --------------------------------------------------------

--
-- Table structure for table `registrations`
--

CREATE TABLE `registrations` (
  `registration_id` int NOT NULL,
  `user_id` int NOT NULL,
  `session_id` int NOT NULL,
  `registered_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `registrations`
--

INSERT INTO `registrations` (`registration_id`, `user_id`, `session_id`, `registered_at`) VALUES
(1, 4, 1, '2026-11-09 10:45:40'),
(3, 1, 6, '2026-11-12 14:33:27'),
(4, 1, 4, '2026-11-12 14:33:40');

-- --------------------------------------------------------

--
-- Table structure for table `skill_sessions`
--

CREATE TABLE `skill_sessions` (
  `session_id` int NOT NULL,
  `creator_id` int NOT NULL,
  `title` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `category` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `description` text COLLATE utf8mb4_general_ci NOT NULL,
  `session_date` date NOT NULL,
  `session_time` time NOT NULL,
  `location` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `capacity` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `skill_sessions`
--

INSERT INTO `skill_sessions` (`session_id`, `creator_id`, `title`, `category`, `description`, `session_date`, `session_time`, `location`, `capacity`) VALUES
(1, 1, 'Canva Design Basics', 'Design', 'Learn Canva basics and simple layout techniques.', '2027-01-09', '14:00:00', 'Student Hub', 8),
(3, 1, 'UI Sketching Workshop', 'Design', 'Practice quick UI sketching techniques and learn how to turn simple ideas into clear mobile app screen layouts.', '2026-12-12', '11:00:00', 'Design Studio', 12),
(4, 5, 'Photography Walk', 'Creative', 'Explore Taylor’s Lakeside Campus while learning simple composition, lighting, and mobile photography techniques.', '2026-12-08', '16:00:00', 'Taylor’s Lakeside Campus', 12),
(5, 5, 'Build Your First Portfolio', 'Career', 'Learn how to organise your projects and create a simple portfolio that presents your skills and experience clearly.', '2026-12-10', '13:00:00', 'Library Discussion Room', 15),
(6, 6, 'Python for Beginners', 'Technology', 'A beginner-friendly introduction to Python covering variables, conditions, loops, and simple programs.', '2026-12-05', '15:00:00', 'Computer Lab 2', 20),
(7, 6, 'Excel for Student Projects', 'Technology', 'Learn useful Excel skills including formulas, tables, sorting, filtering, and organising data for university projects.', '2026-12-15', '14:30:00', 'Computer Lab 1', 20);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `role` varchar(20) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'student'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `email`, `password`, `role`) VALUES
(1, 'Alex Tan', 'alex@example.com', '$2y$10$i2d/KbcCnH06Gm6MLt/c/uBQ4n/sfdQ9errqqBRacOlyd5RNzaN7a', 'student'),
(3, 'test', 'test@example.com', '$2y$10$36vPVEEQhgT0WDQU7u9c3.pRnfMpO/KKkwWT7s1iEnq14D/FTxEcG', 'student'),
(4, 'dongjiuyi', 'dongjiuyi@example.com', '$2y$10$i16Ruy6G4/5h/x05iepIJewXTgMUlQHoHojmhXVxQZOVMjG4DcmQi', 'student'),
(5, 'Sarah Lim', 'sarah@example.com', '$2y$10$F2/bihhhU/Gv7A.RbuoWHuL./bZqOhip6qen3Sf2qTSE/nc2RCgUG', 'student'),
(6, 'Jamie Lee', 'jamie@example.com', '$2y$10$WRSTJyt4qt1o7J5yyh5.MeCo9PJHVGb1VmtumxIibPr1B5pGZt9Gu', 'student');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `registrations`
--
ALTER TABLE `registrations`
  ADD PRIMARY KEY (`registration_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `session_id` (`session_id`);

--
-- Indexes for table `skill_sessions`
--
ALTER TABLE `skill_sessions`
  ADD PRIMARY KEY (`session_id`),
  ADD KEY `creator_id` (`creator_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `registrations`
--
ALTER TABLE `registrations`
  MODIFY `registration_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `skill_sessions`
--
ALTER TABLE `skill_sessions`
  MODIFY `session_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `registrations`
--
ALTER TABLE `registrations`
  ADD CONSTRAINT `    fk_registrations_session` FOREIGN KEY (`session_id`) REFERENCES `skill_sessions` (`session_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `    fk_registrations_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `skill_sessions`
--
ALTER TABLE `skill_sessions`
  ADD CONSTRAINT `fk_skill_sessions_creator` FOREIGN KEY (`creator_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

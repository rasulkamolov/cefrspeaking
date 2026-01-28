-- database/schema.sql
-- This script sets up the MySQL database schema and seeds initial data.
-- You can import this file directly into phpMyAdmin.

SET FOREIGN_KEY_CHECKS = 0;

-- Drop tables if they exist
DROP TABLE IF EXISTS `grades`;
DROP TABLE IF EXISTS `submission_answers`;
DROP TABLE IF EXISTS `submissions`;
DROP TABLE IF EXISTS `test_questions`;
DROP TABLE IF EXISTS `tests`;
DROP TABLE IF EXISTS `users`;

SET FOREIGN_KEY_CHECKS = 1;

-- Create Users Table
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('student','admin') NOT NULL DEFAULT 'student',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create Tests Table
CREATE TABLE `tests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create Test Questions Table
CREATE TABLE `test_questions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `test_id` int(11) NOT NULL,
  `part_type` varchar(50) NOT NULL,
  `content` text,
  `media_url` text,
  `media_url_2` text,
  `sequence` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `test_id` (`test_id`),
  CONSTRAINT `test_questions_ibfk_1` FOREIGN KEY (`test_id`) REFERENCES `tests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create Submissions Table
CREATE TABLE `submissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `test_id` int(11) NOT NULL,
  `status` varchar(50) DEFAULT 'in_progress',
  `started_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `completed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `test_id` (`test_id`),
  CONSTRAINT `submissions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `submissions_ibfk_2` FOREIGN KEY (`test_id`) REFERENCES `tests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create Submission Answers Table
CREATE TABLE `submission_answers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `submission_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `audio_path` text NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `submission_id` (`submission_id`),
  KEY `question_id` (`question_id`),
  CONSTRAINT `submission_answers_ibfk_1` FOREIGN KEY (`submission_id`) REFERENCES `submissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `submission_answers_ibfk_2` FOREIGN KEY (`question_id`) REFERENCES `test_questions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create Grades Table
CREATE TABLE `grades` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `submission_id` int(11) NOT NULL,
  `range_score` int(11) DEFAULT NULL CHECK (`range_score` between 0 and 6),
  `accuracy_score` int(11) DEFAULT NULL CHECK (`accuracy_score` between 0 and 6),
  `fluency_score` int(11) DEFAULT NULL CHECK (`fluency_score` between 0 and 6),
  `coherence_score` int(11) DEFAULT NULL CHECK (`coherence_score` between 0 and 6),
  `comments` text,
  `graded_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `submission_id` (`submission_id`),
  CONSTRAINT `grades_ibfk_1` FOREIGN KEY (`submission_id`) REFERENCES `submissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `role`) VALUES
(1, 'admin@jules.com', '$2y$10$c66ifsPxw6E6Lmp9P2uvIuDG.I5THyoj0wVJSzTe2Qa3VdZt2ZAIS', 'admin'),
(2, 'student@jules.com', '$2y$10$vEcgdsOFik6PshzgiahSMO1aBozuTCqg4lP45JyIJKuo16hNfqWgq', 'student');

--
-- Dumping data for table `tests`
--

INSERT INTO `tests` (`id`, `title`, `description`) VALUES
(1, 'CEFR Mock Exam 1', 'A standard mock exam covering A1-C1 levels.');

--
-- Dumping data for table `test_questions`
--

INSERT INTO `test_questions` (`test_id`, `part_type`, `content`, `media_url`, `media_url_2`, `sequence`) VALUES
(1, '1.1', 'Tell me about your hometown.', NULL, NULL, 1),
(1, '1.1', 'Who is your best friend and why?', NULL, NULL, 2),
(1, '1.1', 'What are your hobbies?', NULL, NULL, 3),
(1, '1.2', 'Compare these two pictures.', 'https://picsum.photos/seed/setup1/600/400', 'https://picsum.photos/seed/setup2/600/400', 1),
(1, '1.2', 'Which mode of transport do you prefer?', 'https://picsum.photos/seed/setup1/600/400', 'https://picsum.photos/seed/setup2/600/400', 2),
(1, '1.2', 'Why is it important to travel?', 'https://picsum.photos/seed/setup1/600/400', 'https://picsum.photos/seed/setup2/600/400', 3),
(1, '2', '{\"topic\":\"Critical Decisions\",\"points\":[\"What the decision was\",\"Why it was critical\",\"How you felt\",\"The outcome\"]}', 'https://picsum.photos/seed/setup3/600/400', NULL, 1),
(1, '3', '{\"topic\":\"Gun Control\",\"for_prompts\":[\"Self-defense is a right\",\"Deterrence against crime\"],\"against_prompts\":[\"Higher accident rates\",\"Escalation of violence\"]}', NULL, NULL, 1);

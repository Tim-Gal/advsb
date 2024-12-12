-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Anamakine: 127.0.0.1
-- Üretim Zamanı: 11 Ara 2024, 05:04:32
-- Sunucu sürümü: 10.4.32-MariaDB
-- PHP Sürümü: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


CREATE TABLE `degrees` (
  `degree_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `type` enum('Major','Minor') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


INSERT INTO `degrees` (`degree_id`, `name`, `type`) VALUES
(1, 'Computer Science', 'Major'),
(2, 'Computer Science', 'Minor'),

(3, 'Biology', 'Major'),
(4, 'Biology', 'Minor'),

(5, 'Political Science', 'Major'),
(6, 'Political Science', 'Minor'),

(7, 'Economics', 'Major'),
(8, 'Economics', 'Minor'),

(9, 'Philosophy', 'Major'),
(10, 'Philosophy', 'Minor'),

(11, 'English', 'Major'),
(12, 'English', 'Minor');


CREATE TABLE `courses` (
  `course_code` varchar(50) NOT NULL,
  `course_name` varchar(255) NOT NULL,
  `course_description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


INSERT INTO `courses` (`course_code`, `course_name`, `course_description`) VALUES
('COMP101', 'Introduction to Programming', 'Learn the basics of programming and algorithms.'),
('COMP102', 'Foundations of Computing', 'Introduction to computer systems and logic.'),
('COMP201', 'Data Structures', 'Study structures for efficient data management.'),
('COMP202', 'Web Development Basics', 'Build interactive websites with modern tools.'),
('COMP301', 'Artificial Intelligence Fundamentals', 'Explore the basics of AI and machine learning.'),
('COMP302', 'Operating Systems', 'Understand OS concepts like memory and processes.'),

('BIOL101', 'Introduction to Biology', 'Explore foundational biology principles and systems.'),
('BIOL102', 'Cellular Biology', 'Study cells, organelles, and basic mechanisms.'),
('BIOL201', 'Genetics', 'Learn principles of heredity and DNA science.'),
('BIOL202', 'Ecology Basics', 'Understand ecosystems and species interactions.'),
('BIOL301', 'Human Physiology', 'Study the systems of the human body.'),
('BIOL302', 'Microbiology', 'Learn about microorganisms and their environments.'),

('POLI101', 'Introduction to Politics', 'Understand political systems and their foundations.'),
('POLI102', 'Global Governance', 'Explore how international organizations function.'),
('POLI201', 'Public Policy', 'Analyze the creation and impact of policies.'),
('POLI202', 'Political Theory', 'Study influential thinkers and political ideas.'),
('POLI301', 'Comparative Politics', 'Compare political systems across nations.'),
('POLI302', 'International Relations', 'Learn the dynamics of global political interactions.'),

('ECON101', 'Principles of Microeconomics', 'Study decision-making in households and firms.'),
('ECON102', 'Principles of Macroeconomics', 'Explore national economies and global trends.'),
('ECON201', 'Intermediate Microeconomics', 'Analyze market behaviors with advanced models.'),
('ECON202', 'Econometrics', 'Apply statistics to analyze economic data.'),
('ECON301', 'Development Economics', 'Understand economic growth in developing nations.'),
('ECON302', 'Behavioral Economics', 'Study psychological factors in economic decisions.'),

('PHIL101', 'Introduction to Philosophy', 'Explore fundamental philosophical questions and ideas.'),
('PHIL102', 'Critical Thinking', 'Develop reasoning and argument analysis skills.'),
('PHIL201', 'Ethics', 'Study moral theories and their applications.'),
('PHIL202', 'Philosophy of Science', 'Analyze scientific methods and their implications.'),
('PHIL301', 'Metaphysics', 'Explore reality, existence, and the nature of being.'),
('PHIL302', 'Philosophy of Mind', 'Study consciousness, cognition, and the self.');


CREATE TABLE `degree_courses` (
  `id` int(11) NOT NULL,
  `degree_id` int(11) NOT NULL,
  `course_code` varchar(50) NOT NULL,
  `course_type` enum('required','elective') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


INSERT INTO `degree_courses` (`id`, `degree_id`, `course_code`, `course_type`) VALUES
(1,  1, 'COMP101', 'required'),
(2,  1, 'COMP102', 'required'),
(3,  1, 'COMP201', 'required'),
(4,  1, 'COMP202', 'required'),
(5,  1, 'COMP301', 'required'),
(6,  1, 'COMP302', 'required'),

(7,  3, 'BIOL101', 'required'),
(8,  3, 'BIOL102', 'required'),
(9,  3, 'BIOL201', 'required'),
(10, 3, 'BIOL202', 'required'),
(11, 3, 'BIOL301', 'required'),
(12, 3, 'BIOL302', 'required'),

(13, 5, 'POLI101', 'required'),
(14, 5, 'POLI102', 'required'),
(15, 5, 'POLI101', 'required'),
(16, 5, 'POLI102', 'required'),
(17, 5, 'POLI101', 'required'),
(18, 5, 'POLI102', 'required');


CREATE TABLE `sections` (
  `section_code` int(11) NOT NULL,
  `semester` varchar(11) NOT NULL,
  `professor` varchar(100) DEFAULT NULL,
  `course_code` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


INSERT INTO `sections` (`section_code`, `semester`, `professor`, `course_code`) VALUES
(1, 'FALL',   'Don Miller', 'COMP101'),
(2, 'FALL',   'Don Miller', 'COMP102'),
(3, 'WINTER', 'Don Miller', 'COMP201'),
(4, 'WINTER', 'Don Miller', 'COMP202'),

(5, 'FALL',   'John Green', 'BIOL101'),
(6, 'FALL',   'John Green', 'BIOL102'),
(7, 'WINTER', 'John Green', 'BIOL201'),
(8, 'WINTER', 'John Green', 'BIOL202');


CREATE TABLE `lectures` (
  `lecture_id` int(11) NOT NULL,
  `location` varchar(100) DEFAULT NULL,
  `day_of_week` enum('Mon','Tue','Wed','Thu','Fri') NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `section_code` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


INSERT INTO `lectures` (`lecture_id`, `location`, `day_of_week`, `start_time`, `end_time`, `section_code`) VALUES
(11, 'Room A', 'Mon', '10:00:00', '11:00:00', 1), /* COMP101 - Fall */
(12, 'Room A', 'Wed', '10:00:00', '11:00:00', 1), /* COMP101 - Fall */

(21, 'Room A', 'Tue', '12:00:00', '13:00:00', 2), /* COMP102 - Fall */
(22, 'Room A', 'Thu', '12:00:00', '13:00:00', 2), /* COMP102 - Fall */

(31, 'Room A', 'Mon', '14:00:00', '15:00:00', 3), /* COMP201 - Winter */
(32, 'Room A', 'Wed', '14:00:00', '15:00:00', 3), /* COMP201 - Winter */

(41, 'Room A', 'Tue', '12:00:00', '13:00:00', 4), /* COMP202 - Winter */
(42, 'Room A', 'Thu', '12:00:00', '13:00:00', 4), /* COMP202 - Winter */

(51, 'Room A', 'Mon', '11:00:00', '12:00:00', 5), /* BIOL101 - Fall */
(52, 'Room A', 'Wed', '11:00:00', '12:00:00', 5), /* BIOL101 - Fall */

(61, 'Room A', 'Tue', '13:00:00', '14:00:00', 6), /* BIOL102 - Fall */
(62, 'Room A', 'Thu', '13:00:00', '14:00:00', 6), /* BIOL102 - Fall */

(71, 'Room A', 'Mon', '15:00:00', '16:00:00', 7), /* BIOL201 - Winter */
(72, 'Room A', 'Wed', '15:00:00', '16:00:00', 7), /* BIOL201 - Winter */

(81, 'Room A', 'Tue', '13:00:00', '14:00:00', 8), /* BIOL202 - Winter */
(82, 'Room A', 'Thu', '13:00:00', '14:00:00', 8); /* BIOL202 - Winter */


CREATE TABLE `prerequisiteof` (
  `course_code` varchar(50) NOT NULL,
  `prerequisite_course_code` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


INSERT INTO `prerequisiteof` (`course_code`, `prerequisite_course_code`) VALUES
('COMP201', 'COMP101'),
('COMP202', 'COMP102'),
('COMP301', 'COMP201'),
('COMP302', 'COMP202'),

('BIOL201', 'BIOL101'),
('BIOL202', 'BIOL102'),
('BIOL301', 'BIOL201'),
('BIOL302', 'BIOL202');


CREATE TABLE `students` (
  `student_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `fname` varchar(100) DEFAULT NULL,
  `lname` varchar(100) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT 0,
  `verification_code` varchar(100) DEFAULT NULL,
  `password_change_code` varchar(6) DEFAULT NULL,
  `major_id` int(11) NOT NULL,
  `minor_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


INSERT INTO `students` (`student_id`, `username`, `fname`, `lname`, `email`, `password_hash`, `remember_token`, `is_verified`, `verification_code`, `password_change_code`, `major_id`, `minor_id`) VALUES
(1,  'jwood1', 'John', 'Wood', 'alice.smith@university.edu', '$2y$10$QUvS4QATistNhdFM2qTf7u2cUfphR9LGbowI8Y2ZIf8rrKvBeM9C6', NULL, 0, NULL, NULL, 1, NULL),
(2,  'eertugrul2', 'Efe', 'Ertugrul', 'efertugrul6@gmail.com', '$2y$10$gv9ASeGqmEmrDPF5omkJwOOpkPsMBY4Gmmr4k8IfWb64S.GhZn6xO', NULL, 1, NULL, '651008', 1, NULL),
(3,  'mreed3', 'Mike', 'Reed', 'diana.prince@university.edu', NULL, NULL, 0, NULL, NULL, 1, NULL),
(4,  'dwest4', 'David', 'West', 'edward.norton@university.edu', NULL, NULL, 0, NULL, NULL, 1, NULL),
(5,  'tpage5', 'Tim', 'Page', 'timoxa.gal@gmail.com', '$2y$10$MS0jyLdIHDcy.U3PDi3kOuqXjKwxgKUArBfBa2t3dswC2GopSdo4G', NULL, 1, NULL, NULL, 1, NULL),
(6,  'fsnow6', 'Frank', 'Snow', 'george.clooney@university.edu', NULL, NULL, 0, NULL, NULL, 1, NULL),
(7,  'bgray7', 'Ben', 'Gray', 'hannah.montana@university.edu', NULL, NULL, 0, NULL, NULL, 1, NULL),
(8,  'mlane8', 'Mark', 'Lane', 'ian.mckellen@university.edu', NULL, NULL, 0, NULL, NULL, 1, NULL),
(9,  'pford9', 'Paul', 'Ford', 'julia.roberts@university.edu', NULL, NULL, 0, NULL, NULL, 1, NULL),
(10, 'Efstarisback10', NULL, NULL, 'ali.ertugrul@mail.mcgill.ca', '$2y$10$HDE78iT/SwIM7dCR3huFGeID9t/bQ.txDxOpSO7SX7zcC6YBIypjm', NULL, 1, NULL, NULL, 1, NULL);


CREATE TABLE `coursescompleted` (
  `student_id` int(11) NOT NULL,
  `course_code` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


INSERT INTO `coursescompleted` (`student_id`, `course_code`) VALUES
(1,  'COMP101'),
(2,  'COMP101'),
(3,  'COMP101'),
(4,  'COMP101'),
(5,  'COMP101'),
(6,  'COMP101'),
(7,  'COMP101'),
(8,  'COMP101'),
(9,  'COMP101'),
(10, 'COMP101');


CREATE TABLE `coursesenrolled` (
  `student_id` int(11) NOT NULL,
  `section_code` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


INSERT INTO `coursesenrolled` (`student_id`, `section_code`) VALUES
(1,  5),
(3,  5),
(4,  5),
(5,  5),
(6,  5),
(7,  5),
(8,  5),
(9,  5),
(10, 5);




CREATE TABLE `friendrequests` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `status` enum('pending','accepted','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `friendswith` (
  `id` int(11) NOT NULL,
  `student_id1` int(11) NOT NULL,
  `student_id2` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;




ALTER TABLE `courses`
  ADD PRIMARY KEY (`course_code`);


ALTER TABLE `coursescompleted`
  ADD PRIMARY KEY (`student_id`,`course_code`),
  ADD KEY `fk_completed_course` (`course_code`);


ALTER TABLE `coursesenrolled`
  ADD PRIMARY KEY (`student_id`,`section_code`),
  ADD KEY `fk_enrolled_section` (`section_code`);


ALTER TABLE `degrees`
  ADD PRIMARY KEY (`degree_id`),
  ADD UNIQUE KEY `unique_degree` (`name`,`type`);


ALTER TABLE `degree_courses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_degree_idx` (`degree_id`),
  ADD KEY `fk_course_idx` (`course_code`);


ALTER TABLE `friendrequests`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_request` (`sender_id`,`receiver_id`),
  ADD KEY `receiver_id` (`receiver_id`);


ALTER TABLE `friendswith`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_friendship` (`student_id1`,`student_id2`),
  ADD KEY `student_id2` (`student_id2`);


ALTER TABLE `lectures`
  ADD PRIMARY KEY (`lecture_id`),
  ADD KEY `fk_section_code` (`section_code`);


ALTER TABLE `prerequisiteof`
  ADD PRIMARY KEY (`course_code`,`prerequisite_course_code`),
  ADD KEY `fk_prerequisite` (`prerequisite_course_code`);


ALTER TABLE `sections`
  ADD PRIMARY KEY (`section_code`),
  ADD KEY `fk_course_code` (`course_code`);


ALTER TABLE `students`
  ADD PRIMARY KEY (`student_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `username_2` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `fk_major` (`major_id`),
  ADD KEY `fk_minor` (`minor_id`);


ALTER TABLE `degrees`
  MODIFY `degree_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;


ALTER TABLE `degree_courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;


ALTER TABLE `friendrequests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;


ALTER TABLE `friendswith`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;


ALTER TABLE `lectures`
  MODIFY `lecture_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9873;


ALTER TABLE `sections`
  MODIFY `section_code` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8302;


ALTER TABLE `students`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;


ALTER TABLE `coursescompleted`
  ADD CONSTRAINT `fk_completed_course` FOREIGN KEY (`course_code`) REFERENCES `courses` (`course_code`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_completed_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE;


ALTER TABLE `coursesenrolled`
  ADD CONSTRAINT `fk_enrolled_section` FOREIGN KEY (`section_code`) REFERENCES `sections` (`section_code`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_enrolled_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE;


ALTER TABLE `degree_courses`
  ADD CONSTRAINT `fk_course` FOREIGN KEY (`course_code`) REFERENCES `courses` (`course_code`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_degree` FOREIGN KEY (`degree_id`) REFERENCES `degrees` (`degree_id`) ON DELETE CASCADE ON UPDATE CASCADE;


ALTER TABLE `friendrequests`
  ADD CONSTRAINT `friendrequests_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `friendrequests_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE;


ALTER TABLE `friendswith`
  ADD CONSTRAINT `friendswith_ibfk_1` FOREIGN KEY (`student_id1`) REFERENCES `students` (`student_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `friendswith_ibfk_2` FOREIGN KEY (`student_id2`) REFERENCES `students` (`student_id`) ON DELETE CASCADE;


ALTER TABLE `lectures`
  ADD CONSTRAINT `fk_section_code` FOREIGN KEY (`section_code`) REFERENCES `sections` (`section_code`) ON DELETE CASCADE;


ALTER TABLE `prerequisiteof`
  ADD CONSTRAINT `fk_prerequisite` FOREIGN KEY (`prerequisite_course_code`) REFERENCES `courses` (`course_code`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_prerequisite_course` FOREIGN KEY (`course_code`) REFERENCES `courses` (`course_code`) ON DELETE CASCADE;


ALTER TABLE `sections`
  ADD CONSTRAINT `fk_course_code` FOREIGN KEY (`course_code`) REFERENCES `courses` (`course_code`) ON DELETE CASCADE;


ALTER TABLE `students`
  ADD CONSTRAINT `fk_major` FOREIGN KEY (`major_id`) REFERENCES `degrees` (`degree_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_minor` FOREIGN KEY (`minor_id`) REFERENCES `degrees` (`degree_id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

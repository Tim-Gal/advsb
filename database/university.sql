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

--
-- Veritabanı: `university`
--

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `courses`
--

CREATE TABLE `courses` (
  `course_code` varchar(50) NOT NULL,
  `course_name` varchar(255) NOT NULL,
  `course_description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `courses`
--

INSERT INTO `courses` (`course_code`, `course_name`, `course_description`) VALUES
('BIOL101', 'Introduction to Biology', 'test'),
('BIOL202', 'Cell Biology', NULL),
('BIOL303', 'Genetics', NULL),
('BIOL404', 'Marine Biology', NULL),
('BIOL505', 'Molecular Biology', NULL),
('BIOL606', 'Human Anatomy', NULL),
('BIOL707', 'Immunology', NULL),
('BIOL808', 'Neuroscience', NULL),
('BIOL909', 'Ecology and Evolution', NULL),
('BIOL999', 'Advanced Botany', NULL),
('CHEM101', 'General Chemistry I', 'Fundamentals of general chemistry.'),
('CHEM102', 'General Chemistry II', 'Continuation of general chemistry.'),
('CS101', 'Introduction to Computer Science', 'Basic concepts in computer science.'),
('CS102', 'Data Structures', 'Introduction to data structures.'),
('CS201', 'Algorithms', 'Design and analysis of algorithms.'),
('CS202', 'Computer Architecture', 'Study of computer system architecture.'),
('CS301', 'Operating Systems', 'Design and implementation of operating systems.'),
('CS302', 'Database Systems', 'Introduction to database design and management.'),
('ENG101', 'English Literature I', 'Introduction to English literature.'),
('ENG102', 'English Literature II', 'Continuation of English literature.'),
('HIST101', 'World History I', 'Introduction to world history.'),
('HIST102', 'World History II', 'Continuation of world history.'),
('MATH101', 'Calculus I', 'Introduction to differential and integral calculus.'),
('MATH102', 'Linear Algebra', 'Study of vector spaces and linear mappings.'),
('MATH201', 'Abstract Algebra', 'Introduction to groups, rings, and fields.'),
('PHIL101', 'Introduction to Philosophy', 'Basic philosophical concepts and thinkers.'),
('PHIL102', 'Ethics', 'Study of moral philosophy.');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `coursescompleted`
--

CREATE TABLE `coursescompleted` (
  `student_id` int(11) NOT NULL,
  `course_code` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `coursescompleted`
--

INSERT INTO `coursescompleted` (`student_id`, `course_code`) VALUES
(1, 'BIOL101'),
(1, 'BIOL202'),
(3, 'BIOL101'),
(3, 'BIOL202'),
(3, 'BIOL303'),
(4, 'BIOL101'),
(5, 'BIOL101'),
(6, 'BIOL202'),
(7, 'BIOL101'),
(8, 'BIOL202'),
(9, 'BIOL101'),
(10, 'BIOL101');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `coursesenrolled`
--

CREATE TABLE `coursesenrolled` (
  `student_id` int(11) NOT NULL,
  `section_code` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `coursesenrolled`
--

INSERT INTO `coursesenrolled` (`student_id`, `section_code`) VALUES
(1, 1),
(1, 2),
(3, 1),
(3, 6),
(4, 1),
(4, 2),
(5, 1),
(6, 1),
(7, 1),
(8, 1),
(9, 1),
(10, 1);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `degrees`
--

CREATE TABLE `degrees` (
  `degree_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `type` enum('Major','Minor') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `degrees`
--

INSERT INTO `degrees` (`degree_id`, `name`, `type`) VALUES
(4, 'Biology', 'Major'),
(5, 'Chemistry', 'Minor'),
(1, 'Computer Science', 'Major'),
(6, 'English', 'Minor'),
(7, 'History', 'Minor'),
(2, 'Mathematics', 'Major'),
(8, 'Philosophy', 'Minor'),
(3, 'Physics', 'Major'),
(9, 'Undeclared', 'Major');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `degree_courses`
--

CREATE TABLE `degree_courses` (
  `id` int(11) NOT NULL,
  `degree_id` int(11) NOT NULL,
  `course_code` varchar(50) NOT NULL,
  `course_type` enum('required','elective') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `degree_courses`
--

INSERT INTO `degree_courses` (`id`, `degree_id`, `course_code`, `course_type`) VALUES
(7, 1, 'CS101', 'required'),
(8, 1, 'CS102', 'required'),
(9, 1, 'CS201', 'elective'),
(10, 1, 'CS202', 'elective'),
(11, 1, 'CS301', 'required'),
(12, 1, 'CS302', 'elective'),
(13, 2, 'MATH101', 'required'),
(14, 2, 'MATH102', 'elective'),
(15, 2, 'MATH201', 'elective'),
(16, 4, 'BIOL101', 'required'),
(17, 4, 'BIOL202', 'required'),
(18, 4, 'BIOL303', 'required'),
(19, 4, 'BIOL404', 'elective'),
(20, 4, 'BIOL505', 'elective'),
(21, 4, 'BIOL606', 'elective'),
(22, 4, 'BIOL707', 'elective'),
(23, 4, 'BIOL808', 'elective'),
(24, 4, 'BIOL909', 'elective'),
(25, 4, 'BIOL999', 'elective'),
(26, 5, 'CHEM101', 'required'),
(27, 5, 'CHEM102', 'elective'),
(28, 6, 'ENG101', 'required'),
(29, 6, 'ENG102', 'elective'),
(30, 7, 'HIST101', 'required'),
(31, 7, 'HIST102', 'elective'),
(32, 8, 'PHIL101', 'required'),
(33, 8, 'PHIL102', 'elective');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `friendrequests`
--

CREATE TABLE `friendrequests` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `status` enum('pending','accepted','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `friendswith`
--

CREATE TABLE `friendswith` (
  `id` int(11) NOT NULL,
  `student_id1` int(11) NOT NULL,
  `student_id2` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `lectures`
--

CREATE TABLE `lectures` (
  `lecture_id` int(11) NOT NULL,
  `location` varchar(100) DEFAULT NULL,
  `day_of_week` enum('Mon','Tue','Wed','Thu','Fri') NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `section_code` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `lectures`
--

INSERT INTO `lectures` (`lecture_id`, `location`, `day_of_week`, `start_time`, `end_time`, `section_code`) VALUES
(11, 'Room A', 'Mon', '10:00:00', '11:00:00', 1),
(12, 'Room B', 'Tue', '11:00:00', '12:00:00', 1),
(21, 'Room C', 'Wed', '12:00:00', '13:00:00', 2),
(22, 'Room D', 'Thu', '13:00:00', '14:00:00', 2),
(31, 'Room A', 'Mon', '10:00:00', '11:30:00', 3),
(32, 'Room B', 'Tue', '11:00:00', '12:00:00', 3),
(41, 'Room C', 'Wed', '12:00:00', '13:00:00', 4),
(42, 'Room D', 'Thu', '13:00:00', '14:00:00', 4);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `prerequisiteof`
--

CREATE TABLE `prerequisiteof` (
  `course_code` varchar(50) NOT NULL,
  `prerequisite_course_code` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `prerequisiteof`
--

INSERT INTO `prerequisiteof` (`course_code`, `prerequisite_course_code`) VALUES
('BIOL202', 'BIOL101'),
('BIOL303', 'BIOL202'),
('BIOL404', 'BIOL303'),
('BIOL505', 'BIOL404'),
('BIOL606', 'BIOL505'),
('BIOL707', 'BIOL606'),
('BIOL808', 'BIOL707'),
('BIOL909', 'BIOL808'),
('BIOL999', 'BIOL909');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `sections`
--

CREATE TABLE `sections` (
  `section_code` int(11) NOT NULL,
  `semester` varchar(11) NOT NULL,
  `professor` varchar(100) DEFAULT NULL,
  `course_code` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `sections`
--

INSERT INTO `sections` (`section_code`, `semester`, `professor`, `course_code`) VALUES
(1, 'FALL', 'John Green', 'BIOL101'),
(2, 'FALL', 'John Green', 'BIOL202'),
(3, 'FALL', 'Don Miller', 'CS101'),
(4, 'FALL', 'Don Miller', 'CS102'),
(5, 'WINTER', 'John Green', 'BIOL303'),
(6, 'WINTER', 'John Green', 'BIOL404'),
(7, 'WINTER', 'Don Miller', 'CS201'),
(8, 'WINTER', 'Don Miller', 'CS202');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `students`
--

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

--
-- Tablo döküm verisi `students`
--

INSERT INTO `students` (`student_id`, `username`, `fname`, `lname`, `email`, `password_hash`, `remember_token`, `is_verified`, `verification_code`, `password_change_code`, `major_id`, `minor_id`) VALUES
(1, 'jwood1', 'John', 'Wood', 'alice.smith@university.edu', '$2y$10$QUvS4QATistNhdFM2qTf7u2cUfphR9LGbowI8Y2ZIf8rrKvBeM9C6', NULL, 0, NULL, NULL, 9, NULL),
(3, 'eertugrul3', 'Efe', 'Ertugrul', 'efertugrul6@gmail.com', '$2y$10$gv9ASeGqmEmrDPF5omkJwOOpkPsMBY4Gmmr4k8IfWb64S.GhZn6xO', NULL, 1, NULL, '651008', 4, NULL),
(4, 'mreed4', 'Mike', 'Reed', 'diana.prince@university.edu', NULL, NULL, 0, NULL, NULL, 9, NULL),
(5, 'dwest5', 'David', 'West', 'edward.norton@university.edu', NULL, NULL, 0, NULL, NULL, 9, NULL),
(6, 'tpage6', 'Tim', 'Page', 'timoxa.gal@gmail.com', '$2y$10$MS0jyLdIHDcy.U3PDi3kOuqXjKwxgKUArBfBa2t3dswC2GopSdo4G', NULL, 1, NULL, NULL, 9, NULL),
(7, 'fsnow7', 'Frank', 'Snow', 'george.clooney@university.edu', NULL, NULL, 0, NULL, NULL, 9, NULL),
(8, 'bgray8', 'Ben', 'Gray', 'hannah.montana@university.edu', NULL, NULL, 0, NULL, NULL, 9, NULL),
(9, 'mlane9', 'Mark', 'Lane', 'ian.mckellen@university.edu', NULL, NULL, 0, NULL, NULL, 9, NULL),
(10, 'pford10', 'Paul', 'Ford', 'julia.roberts@university.edu', NULL, NULL, 0, NULL, NULL, 9, NULL),
(14, 'Efstarisback', NULL, NULL, 'ali.ertugrul@mail.mcgill.ca', '$2y$10$HDE78iT/SwIM7dCR3huFGeID9t/bQ.txDxOpSO7SX7zcC6YBIypjm', NULL, 1, NULL, NULL, 1, NULL);

--
-- Dökümü yapılmış tablolar için indeksler
--

--
-- Tablo için indeksler `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`course_code`);

--
-- Tablo için indeksler `coursescompleted`
--
ALTER TABLE `coursescompleted`
  ADD PRIMARY KEY (`student_id`,`course_code`),
  ADD KEY `fk_completed_course` (`course_code`);

--
-- Tablo için indeksler `coursesenrolled`
--
ALTER TABLE `coursesenrolled`
  ADD PRIMARY KEY (`student_id`,`section_code`),
  ADD KEY `fk_enrolled_section` (`section_code`);

--
-- Tablo için indeksler `degrees`
--
ALTER TABLE `degrees`
  ADD PRIMARY KEY (`degree_id`),
  ADD UNIQUE KEY `unique_degree` (`name`,`type`);

--
-- Tablo için indeksler `degree_courses`
--
ALTER TABLE `degree_courses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_degree_idx` (`degree_id`),
  ADD KEY `fk_course_idx` (`course_code`);

--
-- Tablo için indeksler `friendrequests`
--
ALTER TABLE `friendrequests`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_request` (`sender_id`,`receiver_id`),
  ADD KEY `receiver_id` (`receiver_id`);

--
-- Tablo için indeksler `friendswith`
--
ALTER TABLE `friendswith`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_friendship` (`student_id1`,`student_id2`),
  ADD KEY `student_id2` (`student_id2`);

--
-- Tablo için indeksler `lectures`
--
ALTER TABLE `lectures`
  ADD PRIMARY KEY (`lecture_id`),
  ADD KEY `fk_section_code` (`section_code`);

--
-- Tablo için indeksler `prerequisiteof`
--
ALTER TABLE `prerequisiteof`
  ADD PRIMARY KEY (`course_code`,`prerequisite_course_code`),
  ADD KEY `fk_prerequisite` (`prerequisite_course_code`);

--
-- Tablo için indeksler `sections`
--
ALTER TABLE `sections`
  ADD PRIMARY KEY (`section_code`),
  ADD KEY `fk_course_code` (`course_code`);

--
-- Tablo için indeksler `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`student_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `username_2` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `fk_major` (`major_id`),
  ADD KEY `fk_minor` (`minor_id`);

--
-- Dökümü yapılmış tablolar için AUTO_INCREMENT değeri
--

--
-- Tablo için AUTO_INCREMENT değeri `degrees`
--
ALTER TABLE `degrees`
  MODIFY `degree_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Tablo için AUTO_INCREMENT değeri `degree_courses`
--
ALTER TABLE `degree_courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- Tablo için AUTO_INCREMENT değeri `friendrequests`
--
ALTER TABLE `friendrequests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Tablo için AUTO_INCREMENT değeri `friendswith`
--
ALTER TABLE `friendswith`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Tablo için AUTO_INCREMENT değeri `lectures`
--
ALTER TABLE `lectures`
  MODIFY `lecture_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9873;

--
-- Tablo için AUTO_INCREMENT değeri `sections`
--
ALTER TABLE `sections`
  MODIFY `section_code` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8302;

--
-- Tablo için AUTO_INCREMENT değeri `students`
--
ALTER TABLE `students`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Dökümü yapılmış tablolar için kısıtlamalar
--

--
-- Tablo kısıtlamaları `coursescompleted`
--
ALTER TABLE `coursescompleted`
  ADD CONSTRAINT `fk_completed_course` FOREIGN KEY (`course_code`) REFERENCES `courses` (`course_code`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_completed_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `coursesenrolled`
--
ALTER TABLE `coursesenrolled`
  ADD CONSTRAINT `fk_enrolled_section` FOREIGN KEY (`section_code`) REFERENCES `sections` (`section_code`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_enrolled_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `degree_courses`
--
ALTER TABLE `degree_courses`
  ADD CONSTRAINT `fk_course` FOREIGN KEY (`course_code`) REFERENCES `courses` (`course_code`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_degree` FOREIGN KEY (`degree_id`) REFERENCES `degrees` (`degree_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Tablo kısıtlamaları `friendrequests`
--
ALTER TABLE `friendrequests`
  ADD CONSTRAINT `friendrequests_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `friendrequests_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `friendswith`
--
ALTER TABLE `friendswith`
  ADD CONSTRAINT `friendswith_ibfk_1` FOREIGN KEY (`student_id1`) REFERENCES `students` (`student_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `friendswith_ibfk_2` FOREIGN KEY (`student_id2`) REFERENCES `students` (`student_id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `lectures`
--
ALTER TABLE `lectures`
  ADD CONSTRAINT `fk_section_code` FOREIGN KEY (`section_code`) REFERENCES `sections` (`section_code`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `prerequisiteof`
--
ALTER TABLE `prerequisiteof`
  ADD CONSTRAINT `fk_prerequisite` FOREIGN KEY (`prerequisite_course_code`) REFERENCES `courses` (`course_code`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_prerequisite_course` FOREIGN KEY (`course_code`) REFERENCES `courses` (`course_code`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `sections`
--
ALTER TABLE `sections`
  ADD CONSTRAINT `fk_course_code` FOREIGN KEY (`course_code`) REFERENCES `courses` (`course_code`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `fk_major` FOREIGN KEY (`major_id`) REFERENCES `degrees` (`degree_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_minor` FOREIGN KEY (`minor_id`) REFERENCES `degrees` (`degree_id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

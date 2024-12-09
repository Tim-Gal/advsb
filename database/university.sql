-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Anamakine: 127.0.0.1
-- Üretim Zamanı: 09 Ara 2024, 22:40:04
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Tablo döküm verisi `courses`
--

INSERT INTO `courses` (`course_code`, `course_name`, `course_description`) VALUES
('BIOL101', 'Introduction to Biology', NULL),
('BIOL202', 'Cell Biology', NULL),
('BIOL303', 'Genetics', NULL),
('BIOL404', 'Marine Biology', NULL),
('BIOL505', 'Molecular Biology', NULL),
('BIOL606', 'Human Anatomy', NULL),
('BIOL707', 'Immunology', NULL),
('BIOL808', 'Neuroscience', NULL),
('BIOL909', 'Ecology and Evolution', NULL),
('BIOL999', 'Advanced Botany', NULL);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `coursescompleted`
--

CREATE TABLE `coursescompleted` (
  `student_id` int(11) NOT NULL,
  `course_code` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Tablo döküm verisi `coursescompleted`
--

INSERT INTO `coursescompleted` (`student_id`, `course_code`) VALUES
(1, 'BIOL101'),
(1, 'BIOL202'),
(2, 'BIOL101'),
(3, 'BIOL101'),
(3, 'BIOL202'),
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Tablo döküm verisi `coursesenrolled`
--

INSERT INTO `coursesenrolled` (`student_id`, `section_code`) VALUES
(1, 2057),
(1, 4819),
(2, 3220),
(2, 8301),
(3, 2057),
(3, 4819),
(4, 4819),
(5, 3220),
(6, 8301),
(7, 2057),
(8, 4819),
(9, 3220),
(10, 8301);

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

--
-- Tablo döküm verisi `friendswith`
--

INSERT INTO `friendswith` (`id`, `student_id1`, `student_id2`, `created_at`) VALUES
(1, 3, 6, '2024-12-09 21:33:35');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Tablo döküm verisi `lectures`
--

INSERT INTO `lectures` (`lecture_id`, `location`, `day_of_week`, `start_time`, `end_time`, `section_code`) VALUES
(1024, 'Room C', 'Tue', '13:00:00', '14:30:00', 3220),
(4895, 'Room D', 'Fri', '10:00:00', '12:00:00', 8301),
(5084, 'Room B', 'Wed', '09:00:00', '10:00:00', 2057),
(9872, 'Room A', 'Mon', '09:00:00', '10:00:00', 4819);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `prerequisiteof`
--

CREATE TABLE `prerequisiteof` (
  `course_code` varchar(50) NOT NULL,
  `prerequisite_course_code` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Tablo döküm verisi `sections`
--

INSERT INTO `sections` (`section_code`, `semester`, `professor`, `course_code`) VALUES
(2057, 'FALL', 'John Green', 'BIOL202'),
(3220, 'WINTER', 'Michael Rodriguez', 'BIOL303'),
(4819, 'FALL', 'Peter Johnson', 'BIOL101'),
(8301, 'WINTER', 'Don Miller', 'BIOL404');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `students`
--

CREATE TABLE `students` (
  `student_id` int(11) NOT NULL,
  `fname` varchar(100) DEFAULT NULL,
  `lname` varchar(100) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT 0,
  `verification_code` varchar(100) DEFAULT NULL,
  `password_change_code` varchar(6) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Tablo döküm verisi `students`
--

INSERT INTO `students` (`student_id`, `fname`, `lname`, `email`, `password_hash`, `remember_token`, `is_verified`, `verification_code`, `password_change_code`) VALUES
(1, 'John', 'Wood', 'alice.smith@university.edu', '$2y$10$QUvS4QATistNhdFM2qTf7u2cUfphR9LGbowI8Y2ZIf8rrKvBeM9C6', NULL, 0, NULL, NULL),
(2, 'Jim', 'King', '', NULL, NULL, 0, NULL, NULL),
(3, 'Efe', 'Ertugrul', 'efertugrul6@gmail.com', '$2y$10$gv9ASeGqmEmrDPF5omkJwOOpkPsMBY4Gmmr4k8IfWb64S.GhZn6xO', 'f3e7db30361e3b784509131fb10e738c', 1, NULL, '651008'),
(4, 'Mike', 'Reed', 'diana.prince@university.edu', NULL, NULL, 0, NULL, NULL),
(5, 'David', 'West', 'edward.norton@university.edu', NULL, NULL, 0, NULL, NULL),
(6, 'Tim', 'Page', 'timoxa.gal@gmail.com', '$2y$10$MS0jyLdIHDcy.U3PDi3kOuqXjKwxgKUArBfBa2t3dswC2GopSdo4G', NULL, 1, NULL, NULL),
(7, 'Frank', 'Snow', 'george.clooney@university.edu', NULL, NULL, 0, NULL, NULL),
(8, 'Ben', 'Gray', 'hannah.montana@university.edu', NULL, NULL, 0, NULL, NULL),
(9, 'Mark', 'Lane', 'ian.mckellen@university.edu', NULL, NULL, 0, NULL, NULL),
(10, 'Paul', 'Ford', 'julia.roberts@university.edu', NULL, NULL, 0, NULL, NULL);

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
  ADD PRIMARY KEY (`student_id`);

--
-- Dökümü yapılmış tablolar için AUTO_INCREMENT değeri
--

--
-- Tablo için AUTO_INCREMENT değeri `friendrequests`
--
ALTER TABLE `friendrequests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `friendswith`
--
ALTER TABLE `friendswith`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

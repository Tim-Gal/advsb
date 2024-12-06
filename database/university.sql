-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Anamakine: 127.0.0.1
-- Üretim Zamanı: 06 Ara 2024, 20:02:06
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
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Tablo döküm verisi `courses`
--

INSERT INTO `courses` (`id`, `code`, `name`, `description`) VALUES
(1, 'BIO101', 'Introduction to Biology', NULL),
(2, 'BIO202', 'Cell Biology', NULL),
(3, 'BIO303', 'Genetics', NULL),
(4, 'BIO404', 'Marine Biology', NULL),
(5, 'BIO505', 'Molecular Biology', NULL),
(6, 'BIO606', 'Human Anatomy', NULL),
(7, 'BIO707', 'Immunology', NULL),
(8, 'BIO808', 'Neuroscience', NULL),
(9, 'BIO909', 'Ecology and Evolution', NULL),
(10, 'BIO999', 'Advanced Botany', NULL);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `course_offerings`
--

CREATE TABLE `course_offerings` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `semester_id` int(11) NOT NULL,
  `day_of_week` enum('Mon','Tue','Wed','Thu','Fri') NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `location` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Tablo döküm verisi `course_offerings`
--

INSERT INTO `course_offerings` (`id`, `course_id`, `semester_id`, `day_of_week`, `start_time`, `end_time`, `location`) VALUES
(1, 1, 1, 'Mon', '09:00:00', '10:00:00', 'Room A'),
(2, 1, 1, 'Wed', '09:00:00', '10:00:00', 'Room A'),
(3, 2, 1, 'Tue', '13:00:00', '14:30:00', 'Room B'),
(4, 3, 1, 'Fri', '10:00:00', '12:00:00', 'Room C');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `semesters`
--

CREATE TABLE `semesters` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `year` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Tablo döküm verisi `semesters`
--

INSERT INTO `semesters` (`id`, `name`, `year`) VALUES
(1, 'Fall', 2024),
(2, 'Winter', 2025),
(3, 'Summer', 2025);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `student_id` varchar(50) NOT NULL,
  `email` varchar(255) NOT NULL,
  `username` varchar(100) DEFAULT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Tablo döküm verisi `users`
--

INSERT INTO `users` (`id`, `student_id`, `email`, `username`, `password_hash`, `remember_token`, `created_at`) VALUES
(1, 'S1001', 'alice.smith@university.edu', 'Efstarisback', '$2y$10$QUvS4QATistNhdFM2qTf7u2cUfphR9LGbowI8Y2ZIf8rrKvBeM9C6', NULL, '2024-11-30 23:47:20'),
(2, 'S1002', 'bob.johnson@university.edu', NULL, NULL, NULL, '2024-11-30 23:47:20'),
(3, 'S1003', 'charlie.brown@university.edu', 'Efert', '$2y$10$AJPLF204rYwoLfsFsfbMTuOw6kXkCAbJu7ZYMvDqabQlRAbWDVGfK', NULL, '2024-11-30 23:47:20'),
(4, 'S1004', 'diana.prince@university.edu', NULL, NULL, NULL, '2024-11-30 23:47:20'),
(5, 'S1005', 'edward.norton@university.edu', NULL, NULL, NULL, '2024-11-30 23:47:20'),
(6, 'S1006', 'fiona.apple@university.edu', NULL, NULL, NULL, '2024-11-30 23:47:20'),
(7, 'S1007', 'george.clooney@university.edu', NULL, NULL, NULL, '2024-11-30 23:47:20'),
(8, 'S1008', 'hannah.montana@university.edu', NULL, NULL, NULL, '2024-11-30 23:47:20'),
(9, 'S1009', 'ian.mckellen@university.edu', NULL, NULL, NULL, '2024-11-30 23:47:20'),
(10, 'S1010', 'julia.roberts@university.edu', NULL, NULL, NULL, '2024-11-30 23:47:20');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `users_schedules`
--

CREATE TABLE `users_schedules` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `semester_id` int(11) NOT NULL,
  `course_offering_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dökümü yapılmış tablolar için indeksler
--

--
-- Tablo için indeksler `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Tablo için indeksler `course_offerings`
--
ALTER TABLE `course_offerings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `semester_id` (`semester_id`);

--
-- Tablo için indeksler `semesters`
--
ALTER TABLE `semesters`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `student_id` (`student_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Tablo için indeksler `users_schedules`
--
ALTER TABLE `users_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `semester_id` (`semester_id`),
  ADD KEY `course_offering_id` (`course_offering_id`);

--
-- Dökümü yapılmış tablolar için AUTO_INCREMENT değeri
--

--
-- Tablo için AUTO_INCREMENT değeri `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Tablo için AUTO_INCREMENT değeri `course_offerings`
--
ALTER TABLE `course_offerings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Tablo için AUTO_INCREMENT değeri `semesters`
--
ALTER TABLE `semesters`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Tablo için AUTO_INCREMENT değeri `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Tablo için AUTO_INCREMENT değeri `users_schedules`
--
ALTER TABLE `users_schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Dökümü yapılmış tablolar için kısıtlamalar
--

--
-- Tablo kısıtlamaları `course_offerings`
--
ALTER TABLE `course_offerings`
  ADD CONSTRAINT `course_offerings_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `course_offerings_ibfk_2` FOREIGN KEY (`semester_id`) REFERENCES `semesters` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Tablo kısıtlamaları `users_schedules`
--
ALTER TABLE `users_schedules`
  ADD CONSTRAINT `users_schedules_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `users_schedules_ibfk_2` FOREIGN KEY (`semester_id`) REFERENCES `semesters` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `users_schedules_ibfk_3` FOREIGN KEY (`course_offering_id`) REFERENCES `course_offerings` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Anamakine: 127.0.0.1
-- Üretim Zamanı: 01 Ara 2024, 01:10:01
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
(3, 'S1003', 'charlie.brown@university.edu', NULL, NULL, NULL, '2024-11-30 23:47:20'),
(4, 'S1004', 'diana.prince@university.edu', NULL, NULL, NULL, '2024-11-30 23:47:20'),
(5, 'S1005', 'edward.norton@university.edu', NULL, NULL, NULL, '2024-11-30 23:47:20'),
(6, 'S1006', 'fiona.apple@university.edu', NULL, NULL, NULL, '2024-11-30 23:47:20'),
(7, 'S1007', 'george.clooney@university.edu', NULL, NULL, NULL, '2024-11-30 23:47:20'),
(8, 'S1008', 'hannah.montana@university.edu', NULL, NULL, NULL, '2024-11-30 23:47:20'),
(9, 'S1009', 'ian.mckellen@university.edu', NULL, NULL, NULL, '2024-11-30 23:47:20'),
(10, 'S1010', 'julia.roberts@university.edu', NULL, NULL, NULL, '2024-11-30 23:47:20');

--
-- Dökümü yapılmış tablolar için indeksler
--

--
-- Tablo için indeksler `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `student_id` (`student_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Dökümü yapılmış tablolar için AUTO_INCREMENT değeri
--

--
-- Tablo için AUTO_INCREMENT değeri `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

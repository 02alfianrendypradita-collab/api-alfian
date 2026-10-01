-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 01, 2026 at 08:10 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ujian_asts`
--

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `nisn` varchar(20) DEFAULT NULL,
  `ttl` varchar(100) DEFAULT NULL,
  `gender` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `whatsapp` varchar(20) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `nisn`, `ttl`, `gender`, `email`, `whatsapp`, `password`, `address`) VALUES
(1, 'Leanne Graham', '123131444', 'PONOROGO, 12 Agustus 2010', 'MALE', 'Sincere@april.biz', NULL, NULL, 'Kulas Light, Apt. 556, Gwenborough, 92998-3874'),
(2, 'Ervin Howell', '262363463', 'PONOROGO, 12 Agustus 2010', 'FEMALE', 'Shanna@melissa.tv', NULL, NULL, 'Victor Plains, Suite 879, Wisokyburgh, 90566-7771'),
(3, 'Clementine Bauch', '632641362', 'PONOROGO, 12 Agustus 2010', 'MALE', 'Nathan@yesenia.net', NULL, NULL, 'Douglas Extension, Suite 847, McKenziehaven, 59590-4157'),
(4, 'Patricia Lebsack', '111222333', 'PONOROGO, 12 Agustus 2010', 'FEMALE', 'Julianne.OConner@kory.org', NULL, NULL, 'Hoeger Mall, Apt. 692, South Elvis, 53919-4257'),
(5, 'Chelsey Dietrich', '444555666', 'PONOROGO, 12 Agustus 2010', 'FEMALE', 'Lucio_Hettinger@annie.ca', NULL, NULL, 'Skiles Walks, Suite 351, Roscoeview, 33263'),
(6, 'Mrs. Dennis Schulist', '777888999', 'PONOROGO, 12 Agustus 2010', 'FEMALE', 'Karley_Dach@jasper.info', NULL, NULL, 'Norberto Crossing, Apt. 950, South Christy, 23505-1337'),
(7, 'Kurtis Weissnat', '121212121', 'PONOROGO, 12 Agustus 2010', 'MALE', 'Telly.Hoeger@billy.biz', NULL, NULL, 'Rex Trail, Suite 280, Howemouth, 58804-1099'),
(8, 'Nicholas Runolfsdottir', '343434343', 'PONOROGO, 12 Agustus 2010', 'MALE', 'Sherwood@rosamond.me', NULL, NULL, 'Ellsworth Summit, Suite 729, Aliyaview, 45169'),
(9, 'yanyan', '22222222', 'PONOROGO, 12 Agustus 2008', 'MALE', 'yan@gmail.com', NULL, NULL, 'ponorogo'),
(14, 'alfian', '11111111', 'PONOROGO, 10 Maret 2008', 'male', 'alfian@mail.com', NULL, NULL, 'ponorogo'),
(23, 'User 21', '100000021', 'PONOROGO, 12 Agustus 2010', 'MALE', 'user21@mail.com', NULL, NULL, 'Alamat 21'),
(24, 'yanyan', '11111111', 'PONOROGO, 12 Agustus 2008', 'MALE', 'yan@gmail.com', NULL, NULL, 'ponorogo'),
(32, 'jackowi22', '', 'ponorogo, 10 maret 2008', 'male', 'jack@gmail.com', NULL, NULL, 'ponorogo'),
(33, 'murot paju', '12232323', 'Ponorogo, 10 Januari 2001', 'MALE', 'murot@gmail.com', NULL, NULL, 'PONOROGO'),
(34, 'jawa', '111111111', 'Ponorogo, 10 Maret 2008', 'MALE', 'jawa@gmail.com', NULL, '$2y$10$IV7ztxesGnv/o7BJlm3UKuzt2SmxwTm7Bcyr84MU5hHbUu2vT/qEq', 'pulung kota'),
(39, 'tomtom', '11111111', 'Jakarta, 10 Mei 2009', 'MALE', 'tom@gmail.com', '089524953485', '$2y$10$5vp9wJkVieoH/l4DxlcgiuFP6LScOYkoU4l7D9uB4URxYFJ6eksSS', 'Jl.Raya Pulung');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

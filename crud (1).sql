-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 04, 2025 at 07:21 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `crud`
--

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `stock` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `price`, `image`, `category`, `stock`) VALUES
(5, 'camera', 'thbjnnnnsdkdfvsfgfg', 4000.00, '68741d35574ee.jpeg', NULL, NULL),
(6, 'dress', 'beautiful red dress ', 5000.00, '68741e40761ff.jpeg', NULL, NULL),
(8, 'camera', 'best camera', 12000.00, '687f98bb3ad50.png', NULL, NULL),
(9, 'perfume', 'best perfume', 15000.00, '687fbf5084a93.jfif', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `student`
--

CREATE TABLE `student` (
  `ID` int(11) NOT NULL,
  `Name` varchar(50) NOT NULL,
  `Email` varchar(50) NOT NULL,
  `Age` varchar(50) NOT NULL,
  `Phone_number` varchar(50) NOT NULL,
  `Class` varchar(50) NOT NULL,
  `Image` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student`
--

INSERT INTO `student` (`ID`, `Name`, `Email`, `Age`, `Phone_number`, `Class`, `Image`) VALUES
(1, 'abira', 'abiraamie1880@gmail.com', '12', '35678987', '4', NULL),
(2, 'abira', 'abiraamie1880@gmail.com', '12', '35678987', '4', ''),
(3, 'nayab', 'amirpathanamirpathan352@gmail.com', '13', '35678987', '6', ''),
(5, 'nayab', 'amirpathanamirpathan352@gmail.com', '12', '35678987', '6', 'ancient-colosseum-am'),
(6, 'zahra', 'abiraamir1880@gmail.com', '13', '35678987', '14', 'ancient-colosseum-am'),
(7, 'abira', 'abiraamie1880@gmail.com', '12', '35678987', '7', 'ancient-colosseum-am'),
(8, 'abira', 'abiraamie1880@gmail.com', '12', '35678987', '7', 'ancient-colosseum-am');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `student`
--
ALTER TABLE `student`
  ADD PRIMARY KEY (`ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `student`
--
ALTER TABLE `student`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

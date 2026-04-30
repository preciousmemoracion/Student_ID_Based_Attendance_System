-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 30, 2026 at 08:39 AM
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
-- Database: `attendance_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`) VALUES
(1, 'admin', '0192023a7bbd73250516f069df18b500');

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL,
  `student_id` varchar(50) DEFAULT NULL,
  `class_id` int(11) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `time` time DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `session_id` int(11) DEFAULT NULL,
  `section` varchar(20) DEFAULT NULL,
  `subject` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`id`, `student_id`, `class_id`, `date`, `time`, `status`, `session_id`, `section`, `subject`) VALUES
(1, NULL, NULL, '2026-04-08', '17:21:36', 'Absent', NULL, NULL, NULL),
(2, NULL, NULL, '2026-04-08', '17:23:40', 'Absent', NULL, NULL, NULL),
(3, NULL, NULL, '2026-04-08', '17:28:35', 'Absent', NULL, NULL, NULL),
(4, '12345678', NULL, '2026-04-08', '18:22:25', 'Absent', NULL, 'BSIS 2B', NULL),
(5, '240705a124', NULL, '2026-04-08', '18:52:15', 'Absent', NULL, 'BSIS 2B', NULL),
(6, '240621a091', NULL, '2026-04-08', '19:12:08', 'Absent', NULL, 'BSIS 2A', NULL),
(7, '241207a185', NULL, '2026-04-13', '12:05:29', 'Absent', NULL, 'BSIS 2B', NULL),
(8, '240621a091', NULL, '2026-04-13', '12:57:08', 'Present', NULL, 'A', NULL),
(9, '241207a185', NULL, '2026-04-13', '17:45:37', 'Absent', NULL, '2B', NULL),
(10, '240515a115', NULL, '2026-04-13', '18:01:15', 'Absent', NULL, '2B', NULL),
(11, '240705a124', NULL, '2026-04-13', '18:17:03', 'Late', NULL, '2B', 'rgegeggege'),
(12, '241117A112', NULL, '2026-04-13', '18:18:27', 'Late', NULL, '2B', 'rgegeggege'),
(13, '240621a091', NULL, '2026-04-13', '18:29:30', 'Late', NULL, '2A', 'rgegeggege'),
(14, '12', NULL, '2026-04-13', '18:43:06', 'Late', NULL, '4A', '15'),
(15, '241207a185', NULL, '2026-04-21', '13:26:57', 'Late', NULL, '2B', 'System Analysis And Design'),
(16, '240621a091', NULL, '2026-04-21', '16:12:46', 'Absent', NULL, '2A', 'Information Management (CC105)'),
(17, '241207a185', NULL, '2026-04-28', '13:06:22', 'Late', NULL, '2B', 'System Analysis And Design');

-- --------------------------------------------------------

--
-- Table structure for table `attendance_session`
--

CREATE TABLE `attendance_session` (
  `id` int(11) NOT NULL,
  `subject` varchar(100) DEFAULT NULL,
  `section` varchar(50) DEFAULT NULL,
  `start_time` datetime DEFAULT NULL,
  `end_time` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `classes`
--

CREATE TABLE `classes` (
  `id` int(11) NOT NULL,
  `subject` varchar(100) DEFAULT NULL,
  `section` varchar(50) DEFAULT NULL,
  `start_time` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `student_id` varchar(50) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `section` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `student_id`, `name`, `password`, `section`) VALUES
(13, '241207a185', 'Precious Memoracion', NULL, '2B'),
(14, '240705a124', 'Kelvin Marious Vallente', NULL, '2B'),
(15, '241117A112', 'Kimberly Lumayag', NULL, '2B'),
(16, '240621a091', 'Roy John Petallar', NULL, '2A'),
(17, '240515a115', 'Nicole Torrefiel', NULL, '2B'),
(18, '12', 'russel', NULL, '4A'),
(19, '240716a139', 'Riza Mae Rosacena', NULL, '2B');

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `id` int(11) NOT NULL,
  `subject` varchar(100) DEFAULT NULL,
  `section` varchar(10) DEFAULT NULL,
  `day` varchar(50) DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`id`, `subject`, `section`, `day`, `start_time`, `end_time`) VALUES
(1, 'System Analysis And Design', '2B', 'Tuesday/Thursday', '11:30:00', '14:00:00'),
(2, 'Information Management (CC105)', '2B', 'Monday/Wednesday/Friday', '08:00:00', '10:00:00'),
(3, 'System Analysis And Design', '2A', 'Monday/Wednesday/Friday', '10:00:00', '12:00:00'),
(4, 'rgegeggege', '2B', 'Monday/Wednesday/Friday', '17:00:00', '19:00:00'),
(5, 'rgegeggege', '2A', 'Monday/Wednesday/Friday', '18:00:00', '19:00:00'),
(6, '13', '4B', 'Monday/Wednesday/Friday', '08:00:00', '10:00:00'),
(7, '14', '4A', 'Tuesday/Thursday', '10:00:00', '12:00:00'),
(8, '15', '4A', 'Monday/Wednesday/Friday', '18:00:00', '20:00:00'),
(9, 'Information Management (CC105)', '2A', 'Tuesday/Thursday', '08:00:00', '10:30:00');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `attendance_session`
--
ALTER TABLE `attendance_session`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `classes`
--
ALTER TABLE `classes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `student_id` (`student_id`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `attendance_session`
--
ALTER TABLE `attendance_session`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `classes`
--
ALTER TABLE `classes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

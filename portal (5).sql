-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 19, 2025 at 12:53 AM
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
-- Database: `portal`
--

-- --------------------------------------------------------

--
-- Table structure for table `signup_login`
--

CREATE TABLE `signup_login` (
  `user_id` int(11) NOT NULL,
  `fname` varchar(255) NOT NULL,
  `lname` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(32) NOT NULL,
  `gender` enum('Male','Female') NOT NULL,
  `create_datetime` datetime NOT NULL,
  `role` enum('student','teacher') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `signup_login`
--

INSERT INTO `signup_login` (`user_id`, `fname`, `lname`, `email`, `password`, `gender`, `create_datetime`, `role`) VALUES
(12, 'paul', 'gonzaga', 'gonzaga@gmail.com', '81dc9bdb52d04dc20036dbd8313ed055', 'Male', '2025-06-09 19:23:06', 'teacher'),
(13, 'mike', 'sarmiento', 'mike@gmail.com', '81dc9bdb52d04dc20036dbd8313ed055', 'Male', '2025-06-10 16:50:54', 'student'),
(14, 'roy', 'dela merced', 'roy@gmail.com', '81dc9bdb52d04dc20036dbd8313ed055', 'Male', '2025-06-10 17:30:18', 'student'),
(15, 'yeoj', 'olivar', 'yeoj@gmai.com', '81dc9bdb52d04dc20036dbd8313ed055', 'Male', '2025-06-16 15:37:21', 'teacher'),
(16, 'carlo', 'bonji', 'carlo@gmail.com', '81dc9bdb52d04dc20036dbd8313ed055', 'Male', '2025-06-16 15:54:31', 'student'),
(28, 'angelo', 'sarmiento', 'aa@gmail.com', '81dc9bdb52d04dc20036dbd8313ed055', 'Male', '2025-06-16 16:32:13', 'student'),
(29, 'joshua', 'sabalo', 'joshua@gmail.com', '81dc9bdb52d04dc20036dbd8313ed055', 'Male', '2025-06-16 16:33:36', 'student');

-- --------------------------------------------------------

--
-- Table structure for table `student_grades`
--

CREATE TABLE `student_grades` (
  `id` int(11) NOT NULL,
  `student_id` varchar(20) NOT NULL,
  `teacher_email` varchar(100) NOT NULL,
  `class_section` varchar(50) NOT NULL,
  `subject` varchar(100) NOT NULL,
  `semester` enum('1st Semester','2nd Semester') NOT NULL,
  `quizzes` decimal(5,2) DEFAULT NULL,
  `attendance` decimal(5,2) DEFAULT NULL,
  `activities` decimal(5,2) DEFAULT NULL,
  `participation` decimal(5,2) DEFAULT NULL,
  `final_project` decimal(5,2) DEFAULT NULL,
  `midterm_exam` decimal(5,2) DEFAULT NULL,
  `final_exam` decimal(5,2) DEFAULT NULL,
  `final_computation` decimal(5,2) DEFAULT NULL,
  `final_grade` decimal(3,1) DEFAULT NULL,
  `date_uploaded` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_grades`
--

INSERT INTO `student_grades` (`id`, `student_id`, `teacher_email`, `class_section`, `subject`, `semester`, `quizzes`, `attendance`, `activities`, `participation`, `final_project`, `midterm_exam`, `final_exam`, `final_computation`, `final_grade`, `date_uploaded`) VALUES
(48, '20250003', 'gonzaga@gmail.com', 'BSCS-3C', 'CS2-306', '2nd Semester', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.0, '2025-06-19 06:49:04'),
(49, '20250006', 'gonzaga@gmail.com', 'BSCS-3C', 'CS2-306', '2nd Semester', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.0, '2025-06-19 06:49:04');

-- --------------------------------------------------------

--
-- Table structure for table `student_profiles`
--

CREATE TABLE `student_profiles` (
  `student_id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `section` varchar(255) NOT NULL,
  `program` varchar(255) NOT NULL,
  `year_level` int(11) NOT NULL,
  `address` varchar(255) NOT NULL,
  `phone` int(11) NOT NULL,
  `emergency_contact` int(11) NOT NULL,
  `birthday` date NOT NULL,
  `avatar` varchar(255) NOT NULL,
  `gender` enum('Male','Female') NOT NULL,
  `semester` enum('1st Semester','2nd Semester') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_profiles`
--

INSERT INTO `student_profiles` (`student_id`, `email`, `section`, `program`, `year_level`, `address`, `phone`, `emergency_contact`, `birthday`, `avatar`, `gender`, `semester`) VALUES
(20250002, 'mike@gmail.com', 'A', 'BSCS', 2, '', 0, 0, '0000-00-00', '', 'Male', '1st Semester'),
(20250003, 'roy@gmail.com', 'C', 'BSCS', 3, '', 0, 0, '0000-00-00', '', 'Male', '2nd Semester'),
(20250004, 'carlo@gmail.com', 'A', 'BSCS', 3, '', 0, 0, '0000-00-00', '', 'Male', '1st Semester'),
(20250005, 'aa@gmail.com', 'A', 'BSCS', 3, '', 0, 0, '0000-00-00', '', 'Male', '1st Semester'),
(20250006, 'joshua@gmail.com', 'C', 'BSCS', 3, '', 1212, 0, '2025-06-18', 'uploads/avatars/1750279972_5a5982337692188d.jpg', 'Male', '1st Semester');

-- --------------------------------------------------------

--
-- Table structure for table `teacher_profiles`
--

CREATE TABLE `teacher_profiles` (
  `teacher_id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `birthday` date NOT NULL,
  `phone` int(11) NOT NULL,
  `emergency_contact` int(11) NOT NULL,
  `avatar` varchar(255) NOT NULL,
  `department` varchar(255) NOT NULL,
  `designation` varchar(255) NOT NULL,
  `experience` int(11) NOT NULL,
  `subjects` varchar(255) NOT NULL,
  `class_assigned` varchar(255) NOT NULL,
  `gender` enum('Male','Female') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teacher_profiles`
--

INSERT INTO `teacher_profiles` (`teacher_id`, `email`, `birthday`, `phone`, `emergency_contact`, `avatar`, `department`, `designation`, `experience`, `subjects`, `class_assigned`, `gender`) VALUES
(20250001, 'gonzaga@gmail.com', '2025-06-04', 123, 123, 'uploads/avatars/teacher_6847204d8ae87.jpg', 'Computer Science', 'Associate Professor', 4, 'CS2-204, CS2-305, CS2-306', 'BSCS-2C, BSCS-3C', 'Male'),
(20250002, 'yeoj@gmai.com', '0000-00-00', 0, 0, '', 'Information Technology', 'Assistant Professor', 12, 'CS2-205, CS2-204, CS2-206', 'BSCS-2C', 'Male');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `signup_login`
--
ALTER TABLE `signup_login`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `student_grades`
--
ALTER TABLE `student_grades`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_grade` (`student_id`,`subject`,`semester`,`teacher_email`);

--
-- Indexes for table `student_profiles`
--
ALTER TABLE `student_profiles`
  ADD PRIMARY KEY (`student_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `teacher_profiles`
--
ALTER TABLE `teacher_profiles`
  ADD PRIMARY KEY (`teacher_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `signup_login`
--
ALTER TABLE `signup_login`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `student_grades`
--
ALTER TABLE `student_grades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `student_profiles`
--
ALTER TABLE `student_profiles`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20250007;

--
-- AUTO_INCREMENT for table `teacher_profiles`
--
ALTER TABLE `teacher_profiles`
  MODIFY `teacher_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20250006;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `student_profiles`
--
ALTER TABLE `student_profiles`
  ADD CONSTRAINT `student_profiles_ibfk_1` FOREIGN KEY (`email`) REFERENCES `signup_login` (`email`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `teacher_profiles`
--
ALTER TABLE `teacher_profiles`
  ADD CONSTRAINT `teacher_profiles_ibfk_1` FOREIGN KEY (`email`) REFERENCES `signup_login` (`email`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

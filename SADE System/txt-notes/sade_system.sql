-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 27, 2025 at 02:46 PM
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
-- Database: `sade_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `access_logs`
--

CREATE TABLE `access_logs` (
  `id` int(11) NOT NULL,
  `log_id` varchar(100) NOT NULL,
  `device_id` varchar(50) NOT NULL,
  `lab_id` varchar(10) NOT NULL,
  `user_id` varchar(50) DEFAULT NULL,
  `user_name` varchar(200) DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `method` varchar(50) DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `access_logs`
--

INSERT INTO `access_logs` (`id`, `log_id`, `device_id`, `lab_id`, `user_id`, `user_name`, `action`, `method`, `timestamp`) VALUES
(13, 'ARCHIVE-6925b81e3f728-1764079646', 'SYSTEM', '1811', '11', 'Co Leng', 'ARCHIVED_SCHEDULE', 'ARCHIVE', '2025-11-25 07:07:26'),
(14, 'ARCHIVE-6925b8dd4d2f4-1764079837', 'SYSTEM', '1811', '11', 'Co Leng', 'ARCHIVED_SCHEDULE', 'ARCHIVE', '2025-11-25 07:10:37');

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `log_id` int(11) NOT NULL,
  `faculty_id` varchar(20) DEFAULT NULL,
  `activity_type` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`log_id`, `faculty_id`, `activity_type`, `description`, `ip_address`, `created_at`) VALUES
(1, 'F001', 'CLASS_STARTED', 'Data Structures class started in Lab 1811', '192.168.1.100', '2025-10-31 18:21:31'),
(2, 'F002', 'SCHEDULE_MODIFIED', 'Web Development schedule changed', '192.168.1.101', '2025-11-02 18:21:31'),
(3, 'F001', 'ATTENDANCE_MARKED', 'Attendance marked for CS201', '192.168.1.100', '2025-11-04 10:21:31'),
(4, 'F003', 'CLASS_ENDED', 'Database Design class ended', '192.168.1.102', '2025-11-04 15:21:31');

-- --------------------------------------------------------

--
-- Table structure for table `devices`
--

CREATE TABLE `devices` (
  `id` int(11) NOT NULL,
  `device_id` varchar(50) NOT NULL,
  `lab_id` varchar(10) NOT NULL,
  `room` varchar(100) DEFAULT NULL,
  `is_online` tinyint(1) DEFAULT 0,
  `door_locked` tinyint(1) DEFAULT 1,
  `last_seen` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `devices`
--

INSERT INTO `devices` (`id`, `device_id`, `lab_id`, `room`, `is_online`, `door_locked`, `last_seen`, `created_at`) VALUES
(4, 'SADE_DOOR_1811', '1811', 'Lab 1811', 1, 1, '2025-11-04 18:21:31', '2025-11-04 18:21:31'),
(5, 'SADE_DOOR_1812', '1812', 'Lab 1812', 1, 1, '2025-11-04 18:21:31', '2025-11-04 18:21:31'),
(6, 'SADE_DOOR_1815', '1815', 'Lab 1815', 0, 1, '2025-11-04 16:21:31', '2025-11-04 18:21:31');

-- --------------------------------------------------------

--
-- Table structure for table `faculty`
--

CREATE TABLE `faculty` (
  `faculty_id` varchar(20) NOT NULL,
  `faculty_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `department` varchar(100) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `faculty`
--

INSERT INTO `faculty` (`faculty_id`, `faculty_name`, `email`, `password`, `department`, `status`, `created_at`, `updated_at`) VALUES
('F001', 'Prof. Maria Cruz', 'maria.cruz@ust.edu.ph', 'ef92b778bafe771e89245b89ecbc08a44a4e166c06659911881f383d4473e94f', 'Computer Science', 'active', '2025-11-04 18:21:31', '2025-11-04 18:21:31'),
('F002', 'Prof. Juan Dela Cruz', 'juan.delacruz@ust.edu.ph', 'ef92b778bafe771e89245b89ecbc08a44a4e166c06659911881f383d4473e94f', 'Engineering', 'active', '2025-11-04 18:21:31', '2025-11-04 18:21:31'),
('F003', 'Prof. Anna Santos', 'anna.santos@ust.edu.ph', 'ef92b778bafe771e89245b89ecbc08a44a4e166c06659911881f383d4473e94f', 'Information Technology', 'active', '2025-11-04 18:21:31', '2025-11-04 18:21:31');

-- --------------------------------------------------------

--
-- Table structure for table `participants`
--

CREATE TABLE `participants` (
  `id` int(11) NOT NULL,
  `id_number` varchar(50) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `user_type` varchar(50) NOT NULL DEFAULT 'Student',
  `email` varchar(255) NOT NULL,
  `status` enum('PRESENT','ABSENT') NOT NULL DEFAULT 'PRESENT',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `participants`
--

INSERT INTO `participants` (`id`, `id_number`, `full_name`, `user_type`, `email`, `status`, `created_at`, `updated_at`) VALUES
(1, '2022178651', 'Jericho Wayne G. Co Leng', 'Student', 'jericho.coleng.cics@ust.edu.ph', 'PRESENT', '2025-11-14 08:07:24', '2025-11-14 08:07:24'),
(2, '2022178651', 'Sample Student', 'Student', 'student@example.com', 'PRESENT', '2025-11-14 08:16:23', '2025-11-14 08:16:23'),
(3, '2022178651', 'Sample Student', 'Student', 'student@example.com', 'PRESENT', '2025-11-27 12:33:51', '2025-11-27 12:33:51'),
(4, '290020222', 'sofia wong', 'Student', 'sofia@gmail.com', 'PRESENT', '2025-11-27 12:33:51', '2025-11-27 12:33:51');

-- --------------------------------------------------------

--
-- Table structure for table `schedules`
--

CREATE TABLE `schedules` (
  `id` int(11) NOT NULL,
  `schedule_id` varchar(50) NOT NULL,
  `course_name` varchar(100) NOT NULL,
  `course_code` varchar(20) NOT NULL,
  `instructor` varchar(100) NOT NULL,
  `day` enum('monday','tuesday','wednesday','thursday','friday','saturday') NOT NULL,
  `room` varchar(10) NOT NULL,
  `start_time` varchar(10) NOT NULL,
  `end_time` varchar(10) NOT NULL,
  `created_by` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `security_alerts`
--

CREATE TABLE `security_alerts` (
  `id` int(11) NOT NULL,
  `alert_id` varchar(100) NOT NULL,
  `device_id` varchar(50) NOT NULL,
  `lab_id` varchar(10) NOT NULL,
  `alert_type` varchar(50) NOT NULL,
  `severity` enum('LOW','MEDIUM','HIGH','CRITICAL') DEFAULT 'MEDIUM',
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `is_acknowledged` tinyint(1) DEFAULT 0,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `student_id` varchar(50) NOT NULL,
  `enrollment_date` date DEFAULT NULL,
  `status` enum('active','inactive','graduated','archived') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `system_logs`
--

CREATE TABLE `system_logs` (
  `id` int(11) NOT NULL,
  `log_id` varchar(100) NOT NULL,
  `event_type` varchar(50) NOT NULL,
  `message` text NOT NULL,
  `user_id` varchar(50) DEFAULT NULL,
  `user_name` varchar(200) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_logs`
--

INSERT INTO `system_logs` (`id`, `log_id`, `event_type`, `message`, `user_id`, `user_name`, `ip_address`, `timestamp`) VALUES
(1, 'SYSLOG001', 'SYSTEM_START', 'SADE System started', NULL, 'SYSTEM', '192.168.1.1', '2025-10-28 18:21:31'),
(2, 'SYSLOG002', 'USER_LOGIN', 'User logged in', 'F001-U', 'Maria Cruz', '192.168.1.50', '2025-10-30 18:21:31'),
(3, 'SYSLOG003', 'DATABASE_BACKUP', 'Automated backup completed', NULL, 'SYSTEM', '192.168.1.1', '2025-11-01 18:21:31'),
(4, 'SYSLOG004', 'DEVICE_STATUS_CHANGE', 'Device SADE_DOOR_1815 went offline', NULL, 'SYSTEM', '192.168.1.1', '2025-11-04 15:21:31'),
(5, 'SYSLOG005', 'USER_LOGOUT', 'User logged out', 'F002-U', 'Juan Dela Cruz', '192.168.1.55', '2025-11-04 17:21:31');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `user_id` varchar(50) NOT NULL,
  `user_type` enum('STUDENT','FACULTY','TECHNICIAN') NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `pin` varchar(255) DEFAULT NULL,
  `allowed_labs` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`allowed_labs`)),
  `access_level` varchar(20) DEFAULT 'STUDENT',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `user_id`, `user_type`, `first_name`, `last_name`, `email`, `pin`, `allowed_labs`, `access_level`, `is_active`, `created_at`) VALUES
(11, 'T8363', 'TECHNICIAN', 'Co', 'Leng', NULL, '2222', NULL, 'TECHNICIAN', 1, '2025-11-11 06:04:32');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `access_logs`
--
ALTER TABLE `access_logs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `log_id` (`log_id`);

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `idx_faculty` (`faculty_id`),
  ADD KEY `idx_activity_type` (`activity_type`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `devices`
--
ALTER TABLE `devices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `device_id` (`device_id`);

--
-- Indexes for table `faculty`
--
ALTER TABLE `faculty`
  ADD PRIMARY KEY (`faculty_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `participants`
--
ALTER TABLE `participants`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `schedules`
--
ALTER TABLE `schedules`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `schedule_id` (`schedule_id`),
  ADD KEY `idx_room_day` (`room`,`day`),
  ADD KEY `idx_schedule_id` (`schedule_id`),
  ADD KEY `idx_created_by` (`created_by`);

--
-- Indexes for table `security_alerts`
--
ALTER TABLE `security_alerts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `alert_id` (`alert_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD UNIQUE KEY `student_id` (`student_id`),
  ADD KEY `idx_student_id` (`student_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `system_logs`
--
ALTER TABLE `system_logs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `log_id` (`log_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `access_logs`
--
ALTER TABLE `access_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `devices`
--
ALTER TABLE `devices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `participants`
--
ALTER TABLE `participants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `schedules`
--
ALTER TABLE `schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `security_alerts`
--
ALTER TABLE `security_alerts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `system_logs`
--
ALTER TABLE `system_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`faculty_id`) REFERENCES `faculty` (`faculty_id`) ON DELETE SET NULL;

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `students_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

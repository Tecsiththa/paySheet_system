-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 27, 2026 at 07:46 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.1.17

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `paysheet_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `allowances`
--

CREATE TABLE `allowances` (
  `allowance_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `month` int(11) NOT NULL,
  `year` int(11) NOT NULL,
  `travel_allowance` decimal(10,2) DEFAULT 0.00,
  `food_allowance` decimal(10,2) DEFAULT 0.00,
  `created_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `allowances`
--

INSERT INTO `allowances` (`allowance_id`, `employee_id`, `month`, `year`, `travel_allowance`, `food_allowance`, `created_date`) VALUES
(1, 1, 3, 2024, 5000.00, 3000.00, '2026-03-21 09:52:02'),
(2, 2, 3, 2024, 4000.00, 2500.00, '2026-03-21 09:52:02');

-- --------------------------------------------------------

--
-- Table structure for table `companies`
--

CREATE TABLE `companies` (
  `company_id` int(11) NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `company_address` text NOT NULL,
  `company_phone` varchar(20) NOT NULL,
  `company_email` varchar(100) NOT NULL,
  `company_logo` varchar(255) DEFAULT NULL,
  `registration_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `companies`
--

INSERT INTO `companies` (`company_id`, `company_name`, `company_address`, `company_phone`, `company_email`, `company_logo`, `registration_date`, `status`) VALUES
(1, 'ABC Private Limited', 'No 123, Galle Road, Colombo 03', '0112345678', 'info@abc.lk', NULL, '2026-03-21 09:52:02', 'active'),
(2, 'n&amp;d lanka', 'No 40/5\r\nDhammananda Mawatha', '0771297538', 'n$d@gmail.com', NULL, '2026-03-21 16:21:58', 'active'),
(3, 'amma company', 'no 50, galle road panadura', '0771297535', 'amma@gmail.com', NULL, '2026-03-23 10:59:09', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `employee_id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `employee_name` varchar(255) NOT NULL,
  `employee_nic` varchar(20) NOT NULL,
  `employee_address` text NOT NULL,
  `employee_phone` varchar(20) NOT NULL,
  `employee_email` varchar(100) NOT NULL,
  `position` varchar(100) NOT NULL,
  `department` varchar(100) NOT NULL,
  `basic_salary` decimal(10,2) NOT NULL,
  `joining_date` date NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`employee_id`, `company_id`, `employee_name`, `employee_nic`, `employee_address`, `employee_phone`, `employee_email`, `position`, `department`, `basic_salary`, `joining_date`, `status`, `created_date`) VALUES
(1, 1, 'Kamal Perera', '199012345678', 'No 45, Kandy Road, Gampaha', '0771234567', 'kamal@example.com', 'Software Engineer', 'IT', 80000.00, '2023-01-15', 'active', '2026-03-21 09:52:02'),
(2, 1, 'Nimal Silva', '198523456789', 'No 78, Main Street, Negombo', '0762345678', 'nimal@example.com', 'Accountant', 'Finance', 60000.00, '2023-03-20', 'active', '2026-03-21 09:52:02'),
(3, 1, 'sithara pasanmitha', '200412202900', 'No 40/5\r\nDhammananda Mawatha, panadura', '070698950', 'sithara@gmail.com', 'HR Manager', 'HR Department', 100000.00, '2026-03-02', 'active', '2026-03-25 14:13:18'),
(4, 1, 'sathish sudara', '200105920788', 'No 40/5rnDhammananda Mawatha', '0777888999', 'sathish@gmail.com', 'Network engineer', 'IT', 500000.00, '2026-02-02', 'active', '2026-03-25 15:06:20');

-- --------------------------------------------------------

--
-- Table structure for table `leave_balance`
--

CREATE TABLE `leave_balance` (
  `balance_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `year` int(11) NOT NULL,
  `annual_leave_remaining` int(11) DEFAULT 14,
  `casual_leave_remaining` int(11) DEFAULT 7,
  `sick_leave_remaining` int(11) DEFAULT 7
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `leave_balance`
--

INSERT INTO `leave_balance` (`balance_id`, `employee_id`, `year`, `annual_leave_remaining`, `casual_leave_remaining`, `sick_leave_remaining`) VALUES
(1, 1, 2024, 14, 7, 7),
(2, 2, 2024, 12, 5, 7),
(3, 3, 2026, 14, 7, 7),
(4, 4, 2026, 14, 7, 6);

-- --------------------------------------------------------

--
-- Table structure for table `leave_records`
--

CREATE TABLE `leave_records` (
  `leave_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `leave_type_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `days_count` int(11) NOT NULL,
  `reason` text NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `applied_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `approved_by` int(11) DEFAULT NULL,
  `approved_date` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `leave_records`
--

INSERT INTO `leave_records` (`leave_id`, `employee_id`, `leave_type_id`, `start_date`, `end_date`, `days_count`, `reason`, `status`, `applied_date`, `approved_by`, `approved_date`) VALUES
(1, 4, 3, '2026-03-25', '2026-03-25', 1, 'fever', 'approved', '2026-03-25 15:24:54', 1, '2026-03-25 15:28:45');

-- --------------------------------------------------------

--
-- Table structure for table `leave_types`
--

CREATE TABLE `leave_types` (
  `leave_type_id` int(11) NOT NULL,
  `leave_name` varchar(50) NOT NULL,
  `days_per_year` int(11) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `leave_types`
--

INSERT INTO `leave_types` (`leave_type_id`, `leave_name`, `days_per_year`, `description`) VALUES
(1, 'Annual Leave', 14, 'Paid annual leave'),
(2, 'Casual Leave', 7, 'Leave for personal matters'),
(3, 'Sick Leave', 7, 'Leave when employee is ill');

-- --------------------------------------------------------

--
-- Table structure for table `loans`
--

CREATE TABLE `loans` (
  `loan_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `loan_amount` decimal(10,2) NOT NULL,
  `monthly_installment` decimal(10,2) NOT NULL,
  `remaining_amount` decimal(10,2) NOT NULL,
  `start_date` date NOT NULL,
  `status` enum('active','completed') DEFAULT 'active',
  `created_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `loans`
--

INSERT INTO `loans` (`loan_id`, `employee_id`, `loan_amount`, `monthly_installment`, `remaining_amount`, `start_date`, `status`, `created_date`) VALUES
(1, 1, 100000.00, 10000.00, 40000.00, '2024-01-01', 'active', '2026-03-21 09:52:02');

-- --------------------------------------------------------

--
-- Table structure for table `loan_payments`
--

CREATE TABLE `loan_payments` (
  `payment_id` int(11) NOT NULL,
  `loan_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `payment_amount` decimal(10,2) NOT NULL,
  `payment_month` int(11) NOT NULL,
  `payment_year` int(11) NOT NULL,
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `loan_payments`
--

INSERT INTO `loan_payments` (`payment_id`, `loan_id`, `employee_id`, `payment_amount`, `payment_month`, `payment_year`, `payment_date`) VALUES
(1, 1, 1, 10000.00, 3, 2026, '2026-03-24 15:40:01');

-- --------------------------------------------------------

--
-- Table structure for table `overtime`
--

CREATE TABLE `overtime` (
  `ot_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `month` int(11) NOT NULL,
  `year` int(11) NOT NULL,
  `ot_hours` decimal(5,2) NOT NULL,
  `ot_rate` decimal(10,2) NOT NULL,
  `ot_payment` decimal(10,2) NOT NULL,
  `created_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `overtime`
--

INSERT INTO `overtime` (`ot_id`, `employee_id`, `month`, `year`, `ot_hours`, `ot_rate`, `ot_payment`, `created_date`) VALUES
(1, 1, 3, 2024, 10.00, 500.00, 5000.00, '2026-03-21 09:52:02');

-- --------------------------------------------------------

--
-- Table structure for table `paysheets`
--

CREATE TABLE `paysheets` (
  `paysheet_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `month` int(11) NOT NULL,
  `year` int(11) NOT NULL,
  `basic_salary` decimal(10,2) NOT NULL,
  `ot_payment` decimal(10,2) DEFAULT 0.00,
  `travel_allowance` decimal(10,2) DEFAULT 0.00,
  `food_allowance` decimal(10,2) DEFAULT 0.00,
  `total_earnings` decimal(10,2) NOT NULL,
  `epf_deduction` decimal(10,2) DEFAULT 0.00,
  `etf_deduction` decimal(10,2) DEFAULT 0.00,
  `apit_tax` decimal(10,2) DEFAULT 0.00,
  `loan_deduction` decimal(10,2) DEFAULT 0.00,
  `advance_deduction` decimal(10,2) DEFAULT 0.00,
  `unapproved_leave_deduction` decimal(10,2) DEFAULT 0.00,
  `total_deductions` decimal(10,2) NOT NULL,
  `net_salary` decimal(10,2) NOT NULL,
  `generated_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `paysheets`
--

INSERT INTO `paysheets` (`paysheet_id`, `employee_id`, `month`, `year`, `basic_salary`, `ot_payment`, `travel_allowance`, `food_allowance`, `total_earnings`, `epf_deduction`, `etf_deduction`, `apit_tax`, `loan_deduction`, `advance_deduction`, `unapproved_leave_deduction`, `total_deductions`, `net_salary`, `generated_date`) VALUES
(1, 1, 3, 2026, 80000.00, 0.00, 0.00, 0.00, 80000.00, 9600.00, 2400.00, 0.00, 10000.00, 0.00, 0.00, 22000.00, 58000.00, '2026-03-24 15:40:01'),
(2, 2, 3, 2026, 60000.00, 0.00, 0.00, 0.00, 60000.00, 7200.00, 1800.00, 0.00, 0.00, 0.00, 0.00, 9000.00, 51000.00, '2026-03-24 15:40:01'),
(3, 4, 3, 2026, 500000.00, 0.00, 0.00, 0.00, 500000.00, 60000.00, 15000.00, 106499.70, 0.00, 0.00, 0.00, 181499.70, 318500.30, '2026-03-25 15:35:17'),
(4, 3, 3, 2026, 100000.00, 0.00, 0.00, 0.00, 100000.00, 12000.00, 3000.00, 0.00, 0.00, 0.00, 0.00, 15000.00, 85000.00, '2026-03-25 15:35:31');

-- --------------------------------------------------------

--
-- Table structure for table `salary_advances`
--

CREATE TABLE `salary_advances` (
  `advance_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `advance_amount` decimal(10,2) NOT NULL,
  `month` int(11) NOT NULL,
  `year` int(11) NOT NULL,
  `status` enum('pending','deducted') DEFAULT 'pending',
  `request_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `setting_id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `unapproved_leaves`
--

CREATE TABLE `unapproved_leaves` (
  `unapproved_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `absence_date` date NOT NULL,
  `month` int(11) NOT NULL,
  `year` int(11) NOT NULL,
  `deduction_amount` decimal(10,2) NOT NULL,
  `reason` text DEFAULT NULL,
  `created_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `user_type` enum('admin','employee') NOT NULL,
  `linked_employee_id` int(11) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `company_id`, `username`, `password`, `user_type`, `linked_employee_id`, `status`, `created_date`) VALUES
(1, 1, 'admin', 'admin123', 'admin', NULL, 'active', '2026-03-21 09:52:02'),
(2, 2, 'admin_1', 'admin123', 'employee', NULL, 'active', '2026-03-21 16:21:58'),
(3, 3, 'admin_2', 'admin123', 'admin', NULL, 'active', '2026-03-23 10:59:10'),
(5, 1, 'sathish', 'sathish123', 'employee', 4, 'active', '2026-03-25 15:06:20');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `allowances`
--
ALTER TABLE `allowances`
  ADD PRIMARY KEY (`allowance_id`),
  ADD UNIQUE KEY `unique_employee_month` (`employee_id`,`month`,`year`);

--
-- Indexes for table `companies`
--
ALTER TABLE `companies`
  ADD PRIMARY KEY (`company_id`),
  ADD UNIQUE KEY `company_email` (`company_email`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`employee_id`),
  ADD UNIQUE KEY `employee_nic` (`employee_nic`),
  ADD KEY `company_id` (`company_id`);

--
-- Indexes for table `leave_balance`
--
ALTER TABLE `leave_balance`
  ADD PRIMARY KEY (`balance_id`),
  ADD UNIQUE KEY `unique_employee_year` (`employee_id`,`year`);

--
-- Indexes for table `leave_records`
--
ALTER TABLE `leave_records`
  ADD PRIMARY KEY (`leave_id`),
  ADD KEY `employee_id` (`employee_id`),
  ADD KEY `leave_type_id` (`leave_type_id`),
  ADD KEY `approved_by` (`approved_by`);

--
-- Indexes for table `leave_types`
--
ALTER TABLE `leave_types`
  ADD PRIMARY KEY (`leave_type_id`);

--
-- Indexes for table `loans`
--
ALTER TABLE `loans`
  ADD PRIMARY KEY (`loan_id`),
  ADD KEY `employee_id` (`employee_id`);

--
-- Indexes for table `loan_payments`
--
ALTER TABLE `loan_payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `loan_id` (`loan_id`),
  ADD KEY `employee_id` (`employee_id`);

--
-- Indexes for table `overtime`
--
ALTER TABLE `overtime`
  ADD PRIMARY KEY (`ot_id`),
  ADD UNIQUE KEY `unique_employee_month` (`employee_id`,`month`,`year`);

--
-- Indexes for table `paysheets`
--
ALTER TABLE `paysheets`
  ADD PRIMARY KEY (`paysheet_id`),
  ADD UNIQUE KEY `unique_employee_month` (`employee_id`,`month`,`year`);

--
-- Indexes for table `salary_advances`
--
ALTER TABLE `salary_advances`
  ADD PRIMARY KEY (`advance_id`),
  ADD KEY `employee_id` (`employee_id`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`setting_id`),
  ADD UNIQUE KEY `unique_company_setting` (`company_id`,`setting_key`);

--
-- Indexes for table `unapproved_leaves`
--
ALTER TABLE `unapproved_leaves`
  ADD PRIMARY KEY (`unapproved_id`),
  ADD KEY `employee_id` (`employee_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `company_id` (`company_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `allowances`
--
ALTER TABLE `allowances`
  MODIFY `allowance_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `companies`
--
ALTER TABLE `companies`
  MODIFY `company_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `employee_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `leave_balance`
--
ALTER TABLE `leave_balance`
  MODIFY `balance_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `leave_records`
--
ALTER TABLE `leave_records`
  MODIFY `leave_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `leave_types`
--
ALTER TABLE `leave_types`
  MODIFY `leave_type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `loans`
--
ALTER TABLE `loans`
  MODIFY `loan_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `loan_payments`
--
ALTER TABLE `loan_payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `overtime`
--
ALTER TABLE `overtime`
  MODIFY `ot_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `paysheets`
--
ALTER TABLE `paysheets`
  MODIFY `paysheet_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `salary_advances`
--
ALTER TABLE `salary_advances`
  MODIFY `advance_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `setting_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `unapproved_leaves`
--
ALTER TABLE `unapproved_leaves`
  MODIFY `unapproved_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `allowances`
--
ALTER TABLE `allowances`
  ADD CONSTRAINT `allowances_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE;

--
-- Constraints for table `employees`
--
ALTER TABLE `employees`
  ADD CONSTRAINT `employees_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`company_id`) ON DELETE CASCADE;

--
-- Constraints for table `leave_balance`
--
ALTER TABLE `leave_balance`
  ADD CONSTRAINT `leave_balance_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE;

--
-- Constraints for table `leave_records`
--
ALTER TABLE `leave_records`
  ADD CONSTRAINT `leave_records_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `leave_records_ibfk_2` FOREIGN KEY (`leave_type_id`) REFERENCES `leave_types` (`leave_type_id`),
  ADD CONSTRAINT `leave_records_ibfk_3` FOREIGN KEY (`approved_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `loans`
--
ALTER TABLE `loans`
  ADD CONSTRAINT `loans_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE;

--
-- Constraints for table `loan_payments`
--
ALTER TABLE `loan_payments`
  ADD CONSTRAINT `loan_payments_ibfk_1` FOREIGN KEY (`loan_id`) REFERENCES `loans` (`loan_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `loan_payments_ibfk_2` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE;

--
-- Constraints for table `overtime`
--
ALTER TABLE `overtime`
  ADD CONSTRAINT `overtime_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE;

--
-- Constraints for table `paysheets`
--
ALTER TABLE `paysheets`
  ADD CONSTRAINT `paysheets_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE;

--
-- Constraints for table `salary_advances`
--
ALTER TABLE `salary_advances`
  ADD CONSTRAINT `salary_advances_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE;

--
-- Constraints for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD CONSTRAINT `system_settings_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`company_id`) ON DELETE CASCADE;

--
-- Constraints for table `unapproved_leaves`
--
ALTER TABLE `unapproved_leaves`
  ADD CONSTRAINT `unapproved_leaves_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`company_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

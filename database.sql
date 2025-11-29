-- Create a database named 'amod_indane_db' if it doesn't exist
CREATE DATABASE IF NOT EXISTS amod_indane_db;

-- Use the created database
USE amod_indane_db;

-- Table structure for table `admins`
CREATE TABLE `admins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_token_expires_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Inserting a sample admin user with a hashed password.
INSERT INTO `admins` (`username`, `email`, `password`) VALUES
('admin', 'aadmin@gmail.com', 'admin123');

-- Table structure for table `members`
CREATE TABLE `members` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `contact_number` varchar(15) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `profile_photo` varchar(255) DEFAULT NULL,
  `qualification` varchar(255) DEFAULT NULL,
  `experience` text DEFAULT NULL,
  `address` text DEFAULT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_token_expires_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table structure for table `customers`
CREATE TABLE `customers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_no` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `mobile_no` varchar(15) NOT NULL,
  `aadhar_no` varchar(12) DEFAULT NULL,
  `ration_card_no` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `bank_details` text DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mobile_no` (`mobile_no`),
  UNIQUE KEY `customer_no` (`customer_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table structure for table `transactions`
CREATE TABLE `transactions` (
  `transaction_id` int(11) NOT NULL AUTO_INCREMENT,
  `transaction_date` date NOT NULL,
  `transaction_type` varchar(50) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `money_in` decimal(10,2) DEFAULT 0.00,
  `money_out` decimal(10,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`transaction_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table structure for table `member_history`
CREATE TABLE `member_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL,
  `member_username` varchar(50) NOT NULL,
  `action` varchar(255) NOT NULL,
  `target_type` varchar(50) DEFAULT NULL,
  `target_id` int(11) DEFAULT NULL,
  `action_timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table structure for table `cash_deposits`
CREATE TABLE `cash_deposits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `deposit_date` date NOT NULL,
  `count_500` int(11) DEFAULT 0,
  `count_200` int(11) DEFAULT 0,
  `count_100` int(11) DEFAULT 0,
  `count_50` int(11) DEFAULT 0,
  `count_10` int(11) DEFAULT 0,
  `total_amount` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

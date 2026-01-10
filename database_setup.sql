-- Create database if not exists
CREATE DATABASE IF NOT EXISTS `naroda_group` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `naroda_group`;

-- Create users table
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `firstname` varchar(100) NOT NULL,
  `lastname` varchar(100) NOT NULL,
  `firstname_hi` varchar(100) DEFAULT NULL,
  `lastname_hi` varchar(100) DEFAULT NULL,
  `firstname_gu` varchar(100) DEFAULT NULL,
  `lastname_gu` varchar(100) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(50) NOT NULL,
  `gender` varchar(20) DEFAULT NULL,
  `role` varchar(50) DEFAULT 'viewer',
  `status` varchar(20) DEFAULT 'active',
  `password` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `avatar` text DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_login` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default admin user if not exists
INSERT INTO `users` (`firstname`, `lastname`, `email`, `phone`, `role`, `status`, `password`, `created_at`) 
VALUES ('Admin', 'User', 'admin@naroda.com', '1234567890', 'admin', 'active', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW())
ON DUPLICATE KEY UPDATE email=email;

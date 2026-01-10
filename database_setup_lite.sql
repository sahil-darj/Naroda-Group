-- Users Table
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

INSERT INTO `users` (`firstname`, `lastname`, `email`, `phone`, `role`, `status`, `password`, `created_at`) 
VALUES ('Admin', 'User', 'admin@naroda.com', '1234567890', 'admin', 'active', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW())
ON DUPLICATE KEY UPDATE email=email;

-- Team Members Table
CREATE TABLE IF NOT EXISTS `team_members` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `firstname_en` varchar(100) NOT NULL,
  `lastname_en` varchar(100) NOT NULL,
  `role_en` varchar(100) DEFAULT NULL,
  `bio_en` text DEFAULT NULL,
  `professional_background_en` text DEFAULT NULL,
  `achievements_en` text DEFAULT NULL,
  `education_en` text DEFAULT NULL,
  
  `firstname_hi` varchar(100) DEFAULT NULL,
  `lastname_hi` varchar(100) DEFAULT NULL,
  `role_hi` varchar(100) DEFAULT NULL,
  `bio_hi` text DEFAULT NULL,
  `professional_background_hi` text DEFAULT NULL,
  `achievements_hi` text DEFAULT NULL,
  `education_hi` text DEFAULT NULL,
  
  `firstname_gu` varchar(100) DEFAULT NULL,
  `lastname_gu` varchar(100) DEFAULT NULL,
  `role_gu` varchar(100) DEFAULT NULL,
  `bio_gu` text DEFAULT NULL,
  `professional_background_gu` text DEFAULT NULL,
  `achievements_gu` text DEFAULT NULL,
  `education_gu` text DEFAULT NULL,
  
  `email` varchar(255) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `experience` int(11) DEFAULT 0,
  `status` varchar(20) DEFAULT 'Active',
  `linkedin_url` varchar(255) DEFAULT NULL,
  `twitter_url` varchar(255) DEFAULT NULL,
  `avatar` text DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

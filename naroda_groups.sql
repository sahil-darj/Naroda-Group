-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 23, 2025 at 10:28 AM
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
-- Database: `naroda_groups`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `GetMonthlyStats` (IN `month_param` INT, IN `year_param` INT)   BEGIN
    SELECT 
        COUNT(*) as total_applications,
        SUM(CASE WHEN status = 'new' THEN 1 ELSE 0 END) as new_applications,
        SUM(CASE WHEN status = 'hired' THEN 1 ELSE 0 END) as hired_applications
    FROM job_applications 
    WHERE MONTH(applied_date) = month_param AND YEAR(applied_date) = year_param;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_get_monthly_stats` (IN `month` INT, IN `year` INT)   BEGIN
    SELECT 
        COUNT(DISTINCT c.id) as jobs_posted,
        COUNT(DISTINCT a.id) as applications_received,
        SUM(CASE WHEN a.status = 'hired' THEN 1 ELSE 0 END) as candidates_hired,
        d.name_en as top_department
    FROM career c
    LEFT JOIN job_applications a ON c.id = a.job_id 
        AND MONTH(a.applied_date) = month 
        AND YEAR(a.applied_date) = year
    LEFT JOIN departments d ON c.department = d.name_en
    WHERE MONTH(c.posted_date) = month 
        AND YEAR(c.posted_date) = year
    GROUP BY d.name_en
    ORDER BY applications_received DESC
    LIMIT 1;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `UpdateApplicationCount` (IN `job_id_param` INT)   BEGIN
    UPDATE career 
    SET applications = (SELECT COUNT(*) FROM job_applications WHERE job_id = job_id_param)
    WHERE id = job_id_param;
END$$

--
-- Functions
--
CREATE DEFINER=`root`@`localhost` FUNCTION `GetActiveJobsCount` () RETURNS INT(11) DETERMINISTIC BEGIN
    DECLARE active_count INT;
    SELECT COUNT(*) INTO active_count FROM career WHERE job_status = 'active';
    RETURN active_count;
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `GetTotalApplicationsCount` () RETURNS INT(11) DETERMINISTIC BEGIN
    DECLARE total_count INT;
    SELECT COUNT(*) INTO total_count FROM job_applications;
    RETURN total_count;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `faq`
--

CREATE TABLE `faq` (
  `id` int(11) NOT NULL,
  `question` varchar(500) NOT NULL,
  `question_hi` varchar(500) DEFAULT NULL,
  `question_gu` varchar(500) DEFAULT NULL,
  `answer` longtext DEFAULT NULL,
  `answer_hi` longtext DEFAULT NULL,
  `answer_gu` longtext DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'active',
  `display_order` int(11) DEFAULT 1,
  `tags` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `faq`
--

INSERT INTO `faq` (`id`, `question`, `question_hi`, `question_gu`, `answer`, `answer_hi`, `answer_gu`, `category`, `status`, `display_order`, `tags`, `created_at`, `updated_at`) VALUES
(1, 'What is Naroda Groups?', 'नरोडा ग्रुप्स क्या है?', 'નરોડા ગ્રુપ્સ શું છે?', 'Naroda Groups is a leading enterprise solutions company founded in 2010.', 'नरोडा ग्रुप्स 2010 में स्थापित एक अग्रणी एंटरप्राइज़ सॉल्यूशंस कंपनी है।', 'નરોડા ગ્રુપ્સ 2010 માં સ્થાપિત અગ્રણી એન્ટરપ્રાઇઝ સોલ્યુશન્સ કંપની છે.', 'about', 'active', 1, 'company,information', '2025-12-22 12:35:34', '2025-12-22 12:35:34'),
(2, 'How can I apply for a job at Naroda Groups?', 'मैं नरोडा ग्रुप्स में नौकरी के लिए कैसे आवेदन कर सकता हूँ?', 'હું નરોડા ગ્રુપ્સમાં નોકરી માટે કેવી રીતે અરજી કરી શકું?', 'Browse Careers page and submit resume.', 'कैरियर्स पृष्ठ देखें और रिज्यूमे सबमिट करें।', 'કેરિયર્સ પૃષ્ઠ જુઓ અને રિઝ્યુમ સબમિટ કરો.', 'careers', 'active', 2, 'jobs,careers', '2025-12-22 12:35:34', '2025-12-22 12:35:34'),
(3, 'devil', 'Devil', 'શેતાન', 'devil', 'Devil', 'શેતાન', 'projects', 'active', 1, 'devil', '2025-12-22 12:41:10', '2025-12-22 12:42:26');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `faq`
--
ALTER TABLE `faq`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_order` (`display_order`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `faq`
--
ALTER TABLE `faq`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

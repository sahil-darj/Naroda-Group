<?php
require_once 'config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    // Create Jobs Table
    $sql = "CREATE TABLE IF NOT EXISTS career_jobs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title_en VARCHAR(255) NOT NULL,
        title_hi VARCHAR(255),
        title_gu VARCHAR(255),
        description_en TEXT,
        description_hi TEXT,
        description_gu TEXT,
        requirements_en TEXT,
        requirements_hi TEXT,
        requirements_gu TEXT,
        benefits_en TEXT,
        benefits_hi TEXT,
        benefits_gu TEXT,
        department VARCHAR(100),
        type VARCHAR(50),
        location_en VARCHAR(255),
        location_hi VARCHAR(255),
        location_gu VARCHAR(255),
        experience_en VARCHAR(100),
        experience_hi VARCHAR(100),
        experience_gu VARCHAR(100),
        salary_en VARCHAR(100),
        salary_hi VARCHAR(100),
        salary_gu VARCHAR(100),
        vacancies INT DEFAULT 1,
        status VARCHAR(50) DEFAULT 'draft',
        deadline DATE,
        posted_date DATE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    $db->exec($sql);
    echo "Table 'career_jobs' created or already exists.<br>";

    // Create Departments Table
    $sql = "CREATE TABLE IF NOT EXISTS career_departments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        slug VARCHAR(100) UNIQUE,
        name_en VARCHAR(255) NOT NULL,
        name_hi VARCHAR(255),
        name_gu VARCHAR(255),
        head VARCHAR(255),
        description_en TEXT,
        description_hi TEXT,
        description_gu TEXT,
        status VARCHAR(50) DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    $db->exec($sql);
    echo "Table 'career_departments' created or already exists.<br>";

    // Create Applications Table
    $sql = "CREATE TABLE IF NOT EXISTS career_applications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        job_id INT,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        phone VARCHAR(50),
        experience VARCHAR(100),
        education VARCHAR(255),
        cover_letter TEXT,
        resume_path VARCHAR(255),
        status VARCHAR(50) DEFAULT 'new',
        applied_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (job_id) REFERENCES career_jobs(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    $db->exec($sql);
    echo "Table 'career_applications' created or already exists.<br>";

    echo "Database setup completed successfully.";

} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

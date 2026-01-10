<?php
// add_job.php - Handle adding new jobs
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Include database connection
require_once __DIR__ . '/config/database.php';

try {
    $database = new Database();
    $pdo = $database->getConnection();

    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception("Invalid JSON input");
    }

    // Basic Validation
    if (empty($input['title']['en']) || empty($input['department'])) {
        throw new Exception("Job Title (English) and Department are required");
    }

    // Prepare data handling nulls for optional fields
    $title_en = $input['title']['en'] ?? '';
    $title_hi = $input['title']['hi'] ?? '';
    $title_gu = $input['title']['gu'] ?? '';
    
    $description_en = $input['description']['en'] ?? '';
    $description_hi = $input['description']['hi'] ?? '';
    $description_gu = $input['description']['gu'] ?? '';
    
    $requirements_en = $input['requirements']['en'] ?? '';
    $requirements_hi = $input['requirements']['hi'] ?? '';
    $requirements_gu = $input['requirements']['gu'] ?? '';
    
    $benefits_en = $input['benefits']['en'] ?? '';
    $benefits_hi = $input['benefits']['hi'] ?? '';
    $benefits_gu = $input['benefits']['gu'] ?? '';
    
    $department = $input['department'] ?? '';
    $type = $input['type'] ?? '';
    
    $location_en = $input['location']['en'] ?? '';
    // Assuming location input might be just a string from frontend for now, or object
    // If frontend sends string for location, handle it.
    if (is_string($input['location'])) {
        $location_en = $input['location'];
        $location_hi = $input['location']; // Fallback
        $location_gu = $input['location']; // Fallback
    } else {
        $location_en = $input['location']['en'] ?? '';
        $location_hi = $input['location']['hi'] ?? '';
        $location_gu = $input['location']['gu'] ?? '';
    }

    $experience_en = $input['experience']['en'] ?? '';
    if (is_string($input['experience'])) {
         $experience_en = $input['experience'];
         $experience_hi = $input['experience'];
         $experience_gu = $input['experience'];
    } else {
        $experience_en = $input['experience']['en'] ?? '';
        $experience_hi = $input['experience']['hi'] ?? '';
        $experience_gu = $input['experience']['gu'] ?? '';
    }

    $salary_en = $input['salary']['en'] ?? '';
     if (is_string($input['salary'])) {
         $salary_en = $input['salary'];
         $salary_hi = $input['salary'];
         $salary_gu = $input['salary'];
    } else {
         $salary_en = $input['salary']['en'] ?? '';
         $salary_hi = $input['salary']['hi'] ?? '';
         $salary_gu = $input['salary']['gu'] ?? '';
    }

    $vacancies = isset($input['vacancies']) ? (int)$input['vacancies'] : 1;
    $status = $input['status'] ?? 'draft';
    $deadline = !empty($input['deadline']) ? $input['deadline'] : null;
    $posted_date = !empty($input['posted_date']) ? $input['posted_date'] : date('Y-m-d');

    // Insert Query
    $sql = "INSERT INTO career_jobs (
        title_en, title_hi, title_gu,
        description_en, description_hi, description_gu,
        requirements_en, requirements_hi, requirements_gu,
        benefits_en, benefits_hi, benefits_gu,
        department, type,
        location_en, location_hi, location_gu,
        experience_en, experience_hi, experience_gu,
        salary_en, salary_hi, salary_gu,
        vacancies, status, deadline, posted_date
    ) VALUES (
        :title_en, :title_hi, :title_gu,
        :description_en, :description_hi, :description_gu,
        :requirements_en, :requirements_hi, :requirements_gu,
        :benefits_en, :benefits_hi, :benefits_gu,
        :department, :type,
        :location_en, :location_hi, :location_gu,
        :experience_en, :experience_hi, :experience_gu,
        :salary_en, :salary_hi, :salary_gu,
        :vacancies, :status, :deadline, :posted_date
    )";

    $stmt = $pdo->prepare($sql);
    
    $stmt->execute([
        ':title_en' => $title_en, ':title_hi' => $title_hi, ':title_gu' => $title_gu,
        ':description_en' => $description_en, ':description_hi' => $description_hi, ':description_gu' => $description_gu,
        ':requirements_en' => $requirements_en, ':requirements_hi' => $requirements_hi, ':requirements_gu' => $requirements_gu,
        ':benefits_en' => $benefits_en, ':benefits_hi' => $benefits_hi, ':benefits_gu' => $benefits_gu,
        ':department' => $department, ':type' => $type,
        ':location_en' => $location_en, ':location_hi' => $location_hi, ':location_gu' => $location_gu,
        ':experience_en' => $experience_en, ':experience_hi' => $experience_hi, ':experience_gu' => $experience_gu,
        ':salary_en' => $salary_en, ':salary_hi' => $salary_hi, ':salary_gu' => $salary_gu,
        ':vacancies' => $vacancies, ':status' => $status,
        ':deadline' => $deadline, ':posted_date' => $posted_date
    ]);

    $newId = $pdo->lastInsertId();

    echo json_encode(['success' => true, 'message' => 'Job added successfully', 'id' => $newId]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>

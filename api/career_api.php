<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../models/Career.php';

$career = new Career();
$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Handle OPTIONS request for CORS
if ($method === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$response = ['success' => false, 'message' => 'Invalid Request'];

if ($method === 'GET') {
    switch ($action) {
        case 'get_jobs':
            $search = isset($_GET['search']) ? $_GET['search'] : null;
            $department = isset($_GET['department']) ? $_GET['department'] : null;
            $status = isset($_GET['status']) ? $_GET['status'] : null;
            $type = isset($_GET['type']) ? $_GET['type'] : null;
            
            $jobs = $career->getAllJobs($search, $department, $status, $type);
            
            // Format for frontend
            $formattedJobs = [];
            foreach ($jobs as $job) {
                $formattedJobs[] = [
                    'id' => $job['id'],
                    'title' => ['en' => $job['title_en'], 'hi' => $job['title_hi'], 'gu' => $job['title_gu']],
                    'description' => ['en' => $job['description_en'], 'hi' => $job['description_hi'], 'gu' => $job['description_gu']],
                    'requirements' => ['en' => $job['requirements_en'], 'hi' => $job['requirements_hi'], 'gu' => $job['requirements_gu']],
                    'benefits' => ['en' => $job['benefits_en'], 'hi' => $job['benefits_hi'], 'gu' => $job['benefits_gu']],
                    'department' => $job['department'],
                    'type' => $job['type'],
                    'location' => ['en' => $job['location_en'], 'hi' => $job['location_hi'], 'gu' => $job['location_gu']],
                    'experience' => ['en' => $job['experience_en'], 'hi' => $job['experience_hi'], 'gu' => $job['experience_gu']],
                    'salary' => ['en' => $job['salary_en'], 'hi' => $job['salary_hi'], 'gu' => $job['salary_gu']],
                    'vacancies' => $job['vacancies'],
                    'status' => $job['status'],
                    'deadline' => $job['deadline'],
                    'posted' => $job['posted_date'],
                    'applications' => $job['applications'] // Mock or computed
                ];
            }
            $response = ['success' => true, 'data' => $formattedJobs];
            break;

        case 'get_departments':
            $departments = $career->getAllDepartments();
            $formattedDepts = [];
            foreach ($departments as $dept) {
                $formattedDepts[] = [
                    'id' => $dept['id'],
                    'slug' => $dept['slug'],
                    'name' => ['en' => $dept['name_en'], 'hi' => $dept['name_hi'], 'gu' => $dept['name_gu']],
                    'head' => $dept['head'],
                    'description' => ['en' => $dept['description_en'], 'hi' => $dept['description_hi'], 'gu' => $dept['description_gu']],
                    'status' => $dept['status'],
                    'openPositions' => $dept['openPositions'],
                    'totalEmployees' => $dept['totalEmployees']
                ];
            }
            $response = ['success' => true, 'data' => $formattedDepts];
            break;

        case 'get_applications':
            $applications = $career->getAllApplications();
            // Format if needed
            $formattedApps = [];
            foreach ($applications as $app) {
                $formattedApps[] = [
                    'id' => $app['id'],
                    'name' => $app['name'],
                    'email' => $app['email'],
                    'job' => $app['job_title'] ?? 'Unknown Job', // from join
                    'applied' => $app['applied_date'],
                    'status' => $app['status'],
                    'experience' => $app['experience'],
                    'phone' => $app['phone'],
                    'education' => $app['education'],
                    'coverLetter' => $app['cover_letter'],
                    'resume' => $app['resume_path']
                ];
            }
            $response = ['success' => true, 'data' => $formattedApps];
            break;

        case 'get_inquiries':
            $inquiries = $career->getAllInquiries();
            $response = ['success' => true, 'data' => $inquiries];
            break;
            
        default:
             $response = ['success' => false, 'message' => 'Unknown action'];
             break;
    }
} elseif ($method === 'POST') {
    $data = [];
    if (strpos($_SERVER['CONTENT_TYPE'] ?? '', 'multipart/form-data') === false) {
        $rawInput = file_get_contents("php://input");
        $data = json_decode($rawInput, true);
        
        if (json_last_error() !== JSON_ERROR_NONE && !empty($rawInput)) {
            echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
            exit;
        }
    } else {
        $data = $_POST;
    }
    
    switch ($action) {
        case 'save_job':
            // Transform frontend structure to DB structure
            $dbData = [
                'id' => $data['id'] ?? null,
                'title_en' => $data['title']['en'] ?? '',
                'title_hi' => $data['title']['hi'] ?? '',
                'title_gu' => $data['title']['gu'] ?? '',
                'description_en' => $data['description']['en'] ?? '',
                'description_hi' => $data['description']['hi'] ?? '',
                'description_gu' => $data['description']['gu'] ?? '',
                'requirements_en' => $data['requirements']['en'] ?? '',
                'requirements_hi' => $data['requirements']['hi'] ?? '',
                'requirements_gu' => $data['requirements']['gu'] ?? '',
                'benefits_en' => $data['benefits']['en'] ?? '',
                'benefits_hi' => $data['benefits']['hi'] ?? '',
                'benefits_gu' => $data['benefits']['gu'] ?? '',
                'department' => $data['department'] ?? '',
                'type' => $data['type'] ?? '',
                'location_en' => $data['location']['en'] ?? '',
                'location_hi' => $data['location']['hi'] ?? '',
                'location_gu' => $data['location']['gu'] ?? '',
                'experience_en' => $data['experience']['en'] ?? '',
                'experience_hi' => $data['experience']['hi'] ?? '',
                'experience_gu' => $data['experience']['gu'] ?? '',
                'salary_en' => $data['salary']['en'] ?? '',
                'salary_hi' => $data['salary']['hi'] ?? '',
                'salary_gu' => $data['salary']['gu'] ?? '',
                'vacancies' => $data['vacancies'] ?? 1,
                'status' => $data['status'] ?? 'draft',
                'deadline' => $data['deadline'] ?? null,
                'posted_date' => $data['posted'] ?? date('Y-m-d')
            ];
            
            $result = $career->saveJob($dbData);
            $response = $result;
            break;

        case 'delete_job':
            if (isset($data['id'])) {
                $response = $career->deleteJob($data['id']);
            }
            break;

        case 'save_department':
            $dbData = [
                'id' => $data['id'] ?? null,
                'slug' => isset($data['name']['en']) ? strtolower(str_replace(' ', '-', $data['name']['en'])) : 'dept-' . time(),
                'name_en' => $data['name']['en'] ?? '',
                'name_hi' => $data['name']['hi'] ?? '',
                'name_gu' => $data['name']['gu'] ?? '',
                'head' => $data['head'] ?? '',
                'description_en' => $data['description']['en'] ?? '',
                'description_hi' => $data['description']['hi'] ?? '',
                'description_gu' => $data['description']['gu'] ?? '',
                'status' => $data['status'] ?? 'active'
            ];
             $response = $career->saveDepartment($dbData);
            break;

        case 'delete_department':
            if (isset($data['id'])) {
                $response = $career->deleteDepartment($data['id']);
            }
            break;

        case 'delete_application':
             if (isset($data['id'])) {
                $response = $career->deleteApplication($data['id']);
            }
            break;
            
        case 'update_application_status':
            if (isset($data['id']) && isset($data['status'])) {
                 $response = $career->saveApplicationAndStatus($data['id'], $data['status']);
            }
            break;

        case 'submit_inquiry':
             $response = $career->saveInquiry($data);
             break;

        case 'submit_application':
            // Since this involves file upload, we use $_POST and $_FILES instead of raw input
            $data = $_POST;
            $files = $_FILES;
            
            $resumePath = null;
            if (isset($files['resume']) && $files['resume']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = '../uploads/resumes/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                $fileName = time() . '_' . basename($files['resume']['name']);
                $targetFile = $uploadDir . $fileName;
                
                if (move_uploaded_file($files['resume']['tmp_name'], $targetFile)) {
                    $resumePath = 'uploads/resumes/' . $fileName;
                }
            }
            
            $dbData = [
                'job_id' => $data['job_id'] ?? null,
                'name' => $data['name'] ?? '',
                'email' => $data['email'] ?? '',
                'phone' => $data['phone'] ?? '',
                'experience' => $data['experience'] ?? '',
                'education' => $data['education'] ?? '',
                'cover_letter' => $data['cover_letter'] ?? '',
                'resume_path' => $resumePath,
                'status' => 'new',
                'applied_date' => date('Y-m-d H:i:s')
            ];
            
            $response = $career->saveApplication($dbData);
            break;

        case 'delete_inquiry':
             $response = $career->deleteInquiry($data['id']);
             break;

        default:
             $response = ['success' => false, 'message' => 'Unknown POST action'];
             break;
    }
}

echo json_encode($response);
?>

<?php
class ApplicationApiController extends ApiController {
    public function __construct(){
        $this->applicationModel = $this->model('JobApplication');
    }

    // GET /api/v1/applications
    public function index(){
        $studentId = $_GET['student_id'] ?? null;
        if(!$studentId){
            $this->jsonResponse(['error' => 'Student ID required'], 400);
        }

        $applications = $this->applicationModel->getApplicationsByStudent($studentId);
        $this->jsonResponse($applications);
    }

    // GET /api/v1/applications/{id}
    public function show($id){
        $application = $this->applicationModel->getApplicationById($id);
        if($application){
            $this->jsonResponse($application);
        } else {
            $this->jsonResponse(['error' => 'Application not found'], 404);
        }
    }

    // POST /api/v1/applications/create
    public function create(){
        if($_SERVER['REQUEST_METHOD'] !== 'POST'){
            $this->jsonResponse(['error' => 'Method not allowed'], 405);
        }

        $input = json_decode(file_get_contents('php://input'), true);
        
        $data = [
            'student_id' => $input['student_id'] ?? null,
            'job_id' => $input['job_id'] ?? null,
            'cover_letter' => $input['cover_letter'] ?? '',
            'resume_path' => $input['resume_path'] ?? ''
        ];

        if(!$data['student_id'] || !$data['job_id']){
            $this->jsonResponse(['error' => 'Student ID and Job ID required'], 400);
        }

        if($this->applicationModel->createApplication($data)){
            $this->jsonResponse(['success' => true, 'message' => 'Application submitted']);
        } else {
            $this->jsonResponse(['error' => 'Failed to submit application'], 500);
        }
    }
}
?>
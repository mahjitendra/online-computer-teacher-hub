<?php
class StudentJobController extends Controller {
    public function __construct(){
        if(!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'student'){
            header('location: ' . URLROOT . '/auth/login');
            exit();
        }
        $this->jobModel = $this->model('Job');
        $this->applicationModel = $this->model('JobApplication');
    }

    public function index(){
        $jobs = $this->jobModel->getAllActiveJobs();
        $categories = $this->jobModel->getCategories();

        $data = [
            'title' => 'Job Opportunities',
            'jobs' => $jobs,
            'categories' => $categories
        ];

        $this->view('pages/student/jobs', $data);
    }

    public function applications(){
        $studentId = $_SESSION['user_id'];
        $applications = $this->applicationModel->getApplicationsByStudent($studentId);

        $data = [
            'title' => 'My Applications',
            'applications' => $applications
        ];

        $this->view('pages/student/job-applications', $data);
    }

    public function apply($jobId){
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            $data = [
                'student_id' => $_SESSION['user_id'],
                'job_id' => $jobId,
                'cover_letter' => trim($_POST['cover_letter']),
                'resume_path' => $this->handleResumeUpload()
            ];

            if($this->applicationModel->createApplication($data)){
                header('location: ' . URLROOT . '/student/jobs/applications?success=applied');
            } else {
                header('location: ' . URLROOT . '/student/jobs?error=application');
            }
        }
    }

    private function handleResumeUpload(){
        if(isset($_FILES['resume']) && $_FILES['resume']['error'] == 0){
            $uploadDir = 'uploads/resumes/';
            $fileName = $_SESSION['user_id'] . '_' . time() . '.pdf';
            $uploadPath = $uploadDir . $fileName;

            if(move_uploaded_file($_FILES['resume']['tmp_name'], $uploadPath)){
                return $uploadPath;
            }
        }
        return null;
    }
}
?>
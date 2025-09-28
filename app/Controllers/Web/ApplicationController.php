<?php
class ApplicationController extends Controller {
    public function __construct(){
        if(!isset($_SESSION['user_id'])){
            header('location: ' . URLROOT . '/auth/login');
            exit();
        }
        $this->applicationModel = $this->model('JobApplication');
        $this->jobModel = $this->model('Job');
    }

    public function index(){
        $applications = $this->applicationModel->getApplicationsByStudent($_SESSION['user_id']);

        $data = [
            'applications' => $applications
        ];

        $this->view('pages/student/job-applications', $data);
    }

    public function show($id){
        $application = $this->applicationModel->getApplicationById($id);
        
        // Check if this application belongs to the current user
        if($application->student_id != $_SESSION['user_id']){
            header('location: ' . URLROOT . '/applications');
            return;
        }

        $job = $this->jobModel->getJobById($application->job_id);

        $data = [
            'application' => $application,
            'job' => $job
        ];

        $this->view('pages/student/application-details', $data);
    }

    public function withdraw($id){
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            $application = $this->applicationModel->getApplicationById($id);
            
            // Check if this application belongs to the current user
            if($application->student_id != $_SESSION['user_id']){
                header('location: ' . URLROOT . '/applications');
                return;
            }

            if($this->applicationModel->withdrawApplication($id)){
                header('location: ' . URLROOT . '/applications?success=withdrawn');
            } else {
                header('location: ' . URLROOT . '/applications?error=withdraw');
            }
        }
    }
}
?>
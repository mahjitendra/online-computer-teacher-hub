<?php
class JobController extends Controller {
    public function __construct(){
        $this->jobModel = $this->model('Job');
    }

    public function index(){
        $jobs = $this->jobModel->getAllActiveJobs();
        $categories = $this->jobModel->getCategories();

        $data = [
            'jobs' => $jobs,
            'categories' => $categories
        ];

        $this->view('pages/frontend/jobs', $data);
    }

    public function show($id){
        $job = $this->jobModel->getJobById($id);

        $data = [
            'job' => $job
        ];

        $this->view('pages/frontend/job_details', $data);
    }

    public function apply($job_id){
        if(!isset($_SESSION['user_id'])){
            header('location: ' . URLROOT . '/auth/login');
            return;
        }

        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            // File upload logic would be complex. For now, we'll simulate it.
            // A real implementation would require a robust file upload service.
            $resume_path = 'uploads/resumes/' . $_SESSION['user_id'] . '_' . time() . '.pdf';

            $data = [
                'job_id' => $job_id,
                'student_id' => $_SESSION['user_id'],
                'cover_letter' => trim($_POST['cover_letter']),
                'resume_path' => $resume_path // Placeholder
            ];

            if($this->jobModel->applyForJob($data)){
                // Redirect with success message
                header('location: ' . URLROOT . '/jobs/show/' . $job_id . '?applied=true');
            } else {
                die('Something went wrong with the application.');
            }

        } else {
            header('location: ' . URLROOT . '/jobs');
        }
    }
}
?>
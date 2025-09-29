<?php
class JobManagementController extends Controller {
    public function __construct(){
        if(!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin'){
            header('location: ' . URLROOT . '/auth/login');
            exit();
        }
        $this->jobModel = $this->model('Job');
        $this->applicationModel = $this->model('JobApplication');
        $this->jobCategoryModel = $this->model('JobCategory');
    }

    public function index(){
        $jobs = $this->jobModel->getAllJobsForAdmin();
        $categories = $this->jobCategoryModel->getAllCategories();
        
        $data = [
            'title' => 'Job Management',
            'jobs' => $jobs,
            'categories' => $categories
        ];

        $this->view('pages/admin/jobs', $data);
    }

    public function applications(){
        $applications = $this->applicationModel->getAllApplicationsForAdmin();
        
        $data = [
            'title' => 'Job Applications',
            'applications' => $applications
        ];

        $this->view('pages/admin/applications', $data);
    }

    public function categories(){
        $categories = $this->jobCategoryModel->getCategoriesWithJobCount();
        
        $data = [
            'title' => 'Job Categories',
            'categories' => $categories
        ];

        $this->view('pages/admin/job-categories', $data);
    }

    public function createJob(){
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            $data = [
                'title' => trim($_POST['title']),
                'description' => trim($_POST['description']),
                'company' => trim($_POST['company']),
                'location' => trim($_POST['location']),
                'category_id' => intval($_POST['category_id']),
                'posted_by' => $_SESSION['user_id']
            ];

            if($this->jobModel->createJob($data)){
                header('location: ' . URLROOT . '/admin/jobs?success=created');
            } else {
                header('location: ' . URLROOT . '/admin/jobs?error=create');
            }
        } else {
            $categories = $this->jobCategoryModel->getAllCategories();
            
            $data = [
                'title' => 'Create Job',
                'categories' => $categories
            ];

            $this->view('pages/admin/create-job', $data);
        }
    }
}
?>
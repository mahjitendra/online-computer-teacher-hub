<?php
class AdminController extends Controller {
    public function __construct(){
        if(!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin'){
            header('location: ' . URLROOT . '/auth/login');
            exit();
        }
        $this->userModel = $this->model('User');
        $this->courseModel = $this->model('Course');
        $this->jobModel = $this->model('Job');
        $this->paymentModel = $this->model('Payment');
    }

    public function index(){
        $stats = [
            'total_users' => $this->userModel->getTotalUsers(),
            'total_courses' => $this->courseModel->getTotalCourses(),
            'total_jobs' => $this->jobModel->getTotalJobs(),
            'total_revenue' => $this->paymentModel->getTotalRevenue()
        ];

        $recentUsers = $this->userModel->getRecentUsers(10);
        $recentCourses = $this->courseModel->getRecentCourses(10);

        $data = [
            'stats' => $stats,
            'recentUsers' => $recentUsers,
            'recentCourses' => $recentCourses
        ];

        $this->view('pages/admin/dashboard', $data);
    }

    public function users(){
        $users = $this->userModel->getAllUsers();

        $data = [
            'users' => $users
        ];

        $this->view('pages/admin/users', $data);
    }

    public function courses(){
        $courses = $this->courseModel->getAllCoursesForAdmin();

        $data = [
            'courses' => $courses
        ];

        $this->view('pages/admin/courses', $data);
    }

    public function approveCourse($id){
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            if($this->courseModel->updateCourseStatus($id, 'approved')){
                header('location: ' . URLROOT . '/admin/courses?success=approved');
            } else {
                header('location: ' . URLROOT . '/admin/courses?error=approve');
            }
        }
    }

    public function rejectCourse($id){
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            if($this->courseModel->updateCourseStatus($id, 'rejected')){
                header('location: ' . URLROOT . '/admin/courses?success=rejected');
            } else {
                header('location: ' . URLROOT . '/admin/courses?error=reject');
            }
        }
    }

    public function jobs(){
        $jobs = $this->jobModel->getAllJobsForAdmin();

        $data = [
            'jobs' => $jobs
        ];

        $this->view('pages/admin/jobs', $data);
    }

    public function payments(){
        $payments = $this->paymentModel->getAllPayments();

        $data = [
            'payments' => $payments
        ];

        $this->view('pages/admin/payments', $data);
    }

    public function settings(){
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            // Handle settings update
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
            
            // Update settings logic here
            header('location: ' . URLROOT . '/admin/settings?success=updated');
        } else {
            $data = [
                'settings' => [] // Load current settings
            ];

            $this->view('pages/admin/settings', $data);
        }
    }
}
?>
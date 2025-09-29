<?php
class StudentDashboardController extends Controller {
    public function __construct(){
        if(!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'student'){
            header('location: ' . URLROOT . '/auth/login');
            exit();
        }
        $this->studentModel = $this->model('Student');
        $this->courseModel = $this->model('Course');
        $this->examModel = $this->model('Exam');
    }

    public function index(){
        $studentId = $_SESSION['user_id'];
        
        // Get student statistics
        $stats = $this->studentModel->getStudentStats($studentId);
        
        // Get recent activity
        $recentActivity = $this->studentModel->getRecentActivity($studentId, 5);
        
        // Get enrolled courses
        $enrolledCourses = $this->courseModel->getEnrolledCourses($studentId);
        
        // Get upcoming exams
        $upcomingExams = $this->examModel->getUpcomingExams($studentId);
        
        // Get progress data
        $progress = $this->studentModel->getStudentProgress($studentId);

        $data = [
            'title' => 'Student Dashboard',
            'stats' => $stats,
            'recentActivity' => $recentActivity,
            'enrolledCourses' => $enrolledCourses,
            'upcomingExams' => $upcomingExams,
            'progress' => $progress
        ];

        $this->view('pages/student/dashboard', $data);
    }
}
?>
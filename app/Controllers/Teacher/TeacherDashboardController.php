<?php
class TeacherDashboardController extends Controller {
    public function __construct(){
        if(!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'teacher'){
            header('location: ' . URLROOT . '/auth/login');
            exit();
        }
        $this->teacherModel = $this->model('Teacher');
        $this->courseModel = $this->model('Course');
    }

    public function index(){
        $teacherId = $_SESSION['user_id'];
        
        // Get teacher statistics
        $stats = $this->teacherModel->getTeacherStats($teacherId);
        
        // Get recent courses
        $recentCourses = $this->courseModel->getCoursesByTeacher($teacherId, 5);
        
        // Get earnings data
        $earnings = $this->teacherModel->getTeacherEarnings($teacherId, 'month');
        
        // Get students
        $students = $this->teacherModel->getStudentsByTeacher($teacherId);

        $data = [
            'title' => 'Teacher Dashboard',
            'stats' => $stats,
            'recentCourses' => $recentCourses,
            'earnings' => $earnings,
            'students' => $students
        ];

        $this->view('pages/teacher/dashboard', $data);
    }
}
?>
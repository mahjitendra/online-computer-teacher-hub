<?php
class StudentCourseController extends Controller {
    public function __construct(){
        if(!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'student'){
            header('location: ' . URLROOT . '/auth/login');
            exit();
        }
        $this->courseModel = $this->model('Course');
        $this->enrollmentModel = $this->model('Enrollment');
        $this->tutorialModel = $this->model('Tutorial');
    }

    public function index(){
        $studentId = $_SESSION['user_id'];
        $enrolledCourses = $this->courseModel->getEnrolledCourses($studentId);

        $data = [
            'title' => 'My Courses',
            'courses' => $enrolledCourses
        ];

        $this->view('pages/student/courses', $data);
    }

    public function show($id){
        $studentId = $_SESSION['user_id'];
        
        // Check if student is enrolled
        if(!$this->enrollmentModel->isStudentEnrolled($studentId, $id)){
            header('location: ' . URLROOT . '/courses');
            return;
        }

        $course = $this->courseModel->getCourseById($id);
        $tutorials = $this->tutorialModel->getTutorialsByCourse($id);
        $progress = $this->enrollmentModel->getCourseProgress($studentId, $id);

        $data = [
            'course' => $course,
            'tutorials' => $tutorials,
            'progress' => $progress
        ];

        $this->view('pages/student/course-details', $data);
    }
}
?>
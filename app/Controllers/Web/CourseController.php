<?php
class CourseController extends Controller {
    public function __construct(){
        // Middleware to ensure user is logged in can be added here
        $this->courseModel = $this->model('Course');
        $this->enrollmentModel = $this->model('Enrollment');
    }

    // List all available courses
    public function index(){
        $courses = $this->courseModel->getAllCourses(); // This method needs to be created in Course model

        $data = [
            'courses' => $courses
        ];

        $this->view('pages/frontend/courses', $data);
    }

    // Show single course details
    public function show($id){
        $course = $this->courseModel->getCourseById($id);
        $isEnrolled = false;
        if(isset($_SESSION['user_id'])){
            $isEnrolled = $this->enrollmentModel->isStudentEnrolled($_SESSION['user_id'], $id);
        }

        $data = [
            'course' => $course,
            'isEnrolled' => $isEnrolled
            // We'll add tutorials to this data later
        ];

        $this->view('pages/frontend/course_details', $data);
    }

    // Enroll in a course
    public function enroll($course_id){
        if(!isset($_SESSION['user_id'])){
            header('location: ' . URLROOT . '/auth/login');
            return;
        }

        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            $data = [
                'student_id' => $_SESSION['user_id'],
                'course_id' => $course_id
            ];

            if($this->enrollmentModel->enrollStudent($data)){
                // Redirect back to the course page with a success message
                header('location: ' . URLROOT . '/courses/show/' . $course_id);
            } else {
                die('Something went wrong with enrollment');
            }
        } else {
            header('location: ' . URLROOT . '/courses');
        }
    }
}
?>
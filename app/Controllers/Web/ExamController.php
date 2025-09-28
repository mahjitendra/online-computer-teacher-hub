<?php
class ExamController extends Controller {
    public function __construct(){
        $this->examModel = $this->model('Exam');
        $this->courseModel = $this->model('Course');
        $this->enrollmentModel = $this->model('Enrollment');
    }

    public function index(){
        if(!isset($_SESSION['user_id'])){
            header('location: ' . URLROOT . '/auth/login');
            return;
        }

        $enrolledCourses = $this->enrollmentModel->getEnrolledCourses($_SESSION['user_id']);
        $availableExams = [];
        
        foreach($enrolledCourses as $course){
            $exams = $this->examModel->getExamsByCourse($course->course_id);
            $availableExams = array_merge($availableExams, $exams);
        }

        $data = [
            'exams' => $availableExams
        ];

        $this->view('pages/student/exams', $data);
    }

    public function show($id){
        if(!isset($_SESSION['user_id'])){
            header('location: ' . URLROOT . '/auth/login');
            return;
        }

        $exam = $this->examModel->getExamById($id);
        $course = $this->courseModel->getCourseById($exam->course_id);
        
        // Check if user is enrolled
        $isEnrolled = $this->enrollmentModel->isStudentEnrolled($_SESSION['user_id'], $exam->course_id);
        
        if(!$isEnrolled){
            header('location: ' . URLROOT . '/courses/show/' . $exam->course_id);
            return;
        }

        // Check if user has already attempted this exam
        $attempts = $this->examModel->getAttemptsByStudentAndExam($_SESSION['user_id'], $id);

        $data = [
            'exam' => $exam,
            'course' => $course,
            'attempts' => $attempts
        ];

        $this->view('pages/student/exam-details', $data);
    }
}
?>
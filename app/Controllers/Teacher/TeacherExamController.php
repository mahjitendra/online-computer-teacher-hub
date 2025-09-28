<?php
class TeacherExamController extends Controller {
    public function __construct(){
        // Middleware to ensure user is a logged-in teacher
        $this->examModel = $this->model('Exam');
        $this->courseModel = $this->model('Course');
    }

    public function index($course_id){
        $course = $this->courseModel->getCourseById($course_id);
        // Authorization check: ensure the current user owns this course
        if($course->teacher_id != $_SESSION['user_id']){
            header('location: ' . URLROOT . '/teacher/courses');
            return;
        }

        $exams = $this->examModel->getExamsByCourse($course_id);

        $data = [
            'course' => $course,
            'exams' => $exams
        ];

        $this->view('pages/teacher/exams/index', $data);
    }

    public function create($course_id){
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

            $data = [
                'course_id' => $course_id,
                'title' => trim($_POST['title']),
                'description' => trim($_POST['description']),
                'duration' => trim($_POST['duration']),
                'title_err' => '',
            ];

            if(empty($data['title'])){
                $data['title_err'] = 'Please enter a title';
            }

            if(empty($data['title_err'])){
                if($this->examModel->createExam($data)){
                    header('location: ' . URLROOT . '/teacher/exams/index/' . $course_id);
                } else {
                    die('Something went wrong');
                }
            } else {
                $this->view('pages/teacher/exams/create', $data);
            }
        } else {
            $course = $this->courseModel->getCourseById($course_id);
             if($course->teacher_id != $_SESSION['user_id']){
                header('location: ' . URLROOT . '/teacher/courses');
                return;
            }
            $data = [
                'course_id' => $course_id,
                'title' => '',
                'description' => '',
                'duration' => 60,
            ];
            $this->view('pages/teacher/exams/create', $data);
        }
    }

    public function show($exam_id){
        $exam = $this->examModel->getExamById($exam_id);
        $course = $this->courseModel->getCourseById($exam->course_id);
        if($course->teacher_id != $_SESSION['user_id']){
            header('location: ' . URLROOT . '/teacher/courses');
            return;
        }

        $questions = $this->examModel->getQuestionsForExam($exam_id);

        $data = [
            'exam' => $exam,
            'questions' => $questions
        ];

        $this->view('pages/teacher/exams/show', $data);
    }
}
?>
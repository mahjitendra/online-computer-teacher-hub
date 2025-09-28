<?php
class TeacherQuestionController extends Controller {
    public function __construct(){
        // Middleware to ensure user is a logged-in teacher
        $this->examModel = $this->model('Exam');
        $this->courseModel = $this->model('Course');
    }

    public function add($exam_id){
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            // Authorization check: Ensure the teacher owns the exam's course
            $exam = $this->examModel->getExamById($exam_id);
            $course = $this->courseModel->getCourseById($exam->course_id);
            if($course->teacher_id != $_SESSION['user_id']){
                header('location: ' . URLROOT . '/teacher/courses');
                return;
            }

            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);

            $options = [];
            if($_POST['question_type'] == 'multiple_choice'){
                foreach($_POST['options'] as $key => $option_text){
                    if(!empty($option_text)){
                        $options[] = [
                            'text' => $option_text,
                            'is_correct' => (isset($_POST['correct_option']) && $_POST['correct_option'] == $key) ? 1 : 0
                        ];
                    }
                }
            }

            $data = [
                'exam_id' => $exam_id,
                'question_text' => trim($_POST['question_text']),
                'question_type' => trim($_POST['question_type']),
                'options' => $options,
                'question_text_err' => ''
            ];

            if(empty($data['question_text'])){
                $data['question_text_err'] = 'Please enter the question text';
            }

            // Add more validation as needed...

            if(empty($data['question_text_err'])){
                if($this->examModel->addQuestionToExam($data)){
                    header('location: ' . URLROOT . '/teacher/exams/show/' . $exam_id);
                } else {
                    die('Something went wrong adding the question.');
                }
            } else {
                // Handle errors - for now, just redirect
                header('location: ' . URLROOT . '/teacher/exams/show/' . $exam_id);
            }
        } else {
            header('location: ' . URLROOT . '/teacher/courses');
        }
    }
}
?>
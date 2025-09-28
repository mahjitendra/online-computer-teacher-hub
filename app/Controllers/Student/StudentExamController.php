<?php
class StudentExamController extends Controller {
    public function __construct(){
        // Middleware to ensure user is logged in
        $this->examModel = $this->model('Exam');
        // We will need a model for attempts and results later
    }

    // Displays the exam and its questions to the student
    public function take($exam_id){
        // First, check if student is enrolled in the course associated with this exam
        // (This logic would be more robust in a full implementation)

        $exam = $this->examModel->getExamById($exam_id);
        $questions = $this->examModel->getQuestionsForExam($exam_id);

        // We would also create an 'exam_attempt' record here in a real scenario

        $data = [
            'exam' => $exam,
            'questions' => $questions
        ];

        $this->view('pages/student/exam_attempt', $data);
    }

    // Processes the submitted exam answers
    public function submit($exam_id){
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            $student_id = $_SESSION['user_id'];
            $submitted_answers = $_POST['answers'];

            // Get correct answers from the database
            $questions = $this->examModel->getQuestionsForExam($exam_id);

            $score = 0;
            $total_questions = count($questions);

            foreach($questions as $question){
                if(isset($submitted_answers[$question->id])){
                    $student_answer = $submitted_answers[$question->id];

                    if($question->question_type == 'multiple_choice'){
                        foreach($question->options as $option){
                            if($option->is_correct && $option->id == $student_answer){
                                $score++;
                            }
                        }
                    }
                    // Add grading logic for other question types here...
                }
            }

            $percentage = ($score / $total_questions) * 100;

            // Save the result to the database (exam_attempts, exam_results)
            // For now, just display the result

            $exam = $this->examModel->getExamById($exam_id);

            $data = [
                'exam' => $exam,
                'score' => $score,
                'total_questions' => $total_questions,
                'percentage' => $percentage
            ];

            $this->view('pages/student/exam_result', $data);

        } else {
            header('location: ' . URLROOT . '/');
        }
    }
}
?>
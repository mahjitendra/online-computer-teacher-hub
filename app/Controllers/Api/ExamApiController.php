<?php
class ExamApiController extends ApiController {
    public function __construct(){
        $this->examModel = $this->model('Exam');
        $this->examService = new ExamService();
    }

    // GET /api/v1/exams
    public function index(){
        $exams = $this->examModel->getAllExams();
        $this->jsonResponse($exams);
    }

    // GET /api/v1/exams/{id}
    public function show($id){
        $exam = $this->examModel->getExamById($id);
        if($exam){
            $questions = $this->examModel->getQuestionsForExam($id);
            $exam->questions = $questions;
            $this->jsonResponse($exam);
        } else {
            $this->jsonResponse(['error' => 'Exam not found'], 404);
        }
    }

    // POST /api/v1/exams/{id}/attempt
    public function attempt($id){
        if($_SERVER['REQUEST_METHOD'] !== 'POST'){
            $this->jsonResponse(['error' => 'Method not allowed'], 405);
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $studentId = $input['student_id'] ?? null;

        if(!$studentId){
            $this->jsonResponse(['error' => 'Student ID required'], 400);
        }

        $attempt = $this->examService->startExamAttempt($id, $studentId);
        if($attempt){
            $this->jsonResponse(['success' => true, 'attempt_id' => $attempt]);
        } else {
            $this->jsonResponse(['error' => 'Failed to start exam'], 500);
        }
    }

    // POST /api/v1/exams/{id}/submit
    public function submit($id){
        if($_SERVER['REQUEST_METHOD'] !== 'POST'){
            $this->jsonResponse(['error' => 'Method not allowed'], 405);
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $studentId = $input['student_id'] ?? null;
        $answers = $input['answers'] ?? [];

        if(!$studentId){
            $this->jsonResponse(['error' => 'Student ID required'], 400);
        }

        $result = $this->examService->submitExam($id, $studentId, $answers);
        if($result){
            $this->jsonResponse(['success' => true, 'result' => $result]);
        } else {
            $this->jsonResponse(['error' => 'Failed to submit exam'], 500);
        }
    }
}
?>
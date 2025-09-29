<?php
class StudentProgressController extends Controller {
    public function __construct(){
        if(!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'student'){
            header('location: ' . URLROOT . '/auth/login');
            exit();
        }
        $this->studentModel = $this->model('Student');
        $this->progressService = new ProgressTrackingService();
    }

    public function index(){
        $studentId = $_SESSION['user_id'];
        
        $overallProgress = $this->progressService->getOverallProgress($studentId);
        $courseProgress = $this->progressService->getCourseProgress($studentId);
        $examResults = $this->progressService->getExamResults($studentId);
        $certificates = $this->progressService->getCertificates($studentId);

        $data = [
            'title' => 'My Progress',
            'overallProgress' => $overallProgress,
            'courseProgress' => $courseProgress,
            'examResults' => $examResults,
            'certificates' => $certificates
        ];

        $this->view('pages/student/progress', $data);
    }
}
?>
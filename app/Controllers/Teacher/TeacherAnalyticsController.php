<?php
class TeacherAnalyticsController extends Controller {
    public function __construct(){
        if(!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'teacher'){
            header('location: ' . URLROOT . '/auth/login');
            exit();
        }
        $this->analyticsService = new AnalyticsService();
        $this->teacherModel = $this->model('Teacher');
    }

    public function index(){
        $teacherId = $_SESSION['user_id'];
        
        $courseAnalytics = $this->analyticsService->getCourseAnalytics($teacherId);
        $studentAnalytics = $this->analyticsService->getStudentAnalytics($teacherId);
        $earningsAnalytics = $this->analyticsService->getEarningsAnalytics($teacherId);
        $engagementData = $this->analyticsService->getEngagementData($teacherId);

        $data = [
            'title' => 'Analytics Dashboard',
            'courseAnalytics' => $courseAnalytics,
            'studentAnalytics' => $studentAnalytics,
            'earningsAnalytics' => $earningsAnalytics,
            'engagementData' => $engagementData
        ];

        $this->view('pages/teacher/analytics', $data);
    }
}
?>
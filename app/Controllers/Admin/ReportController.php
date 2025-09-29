<?php
class ReportController extends Controller {
    public function __construct(){
        if(!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin'){
            header('location: ' . URLROOT . '/auth/login');
            exit();
        }
        $this->reportService = new ReportService();
    }

    public function index(){
        $data = [
            'title' => 'Reports Dashboard'
        ];

        $this->view('pages/admin/reports', $data);
    }

    public function users(){
        $userReport = $this->reportService->generateUserReport();
        
        $data = [
            'title' => 'User Report',
            'report' => $userReport
        ];

        $this->view('pages/admin/user-report', $data);
    }

    public function courses(){
        $courseReport = $this->reportService->generateCourseReport();
        
        $data = [
            'title' => 'Course Report',
            'report' => $courseReport
        ];

        $this->view('pages/admin/course-report', $data);
    }

    public function revenue(){
        $revenueReport = $this->reportService->generateRevenueReport();
        
        $data = [
            'title' => 'Revenue Report',
            'report' => $revenueReport
        ];

        $this->view('pages/admin/revenue-report', $data);
    }

    public function export($type){
        switch($type){
            case 'users':
                $this->reportService->exportUserReport();
                break;
            case 'courses':
                $this->reportService->exportCourseReport();
                break;
            case 'revenue':
                $this->reportService->exportRevenueReport();
                break;
            default:
                header('location: ' . URLROOT . '/admin/reports');
        }
    }
}
?>
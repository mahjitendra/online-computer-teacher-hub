<?php
class AdminDashboardController extends Controller {
    public function __construct(){
        if(!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin'){
            header('location: ' . URLROOT . '/auth/login');
            exit();
        }
        $this->adminModel = $this->model('Admin');
        $this->analyticsService = new AnalyticsService();
    }

    public function index(){
        $systemStats = $this->adminModel->getSystemStats();
        $recentActivity = $this->adminModel->getRecentActivity();
        $userGrowth = $this->adminModel->getUserGrowthData();
        $revenueData = $this->adminModel->getRevenueData();
        $pendingApprovals = $this->adminModel->getPendingApprovals();

        $data = [
            'title' => 'Admin Dashboard',
            'systemStats' => $systemStats,
            'recentActivity' => $recentActivity,
            'userGrowth' => $userGrowth,
            'revenueData' => $revenueData,
            'pendingApprovals' => $pendingApprovals
        ];

        $this->view('pages/admin/dashboard', $data);
    }
}
?>
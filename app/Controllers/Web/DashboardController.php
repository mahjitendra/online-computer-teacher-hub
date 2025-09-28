<?php
class DashboardController extends Controller {
    public function __construct(){
        // Here we can add a check to ensure user is logged in
        // For now, we assume the user is logged in if they reach this page
    }

    public function index(){
        // A simple dashboard page
        $data = [
            'title' => 'User Dashboard',
            'user_name' => $_SESSION['user_name'] ?? 'Guest'
        ];

        // Based on user type, load the correct dashboard view
        // For now, we'll just have a generic dashboard
        $this->view('pages/student/dashboard', $data);
    }
}
?>
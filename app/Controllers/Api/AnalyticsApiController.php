<?php
class AnalyticsApiController extends ApiController {
    public function __construct(){
        $this->analyticsService = new AnalyticsService();
    }

    // GET /api/v1/analytics/dashboard
    public function dashboard(){
        $userId = $_GET['user_id'] ?? null;
        $userType = $_GET['user_type'] ?? null;

        if(!$userId || !$userType){
            $this->jsonResponse(['error' => 'User ID and type required'], 400);
        }

        $data = $this->analyticsService->getDashboardData($userId, $userType);
        $this->jsonResponse($data);
    }

    // GET /api/v1/analytics/courses
    public function courses(){
        $teacherId = $_GET['teacher_id'] ?? null;
        if(!$teacherId){
            $this->jsonResponse(['error' => 'Teacher ID required'], 400);
        }

        $data = $this->analyticsService->getCourseAnalytics($teacherId);
        $this->jsonResponse($data);
    }

    // GET /api/v1/analytics/users
    public function users(){
        // Admin only endpoint
        $data = $this->analyticsService->getUserAnalytics();
        $this->jsonResponse($data);
    }
}
?>
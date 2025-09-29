<?php
class NotificationApiController extends ApiController {
    public function __construct(){
        $this->notificationModel = $this->model('Notification');
    }

    // GET /api/v1/notifications
    public function index(){
        $userId = $_GET['user_id'] ?? null;
        if(!$userId){
            $this->jsonResponse(['error' => 'User ID required'], 400);
        }

        $notifications = $this->notificationModel->getNotificationsByUser($userId);
        $this->jsonResponse($notifications);
    }

    // POST /api/v1/notifications/{id}/read
    public function markAsRead($id){
        if($_SERVER['REQUEST_METHOD'] !== 'POST'){
            $this->jsonResponse(['error' => 'Method not allowed'], 405);
        }

        if($this->notificationModel->markAsRead($id)){
            $this->jsonResponse(['success' => true, 'message' => 'Notification marked as read']);
        } else {
            $this->jsonResponse(['error' => 'Failed to mark as read'], 500);
        }
    }
}
?>
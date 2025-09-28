<?php
class TeacherMiddleware {
    public function handle(){
        if(!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'teacher'){
            if($this->isApiRequest()){
                http_response_code(403);
                echo json_encode(['error' => 'Teacher access required']);
                exit();
            } else {
                header('location: ' . URLROOT . '/auth/login');
                exit();
            }
        }
    }

    private function isApiRequest(){
        return strpos($_SERVER['REQUEST_URI'], '/api/') !== false;
    }
}
?>
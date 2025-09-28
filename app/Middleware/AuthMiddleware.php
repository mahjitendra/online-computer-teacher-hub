<?php
class AuthMiddleware {
    public function handle(){
        if(!isset($_SESSION['user_id'])){
            if($this->isApiRequest()){
                http_response_code(401);
                echo json_encode(['error' => 'Unauthorized']);
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
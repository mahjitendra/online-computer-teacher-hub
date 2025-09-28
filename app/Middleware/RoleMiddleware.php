<?php
class RoleMiddleware {
    private $requiredRole;

    public function __construct($role = null){
        $this->requiredRole = $role;
    }

    public function handle(){
        if(!isset($_SESSION['user_id'])){
            $this->unauthorized();
            return;
        }

        if($this->requiredRole && $_SESSION['user_type'] !== $this->requiredRole){
            $this->forbidden();
            return;
        }
    }

    public function requireRole($role){
        $this->requiredRole = $role;
        return $this;
    }

    private function unauthorized(){
        if($this->isApiRequest()){
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit();
        } else {
            header('location: ' . URLROOT . '/auth/login');
            exit();
        }
    }

    private function forbidden(){
        if($this->isApiRequest()){
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden']);
            exit();
        } else {
            header('location: ' . URLROOT . '/');
            exit();
        }
    }

    private function isApiRequest(){
        return strpos($_SERVER['REQUEST_URI'], '/api/') !== false;
    }
}
?>
<?php
class AuthApiController extends ApiController {
    public function __construct(){
        $this->userModel = $this->model('User');
        $this->authService = new AuthService();
    }

    // POST /api/v1/auth/login
    public function login(){
        if($_SERVER['REQUEST_METHOD'] !== 'POST'){
            $this->jsonResponse(['error' => 'Method not allowed'], 405);
        }

        $input = json_decode(file_get_contents('php://input'), true);
        
        $data = [
            'email' => $input['email'] ?? '',
            'password' => $input['password'] ?? ''
        ];

        $errors = [];
        if(empty($data['email'])){
            $errors['email'] = 'Email is required';
        }
        if(empty($data['password'])){
            $errors['password'] = 'Password is required';
        }

        if(!empty($errors)){
            $this->jsonResponse(['errors' => $errors], 400);
        }

        $user = $this->userModel->login($data['email'], $data['password']);
        
        if($user){
            $token = $this->authService->generateToken($user);
            $this->jsonResponse([
                'success' => true,
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'user_type' => $user->user_type
                ]
            ]);
        } else {
            $this->jsonResponse(['error' => 'Invalid credentials'], 401);
        }
    }

    // POST /api/v1/auth/register
    public function register(){
        if($_SERVER['REQUEST_METHOD'] !== 'POST'){
            $this->jsonResponse(['error' => 'Method not allowed'], 405);
        }

        $input = json_decode(file_get_contents('php://input'), true);
        
        $data = [
            'name' => $input['name'] ?? '',
            'email' => $input['email'] ?? '',
            'password' => $input['password'] ?? '',
            'user_type' => $input['user_type'] ?? 'student'
        ];

        $errors = [];
        if(empty($data['name'])){
            $errors['name'] = 'Name is required';
        }
        if(empty($data['email'])){
            $errors['email'] = 'Email is required';
        } elseif($this->userModel->findUserByEmail($data['email'])){
            $errors['email'] = 'Email already exists';
        }
        if(empty($data['password'])){
            $errors['password'] = 'Password is required';
        } elseif(strlen($data['password']) < 6){
            $errors['password'] = 'Password must be at least 6 characters';
        }

        if(!empty($errors)){
            $this->jsonResponse(['errors' => $errors], 400);
        }

        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        
        if($this->userModel->register($data)){
            $this->jsonResponse(['success' => true, 'message' => 'User registered successfully']);
        } else {
            $this->jsonResponse(['error' => 'Registration failed'], 500);
        }
    }

    // POST /api/v1/auth/logout
    public function logout(){
        // In a real implementation, you would invalidate the token
        $this->jsonResponse(['success' => true, 'message' => 'Logged out successfully']);
    }

    // POST /api/v1/auth/refresh
    public function refresh(){
        $token = $this->getAuthToken();
        if(!$token){
            $this->jsonResponse(['error' => 'Token required'], 401);
        }

        $user = $this->authService->validateToken($token);
        if(!$user){
            $this->jsonResponse(['error' => 'Invalid token'], 401);
        }

        $newToken = $this->authService->generateToken($user);
        $this->jsonResponse(['token' => $newToken]);
    }

    private function getAuthToken(){
        $headers = getallheaders();
        if(isset($headers['Authorization'])){
            return str_replace('Bearer ', '', $headers['Authorization']);
        }
        return null;
    }
}
?>
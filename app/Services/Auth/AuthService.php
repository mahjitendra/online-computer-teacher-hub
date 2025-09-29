<?php
class AuthService extends BaseService {
    private $userModel;
    private $sessionService;

    public function __construct(){
        parent::__construct();
        $this->userModel = new User();
        $this->sessionService = new SessionService();
    }

    public function authenticate($email, $password){
        try {
            $user = $this->userModel->login($email, $password);
            
            if($user){
                $this->sessionService->createSession($user);
                $this->logActivity('user_login', ['user_id' => $user->id]);
                return $user;
            }
            
            $this->logActivity('failed_login_attempt', ['email' => $email]);
            return false;
            
        } catch(Exception $e){
            return $this->handleException($e, ['email' => $email]);
        }
    }

    public function register($data){
        try {
            $errors = $this->validateRegistrationData($data);
            if(!empty($errors)){
                return ['success' => false, 'errors' => $errors];
            }

            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            
            if($this->userModel->register($data)){
                $this->logActivity('user_registration', ['email' => $data['email']]);
                return ['success' => true, 'message' => 'Registration successful'];
            }
            
            return ['success' => false, 'message' => 'Registration failed'];
            
        } catch(Exception $e){
            $this->handleException($e, $data);
            return ['success' => false, 'message' => 'Registration failed'];
        }
    }

    public function logout($userId = null){
        try {
            $userId = $userId ?? $_SESSION['user_id'] ?? null;
            
            if($userId){
                $this->logActivity('user_logout', ['user_id' => $userId]);
            }
            
            $this->sessionService->destroySession();
            return true;
            
        } catch(Exception $e){
            return $this->handleException($e);
        }
    }

    public function generateToken($user){
        $payload = [
            'user_id' => $user->id,
            'email' => $user->email,
            'user_type' => $user->user_type,
            'issued_at' => time(),
            'expires_at' => time() + (24 * 60 * 60) // 24 hours
        ];
        
        return base64_encode(json_encode($payload));
    }

    public function validateToken($token){
        try {
            $payload = json_decode(base64_decode($token), true);
            
            if(!$payload || $payload['expires_at'] < time()){
                return false;
            }
            
            return $this->userModel->getUserById($payload['user_id']);
            
        } catch(Exception $e){
            return false;
        }
    }

    public function resetPassword($email){
        try {
            if(!$this->userModel->findUserByEmail($email)){
                return ['success' => false, 'message' => 'Email not found'];
            }
            
            $resetToken = $this->generateResetToken($email);
            
            // Send reset email (would integrate with EmailService)
            $this->logActivity('password_reset_requested', ['email' => $email]);
            
            return ['success' => true, 'message' => 'Reset link sent to email'];
            
        } catch(Exception $e){
            $this->handleException($e, ['email' => $email]);
            return ['success' => false, 'message' => 'Reset failed'];
        }
    }

    public function changePassword($userId, $currentPassword, $newPassword){
        try {
            $user = $this->userModel->getUserById($userId);
            
            if(!$user || !password_verify($currentPassword, $user->password)){
                return ['success' => false, 'message' => 'Current password is incorrect'];
            }
            
            $errors = $this->validatePassword($newPassword);
            if(!empty($errors)){
                return ['success' => false, 'errors' => $errors];
            }
            
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            
            if($this->userModel->updatePassword($userId, $hashedPassword)){
                $this->logActivity('password_changed', ['user_id' => $userId]);
                return ['success' => true, 'message' => 'Password changed successfully'];
            }
            
            return ['success' => false, 'message' => 'Password change failed'];
            
        } catch(Exception $e){
            $this->handleException($e, ['user_id' => $userId]);
            return ['success' => false, 'message' => 'Password change failed'];
        }
    }

    private function validateRegistrationData($data){
        $errors = [];
        
        // Name validation
        if(empty($data['name'])){
            $errors['name'] = 'Name is required';
        } elseif(strlen($data['name']) < 2){
            $errors['name'] = 'Name must be at least 2 characters';
        }
        
        // Email validation
        if(empty($data['email'])){
            $errors['email'] = 'Email is required';
        } elseif(!filter_var($data['email'], FILTER_VALIDATE_EMAIL)){
            $errors['email'] = 'Invalid email format';
        } elseif($this->userModel->findUserByEmail($data['email'])){
            $errors['email'] = 'Email already exists';
        }
        
        // Password validation
        $passwordErrors = $this->validatePassword($data['password'] ?? '');
        if(!empty($passwordErrors)){
            $errors['password'] = $passwordErrors[0];
        }
        
        // Confirm password
        if(empty($data['confirm_password'])){
            $errors['confirm_password'] = 'Please confirm password';
        } elseif($data['password'] !== $data['confirm_password']){
            $errors['confirm_password'] = 'Passwords do not match';
        }
        
        return $errors;
    }

    private function validatePassword($password){
        $errors = [];
        
        if(empty($password)){
            $errors[] = 'Password is required';
        } elseif(strlen($password) < 8){
            $errors[] = 'Password must be at least 8 characters';
        } elseif(!preg_match('/[A-Z]/', $password)){
            $errors[] = 'Password must contain at least one uppercase letter';
        } elseif(!preg_match('/[a-z]/', $password)){
            $errors[] = 'Password must contain at least one lowercase letter';
        } elseif(!preg_match('/[0-9]/', $password)){
            $errors[] = 'Password must contain at least one number';
        }
        
        return $errors;
    }

    private function generateResetToken($email){
        $token = bin2hex(random_bytes(32));
        $expires = time() + (60 * 60); // 1 hour
        
        // Store token in cache or database
        $this->cache->set("reset_token:{$token}", [
            'email' => $email,
            'expires' => $expires
        ], 3600);
        
        return $token;
    }

    public function validateResetToken($token){
        $data = $this->cache->get("reset_token:{$token}");
        
        if(!$data || $data['expires'] < time()){
            return false;
        }
        
        return $data['email'];
    }

    public function completePasswordReset($token, $newPassword){
        try {
            $email = $this->validateResetToken($token);
            
            if(!$email){
                return ['success' => false, 'message' => 'Invalid or expired token'];
            }
            
            $errors = $this->validatePassword($newPassword);
            if(!empty($errors)){
                return ['success' => false, 'errors' => $errors];
            }
            
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            
            if($this->userModel->updatePasswordByEmail($email, $hashedPassword)){
                $this->cache->delete("reset_token:{$token}");
                $this->logActivity('password_reset_completed', ['email' => $email]);
                return ['success' => true, 'message' => 'Password reset successfully'];
            }
            
            return ['success' => false, 'message' => 'Password reset failed'];
            
        } catch(Exception $e){
            $this->handleException($e, ['token' => $token]);
            return ['success' => false, 'message' => 'Password reset failed'];
        }
    }
}
?>
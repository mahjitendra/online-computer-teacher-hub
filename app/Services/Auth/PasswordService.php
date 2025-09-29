<?php
class PasswordService extends BaseService {
    private $userModel;
    private $emailService;

    public function __construct(){
        parent::__construct();
        $this->userModel = new User();
        $this->emailService = new EmailService();
    }

    public function validatePasswordStrength($password){
        $errors = [];
        $score = 0;

        // Length check
        if(strlen($password) < 8){
            $errors[] = 'Password must be at least 8 characters long';
        } else {
            $score += 1;
        }

        // Uppercase check
        if(!preg_match('/[A-Z]/', $password)){
            $errors[] = 'Password must contain at least one uppercase letter';
        } else {
            $score += 1;
        }

        // Lowercase check
        if(!preg_match('/[a-z]/', $password)){
            $errors[] = 'Password must contain at least one lowercase letter';
        } else {
            $score += 1;
        }

        // Number check
        if(!preg_match('/[0-9]/', $password)){
            $errors[] = 'Password must contain at least one number';
        } else {
            $score += 1;
        }

        // Special character check
        if(!preg_match('/[^A-Za-z0-9]/', $password)){
            $errors[] = 'Password must contain at least one special character';
        } else {
            $score += 1;
        }

        // Common password check
        if($this->isCommonPassword($password)){
            $errors[] = 'Password is too common, please choose a stronger password';
            $score -= 2;
        }

        $strength = $this->calculateStrength($score);

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'score' => max(0, $score),
            'strength' => $strength
        ];
    }

    public function hashPassword($password){
        return password_hash($password, PASSWORD_DEFAULT, [
            'cost' => 12
        ]);
    }

    public function verifyPassword($password, $hash){
        return password_verify($password, $hash);
    }

    public function needsRehash($hash){
        return password_needs_rehash($hash, PASSWORD_DEFAULT, [
            'cost' => 12
        ]);
    }

    public function generateSecurePassword($length = 12){
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()';
        $password = '';
        
        for($i = 0; $i < $length; $i++){
            $password .= $characters[random_int(0, strlen($characters) - 1)];
        }
        
        return $password;
    }

    public function initiatePasswordReset($email){
        try {
            if(!$this->userModel->findUserByEmail($email)){
                // Don't reveal if email exists or not
                return ['success' => true, 'message' => 'If the email exists, a reset link has been sent'];
            }

            $token = $this->generateResetToken();
            $expires = time() + (60 * 60); // 1 hour

            // Store reset token
            $this->cache->set("password_reset:{$token}", [
                'email' => $email,
                'expires' => $expires,
                'attempts' => 0
            ], 3600);

            // Send reset email
            $this->emailService->sendPasswordResetEmail($email, $token);

            $this->logActivity('password_reset_initiated', ['email' => $email]);

            return ['success' => true, 'message' => 'If the email exists, a reset link has been sent'];

        } catch(Exception $e){
            return $this->handleException($e, ['email' => $email]);
        }
    }

    public function validateResetToken($token){
        $data = $this->cache->get("password_reset:{$token}");

        if(!$data){
            return ['valid' => false, 'message' => 'Invalid reset token'];
        }

        if($data['expires'] < time()){
            $this->cache->delete("password_reset:{$token}");
            return ['valid' => false, 'message' => 'Reset token has expired'];
        }

        if($data['attempts'] >= 3){
            $this->cache->delete("password_reset:{$token}");
            return ['valid' => false, 'message' => 'Too many reset attempts'];
        }

        return ['valid' => true, 'email' => $data['email']];
    }

    public function resetPassword($token, $newPassword){
        try {
            $validation = $this->validateResetToken($token);
            
            if(!$validation['valid']){
                return ['success' => false, 'message' => $validation['message']];
            }

            $email = $validation['email'];

            // Validate new password
            $passwordValidation = $this->validatePasswordStrength($newPassword);
            if(!$passwordValidation['valid']){
                // Increment attempts
                $data = $this->cache->get("password_reset:{$token}");
                $data['attempts']++;
                $this->cache->set("password_reset:{$token}", $data, 3600);

                return ['success' => false, 'errors' => $passwordValidation['errors']];
            }

            // Update password
            $hashedPassword = $this->hashPassword($newPassword);
            
            if($this->userModel->updatePasswordByEmail($email, $hashedPassword)){
                // Clear reset token
                $this->cache->delete("password_reset:{$token}");
                
                // Invalidate all user sessions
                $this->invalidateUserSessions($email);
                
                $this->logActivity('password_reset_completed', ['email' => $email]);
                
                return ['success' => true, 'message' => 'Password reset successfully'];
            }

            return ['success' => false, 'message' => 'Failed to update password'];

        } catch(Exception $e){
            return $this->handleException($e, ['token' => $token]);
        }
    }

    public function changePassword($userId, $currentPassword, $newPassword){
        try {
            $user = $this->userModel->getUserById($userId);
            
            if(!$user){
                return ['success' => false, 'message' => 'User not found'];
            }

            // Verify current password
            if(!$this->verifyPassword($currentPassword, $user->password)){
                return ['success' => false, 'message' => 'Current password is incorrect'];
            }

            // Validate new password
            $passwordValidation = $this->validatePasswordStrength($newPassword);
            if(!$passwordValidation['valid']){
                return ['success' => false, 'errors' => $passwordValidation['errors']];
            }

            // Check if new password is different from current
            if($this->verifyPassword($newPassword, $user->password)){
                return ['success' => false, 'message' => 'New password must be different from current password'];
            }

            // Update password
            $hashedPassword = $this->hashPassword($newPassword);
            
            if($this->userModel->updatePassword($userId, $hashedPassword)){
                $this->logActivity('password_changed', ['user_id' => $userId]);
                return ['success' => true, 'message' => 'Password changed successfully'];
            }

            return ['success' => false, 'message' => 'Failed to update password'];

        } catch(Exception $e){
            return $this->handleException($e, ['user_id' => $userId]);
        }
    }

    private function generateResetToken(){
        return bin2hex(random_bytes(32));
    }

    private function calculateStrength($score){
        if($score <= 1) return 'Very Weak';
        if($score <= 2) return 'Weak';
        if($score <= 3) return 'Fair';
        if($score <= 4) return 'Good';
        return 'Strong';
    }

    private function isCommonPassword($password){
        $commonPasswords = [
            'password', '123456', '123456789', 'qwerty', 'abc123',
            'password123', 'admin', 'letmein', 'welcome', 'monkey',
            'dragon', 'master', 'shadow', 'superman', 'michael'
        ];

        return in_array(strtolower($password), $commonPasswords);
    }

    private function invalidateUserSessions($email){
        // This would invalidate all active sessions for the user
        // Implementation depends on session storage mechanism
        $this->cache->delete("user_sessions:{$email}");
    }

    public function getPasswordHistory($userId, $limit = 5){
        try {
            $this->db->query('SELECT password_hash, created_at FROM password_history 
                             WHERE user_id = :user_id 
                             ORDER BY created_at DESC 
                             LIMIT :limit');
            $this->db->bind(':user_id', $userId);
            $this->db->bind(':limit', $limit);
            
            return $this->db->resultSet();

        } catch(Exception $e){
            return $this->handleException($e, ['user_id' => $userId]);
        }
    }

    public function addToPasswordHistory($userId, $passwordHash){
        try {
            $this->db->query('INSERT INTO password_history (user_id, password_hash) VALUES (:user_id, :password_hash)');
            $this->db->bind(':user_id', $userId);
            $this->db->bind(':password_hash', $passwordHash);
            
            return $this->db->execute();

        } catch(Exception $e){
            return $this->handleException($e, ['user_id' => $userId]);
        }
    }

    public function checkPasswordReuse($userId, $newPassword, $historyLimit = 5){
        $history = $this->getPasswordHistory($userId, $historyLimit);
        
        foreach($history as $entry){
            if($this->verifyPassword($newPassword, $entry->password_hash)){
                return true; // Password was used before
            }
        }
        
        return false; // Password is new
    }
}
?>
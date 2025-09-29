<?php
class RegistrationService extends BaseService {
    private $userModel;
    private $profileModel;
    private $emailService;

    public function __construct(){
        parent::__construct();
        $this->userModel = new User();
        $this->profileModel = new UserProfile();
        $this->emailService = new EmailService();
    }

    public function registerUser($data){
        try {
            // Validate registration data
            $errors = $this->validateRegistrationData($data);
            if(!empty($errors)){
                return ['success' => false, 'errors' => $errors];
            }

            // Start transaction
            $this->db->beginTransaction();

            // Create user account
            $userData = [
                'name' => $this->sanitizeInput($data['name']),
                'email' => strtolower(trim($data['email'])),
                'password' => password_hash($data['password'], PASSWORD_DEFAULT),
                'user_type' => $data['user_type'] ?? 'student'
            ];

            $userId = $this->userModel->register($userData);
            
            if(!$userId){
                $this->db->rollback();
                return ['success' => false, 'message' => 'Failed to create user account'];
            }

            // Create user profile
            $profileData = [
                'user_id' => $userId,
                'first_name' => $data['first_name'] ?? '',
                'last_name' => $data['last_name'] ?? '',
                'phone' => $data['phone'] ?? '',
                'bio' => $data['bio'] ?? ''
            ];

            if(!$this->profileModel->createProfile($profileData)){
                $this->db->rollback();
                return ['success' => false, 'message' => 'Failed to create user profile'];
            }

            // Create role-specific profile
            $this->createRoleSpecificProfile($userId, $userData['user_type'], $data);

            // Send welcome email
            $this->sendWelcomeEmail($userData['email'], $userData['name']);

            // Commit transaction
            $this->db->commit();

            $this->logActivity('user_registered', [
                'user_id' => $userId,
                'email' => $userData['email'],
                'user_type' => $userData['user_type']
            ]);

            return [
                'success' => true,
                'message' => 'Registration successful',
                'user_id' => $userId
            ];

        } catch(Exception $e){
            $this->db->rollback();
            return $this->handleException($e, $data);
        }
    }

    public function verifyEmail($token){
        try {
            $data = $this->cache->get("email_verification:{$token}");
            
            if(!$data){
                return ['success' => false, 'message' => 'Invalid or expired verification token'];
            }

            $userId = $data['user_id'];
            
            if($this->userModel->verifyEmail($userId)){
                $this->cache->delete("email_verification:{$token}");
                $this->logActivity('email_verified', ['user_id' => $userId]);
                return ['success' => true, 'message' => 'Email verified successfully'];
            }

            return ['success' => false, 'message' => 'Email verification failed'];

        } catch(Exception $e){
            return $this->handleException($e, ['token' => $token]);
        }
    }

    public function resendVerificationEmail($email){
        try {
            $user = $this->userModel->getUserByEmail($email);
            
            if(!$user){
                return ['success' => false, 'message' => 'User not found'];
            }

            if($user->email_verified){
                return ['success' => false, 'message' => 'Email already verified'];
            }

            $this->sendVerificationEmail($user->email, $user->name, $user->id);

            return ['success' => true, 'message' => 'Verification email sent'];

        } catch(Exception $e){
            return $this->handleException($e, ['email' => $email]);
        }
    }

    private function validateRegistrationData($data){
        $errors = [];

        // Required fields
        $required = ['name', 'email', 'password'];
        $requiredErrors = $this->validateRequired($data, $required);
        $errors = array_merge($errors, $requiredErrors);

        // Email validation
        if(!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)){
            $errors['email'] = 'Invalid email format';
        }

        if(!empty($data['email']) && $this->userModel->findUserByEmail($data['email'])){
            $errors['email'] = 'Email already exists';
        }

        // Password validation
        if(!empty($data['password'])){
            $passwordErrors = $this->validatePassword($data['password']);
            if(!empty($passwordErrors)){
                $errors['password'] = $passwordErrors[0];
            }
        }

        // Confirm password
        if(!empty($data['password']) && $data['password'] !== ($data['confirm_password'] ?? '')){
            $errors['confirm_password'] = 'Passwords do not match';
        }

        // User type validation
        $validUserTypes = ['student', 'teacher'];
        if(!empty($data['user_type']) && !in_array($data['user_type'], $validUserTypes)){
            $errors['user_type'] = 'Invalid user type';
        }

        return $errors;
    }

    private function validatePassword($password){
        $errors = [];

        if(strlen($password) < 8){
            $errors[] = 'Password must be at least 8 characters';
        }

        if(!preg_match('/[A-Z]/', $password)){
            $errors[] = 'Password must contain at least one uppercase letter';
        }

        if(!preg_match('/[a-z]/', $password)){
            $errors[] = 'Password must contain at least one lowercase letter';
        }

        if(!preg_match('/[0-9]/', $password)){
            $errors[] = 'Password must contain at least one number';
        }

        return $errors;
    }

    private function createRoleSpecificProfile($userId, $userType, $data){
        switch($userType){
            case 'student':
                $studentModel = new Student();
                $studentData = [
                    'user_id' => $userId,
                    'student_id' => $this->generateStudentId(),
                    'enrollment_date' => date('Y-m-d'),
                    'status' => 'active'
                ];
                $studentModel->createStudentProfile($studentData);
                break;

            case 'teacher':
                $teacherModel = new Teacher();
                $teacherData = [
                    'user_id' => $userId,
                    'teacher_id' => $this->generateTeacherId(),
                    'specialization' => $data['specialization'] ?? '',
                    'experience' => $data['experience'] ?? '',
                    'qualification' => $data['qualification'] ?? '',
                    'status' => 'pending' // Requires admin approval
                ];
                $teacherModel->createTeacherProfile($teacherData);
                break;
        }
    }

    private function generateStudentId(){
        return 'STU' . date('Y') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    }

    private function generateTeacherId(){
        return 'TCH' . date('Y') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    }

    private function sendWelcomeEmail($email, $name){
        $this->emailService->sendWelcomeEmail($email, $name);
    }

    private function sendVerificationEmail($email, $name, $userId){
        $token = bin2hex(random_bytes(32));
        
        // Store verification token
        $this->cache->set("email_verification:{$token}", [
            'user_id' => $userId,
            'email' => $email
        ], 86400); // 24 hours

        $this->emailService->sendVerificationEmail($email, $name, $token);
    }

    public function getRegistrationStats($period = 'month'){
        try {
            $dateCondition = '';
            switch($period){
                case 'week':
                    $dateCondition = 'WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 WEEK)';
                    break;
                case 'month':
                    $dateCondition = 'WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)';
                    break;
                case 'year':
                    $dateCondition = 'WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR)';
                    break;
            }

            $this->db->query("SELECT 
                             COUNT(*) as total_registrations,
                             SUM(CASE WHEN user_type = 'student' THEN 1 ELSE 0 END) as student_registrations,
                             SUM(CASE WHEN user_type = 'teacher' THEN 1 ELSE 0 END) as teacher_registrations,
                             DATE(created_at) as registration_date,
                             COUNT(*) as daily_count
                             FROM users 
                             {$dateCondition}
                             GROUP BY DATE(created_at)
                             ORDER BY registration_date DESC");

            return $this->db->resultSet();

        } catch(Exception $e){
            return $this->handleException($e);
        }
    }
}
?>
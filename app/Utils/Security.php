<?php
class Security {
    
    public static function generateToken($length = 32){
        return bin2hex(random_bytes($length));
    }

    public static function hashPassword($password){
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public static function verifyPassword($password, $hash){
        return password_verify($password, $hash);
    }

    public static function sanitizeInput($input){
        if(is_array($input)){
            return array_map([self::class, 'sanitizeInput'], $input);
        }
        
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    public static function validateEmail($email){
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function validateUrl($url){
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    public static function generateCSRFToken(){
        if(!isset($_SESSION['csrf_token'])){
            $_SESSION['csrf_token'] = self::generateToken();
        }
        return $_SESSION['csrf_token'];
    }

    public static function validateCSRFToken($token){
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    public static function encryptData($data, $key = null){
        $key = $key ?: self::getEncryptionKey();
        $iv = random_bytes(16);
        $encrypted = openssl_encrypt($data, 'AES-256-CBC', $key, 0, $iv);
        return base64_encode($iv . $encrypted);
    }

    public static function decryptData($encryptedData, $key = null){
        $key = $key ?: self::getEncryptionKey();
        $data = base64_decode($encryptedData);
        $iv = substr($data, 0, 16);
        $encrypted = substr($data, 16);
        return openssl_decrypt($encrypted, 'AES-256-CBC', $key, 0, $iv);
    }

    private static function getEncryptionKey(){
        return hash('sha256', ENCRYPTION_KEY ?? 'default-key-change-this');
    }

    public static function isValidFileType($filename, $allowedTypes = []){
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if(empty($allowedTypes)){
            $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'txt'];
        }
        
        return in_array($extension, $allowedTypes);
    }

    public static function generateSecureFilename($originalName){
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $filename = pathinfo($originalName, PATHINFO_FILENAME);
        $filename = preg_replace('/[^a-zA-Z0-9_-]/', '', $filename);
        $filename = substr($filename, 0, 50); // Limit length
        
        return $filename . '_' . time() . '_' . self::generateToken(8) . '.' . $extension;
    }

    public static function rateLimitCheck($identifier, $maxAttempts = 5, $timeWindow = 300){
        $key = 'rate_limit_' . md5($identifier);
        
        if(!isset($_SESSION[$key])){
            $_SESSION[$key] = [];
        }
        
        $now = time();
        $attempts = $_SESSION[$key];
        
        // Remove old attempts outside time window
        $attempts = array_filter($attempts, function($timestamp) use ($now, $timeWindow){
            return ($now - $timestamp) < $timeWindow;
        });
        
        if(count($attempts) >= $maxAttempts){
            return false;
        }
        
        $attempts[] = $now;
        $_SESSION[$key] = $attempts;
        
        return true;
    }

    public static function logSecurityEvent($event, $details = []){
        $logger = new Logger(dirname(__DIR__, 2) . '/storage/logs/security.log');
        $logger->warning("Security event: {$event}", array_merge($details, [
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'user_id' => $_SESSION['user_id'] ?? null
        ]));
    }

    public static function detectSQLInjection($input){
        $patterns = [
            '/(\bunion\b.*\bselect\b)/i',
            '/(\bselect\b.*\bfrom\b)/i',
            '/(\binsert\b.*\binto\b)/i',
            '/(\bdelete\b.*\bfrom\b)/i',
            '/(\bupdate\b.*\bset\b)/i',
            '/(\bdrop\b.*\btable\b)/i',
            '/(\'|\"|;|--|\#)/i'
        ];
        
        foreach($patterns as $pattern){
            if(preg_match($pattern, $input)){
                return true;
            }
        }
        
        return false;
    }

    public static function detectXSS($input){
        $patterns = [
            '/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi',
            '/<iframe\b[^<]*(?:(?!<\/iframe>)<[^<]*)*<\/iframe>/mi',
            '/javascript:/i',
            '/on\w+\s*=/i'
        ];
        
        foreach($patterns as $pattern){
            if(preg_match($pattern, $input)){
                return true;
            }
        }
        
        return false;
    }

    public static function validateInput($input, $rules = []){
        $errors = [];
        
        foreach($rules as $rule => $params){
            switch($rule){
                case 'required':
                    if(empty($input)){
                        $errors[] = 'This field is required';
                    }
                    break;
                    
                case 'min_length':
                    if(strlen($input) < $params){
                        $errors[] = "Minimum length is {$params} characters";
                    }
                    break;
                    
                case 'max_length':
                    if(strlen($input) > $params){
                        $errors[] = "Maximum length is {$params} characters";
                    }
                    break;
                    
                case 'email':
                    if(!self::validateEmail($input)){
                        $errors[] = 'Invalid email format';
                    }
                    break;
                    
                case 'url':
                    if(!self::validateUrl($input)){
                        $errors[] = 'Invalid URL format';
                    }
                    break;
                    
                case 'numeric':
                    if(!is_numeric($input)){
                        $errors[] = 'Must be a number';
                    }
                    break;
                    
                case 'alpha':
                    if(!ctype_alpha($input)){
                        $errors[] = 'Must contain only letters';
                    }
                    break;
                    
                case 'alphanumeric':
                    if(!ctype_alnum($input)){
                        $errors[] = 'Must contain only letters and numbers';
                    }
                    break;
            }
        }
        
        return $errors;
    }
}
?>
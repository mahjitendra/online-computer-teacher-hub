<?php
class SessionService extends BaseService {
    private $sessionTimeout;
    private $sessionName;

    public function __construct(){
        parent::__construct();
        $this->sessionTimeout = 7200; // 2 hours
        $this->sessionName = 'OCTH_SESSION';
    }

    public function createSession($user){
        try {
            // Regenerate session ID for security
            session_regenerate_id(true);

            // Set session data
            $_SESSION['user_id'] = $user->id;
            $_SESSION['user_email'] = $user->email;
            $_SESSION['user_name'] = $user->name;
            $_SESSION['user_type'] = $user->user_type;
            $_SESSION['login_time'] = time();
            $_SESSION['last_activity'] = time();
            $_SESSION['session_token'] = $this->generateSessionToken();

            // Store session in cache for tracking
            $this->storeSessionData($user->id, $_SESSION['session_token']);

            $this->logActivity('session_created', ['user_id' => $user->id]);

            return true;

        } catch(Exception $e){
            return $this->handleException($e, ['user_id' => $user->id]);
        }
    }

    public function validateSession(){
        try {
            if(!isset($_SESSION['user_id'])){
                return false;
            }

            // Check session timeout
            if($this->isSessionExpired()){
                $this->destroySession();
                return false;
            }

            // Update last activity
            $_SESSION['last_activity'] = time();

            // Validate session token
            if(!$this->validateSessionToken()){
                $this->destroySession();
                return false;
            }

            return true;

        } catch(Exception $e){
            $this->handleException($e);
            return false;
        }
    }

    public function refreshSession(){
        try {
            if(!isset($_SESSION['user_id'])){
                return false;
            }

            // Regenerate session ID
            session_regenerate_id(true);

            // Update session data
            $_SESSION['last_activity'] = time();
            $_SESSION['session_token'] = $this->generateSessionToken();

            // Update stored session data
            $this->storeSessionData($_SESSION['user_id'], $_SESSION['session_token']);

            return true;

        } catch(Exception $e){
            return $this->handleException($e);
        }
    }

    public function destroySession(){
        try {
            $userId = $_SESSION['user_id'] ?? null;

            if($userId){
                // Remove from session storage
                $this->removeSessionData($userId);
                $this->logActivity('session_destroyed', ['user_id' => $userId]);
            }

            // Clear session data
            $_SESSION = [];

            // Destroy session cookie
            if(ini_get("session.use_cookies")){
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000,
                    $params["path"], $params["domain"],
                    $params["secure"], $params["httponly"]
                );
            }

            // Destroy session
            session_destroy();

            return true;

        } catch(Exception $e){
            return $this->handleException($e);
        }
    }

    public function isLoggedIn(){
        return isset($_SESSION['user_id']) && $this->validateSession();
    }

    public function getCurrentUser(){
        if(!$this->isLoggedIn()){
            return null;
        }

        return [
            'id' => $_SESSION['user_id'],
            'email' => $_SESSION['user_email'],
            'name' => $_SESSION['user_name'],
            'user_type' => $_SESSION['user_type']
        ];
    }

    public function hasRole($role){
        return $this->isLoggedIn() && $_SESSION['user_type'] === $role;
    }

    public function getSessionDuration(){
        if(!isset($_SESSION['login_time'])){
            return 0;
        }

        return time() - $_SESSION['login_time'];
    }

    public function getTimeUntilExpiry(){
        if(!isset($_SESSION['last_activity'])){
            return 0;
        }

        $timeLeft = $this->sessionTimeout - (time() - $_SESSION['last_activity']);
        return max(0, $timeLeft);
    }

    public function extendSession($additionalTime = 3600){
        if($this->isLoggedIn()){
            $_SESSION['last_activity'] = time() + $additionalTime;
            return true;
        }
        return false;
    }

    private function isSessionExpired(){
        if(!isset($_SESSION['last_activity'])){
            return true;
        }

        return (time() - $_SESSION['last_activity']) > $this->sessionTimeout;
    }

    private function generateSessionToken(){
        return bin2hex(random_bytes(32));
    }

    private function validateSessionToken(){
        if(!isset($_SESSION['session_token']) || !isset($_SESSION['user_id'])){
            return false;
        }

        $storedToken = $this->getStoredSessionToken($_SESSION['user_id']);
        return $storedToken === $_SESSION['session_token'];
    }

    private function storeSessionData($userId, $sessionToken){
        $sessionData = [
            'user_id' => $userId,
            'session_token' => $sessionToken,
            'created_at' => time(),
            'last_activity' => time(),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ];

        $this->cache->set("session:{$userId}", $sessionData, $this->sessionTimeout);
    }

    private function getStoredSessionToken($userId){
        $sessionData = $this->cache->get("session:{$userId}");
        return $sessionData ? $sessionData['session_token'] : null;
    }

    private function removeSessionData($userId){
        $this->cache->delete("session:{$userId}");
    }

    public function getActiveSessions($userId = null){
        try {
            if($userId){
                $sessionData = $this->cache->get("session:{$userId}");
                return $sessionData ? [$sessionData] : [];
            }

            // This would require a different storage mechanism to list all sessions
            // For now, return empty array
            return [];

        } catch(Exception $e){
            return $this->handleException($e, ['user_id' => $userId]);
        }
    }

    public function terminateUserSessions($userId){
        try {
            $this->removeSessionData($userId);
            $this->logActivity('user_sessions_terminated', ['user_id' => $userId]);
            return true;

        } catch(Exception $e){
            return $this->handleException($e, ['user_id' => $userId]);
        }
    }

    public function getSessionStats(){
        try {
            // This would require session storage in database for accurate stats
            return [
                'active_sessions' => 0,
                'total_sessions_today' => 0,
                'average_session_duration' => 0
            ];

        } catch(Exception $e){
            return $this->handleException($e);
        }
    }

    public function configureSession(){
        // Configure session settings
        ini_set('session.cookie_httponly', 1);
        ini_set('session.use_only_cookies', 1);
        ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
        ini_set('session.cookie_samesite', 'Lax');
        
        session_name($this->sessionName);
        
        if(session_status() === PHP_SESSION_NONE){
            session_start();
        }
    }
}
?>
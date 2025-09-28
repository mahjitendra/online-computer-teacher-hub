<?php
class RateLimitMiddleware {
    private $maxRequests;
    private $timeWindow;
    private $storage;

    public function __construct($maxRequests = 100, $timeWindow = 3600){
        $this->maxRequests = $maxRequests;
        $this->timeWindow = $timeWindow;
        $this->storage = new Cache();
    }

    public function handle(){
        $clientId = $this->getClientId();
        $key = "rate_limit:{$clientId}";
        
        $requests = $this->storage->get($key, []);
        $now = time();
        
        // Remove old requests outside the time window
        $requests = array_filter($requests, function($timestamp) use ($now){
            return ($now - $timestamp) < $this->timeWindow;
        });
        
        if(count($requests) >= $this->maxRequests){
            $this->rateLimitExceeded();
            return;
        }
        
        // Add current request
        $requests[] = $now;
        $this->storage->set($key, $requests, $this->timeWindow);
        
        // Set rate limit headers
        header('X-RateLimit-Limit: ' . $this->maxRequests);
        header('X-RateLimit-Remaining: ' . ($this->maxRequests - count($requests)));
        header('X-RateLimit-Reset: ' . ($now + $this->timeWindow));
    }

    private function getClientId(){
        if(isset($_SESSION['user_id'])){
            return 'user_' . $_SESSION['user_id'];
        }
        return 'ip_' . $_SERVER['REMOTE_ADDR'];
    }

    private function rateLimitExceeded(){
        http_response_code(429);
        if($this->isApiRequest()){
            echo json_encode(['error' => 'Rate limit exceeded']);
        } else {
            echo 'Rate limit exceeded. Please try again later.';
        }
        exit();
    }

    private function isApiRequest(){
        return strpos($_SERVER['REQUEST_URI'], '/api/') !== false;
    }
}
?>
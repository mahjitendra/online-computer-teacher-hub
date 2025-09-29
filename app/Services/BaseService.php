<?php
abstract class BaseService {
    protected $db;
    protected $cache;
    protected $logger;

    public function __construct(){
        $this->db = new Database();
        $this->cache = new Cache();
        $this->logger = new Logger();
    }

    protected function validateRequired($data, $required = []){
        $errors = [];
        
        foreach($required as $field){
            if(!isset($data[$field]) || empty(trim($data[$field]))){
                $errors[$field] = ucfirst($field) . ' is required';
            }
        }
        
        return $errors;
    }

    protected function sanitizeInput($input){
        if(is_array($input)){
            return array_map([$this, 'sanitizeInput'], $input);
        }
        
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    protected function logActivity($action, $details = [], $userId = null){
        $this->logger->info("Service activity: {$action}", array_merge($details, [
            'user_id' => $userId,
            'service' => get_class($this)
        ]));
    }

    protected function handleException($e, $context = []){
        $this->logger->error($e->getMessage(), array_merge($context, [
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]));
        
        return false;
    }

    protected function cacheKey($key, $params = []){
        $cacheKey = get_class($this) . ':' . $key;
        if(!empty($params)){
            $cacheKey .= ':' . md5(serialize($params));
        }
        return $cacheKey;
    }

    protected function remember($key, $callback, $ttl = 3600){
        $cacheKey = $this->cacheKey($key);
        return $this->cache->remember($cacheKey, $callback, $ttl);
    }

    protected function forget($key){
        $cacheKey = $this->cacheKey($key);
        return $this->cache->delete($cacheKey);
    }
}
?>
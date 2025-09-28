<?php
class Cache {
    private $cacheDir;
    private $defaultTtl;

    public function __construct($cacheDir = null, $defaultTtl = 3600){
        $this->cacheDir = $cacheDir ?: dirname(__DIR__, 2) . '/storage/cache/';
        $this->defaultTtl = $defaultTtl;
        
        // Create cache directory if it doesn't exist
        if(!is_dir($this->cacheDir)){
            mkdir($this->cacheDir, 0755, true);
        }
    }

    public function get($key, $default = null){
        $filePath = $this->getFilePath($key);
        
        if(!file_exists($filePath)){
            return $default;
        }
        
        $data = unserialize(file_get_contents($filePath));
        
        // Check if cache has expired
        if($data['expires'] < time()){
            $this->delete($key);
            return $default;
        }
        
        return $data['value'];
    }

    public function set($key, $value, $ttl = null){
        $ttl = $ttl ?: $this->defaultTtl;
        $filePath = $this->getFilePath($key);
        
        $data = [
            'value' => $value,
            'expires' => time() + $ttl,
            'created' => time()
        ];
        
        return file_put_contents($filePath, serialize($data), LOCK_EX) !== false;
    }

    public function delete($key){
        $filePath = $this->getFilePath($key);
        
        if(file_exists($filePath)){
            return unlink($filePath);
        }
        
        return true;
    }

    public function clear(){
        $files = glob($this->cacheDir . '*.cache');
        
        foreach($files as $file){
            unlink($file);
        }
        
        return true;
    }

    public function has($key){
        return $this->get($key) !== null;
    }

    public function remember($key, $callback, $ttl = null){
        $value = $this->get($key);
        
        if($value === null){
            $value = $callback();
            $this->set($key, $value, $ttl);
        }
        
        return $value;
    }

    public function increment($key, $value = 1){
        $current = $this->get($key, 0);
        $new = $current + $value;
        $this->set($key, $new);
        return $new;
    }

    public function decrement($key, $value = 1){
        $current = $this->get($key, 0);
        $new = $current - $value;
        $this->set($key, $new);
        return $new;
    }

    private function getFilePath($key){
        $hash = md5($key);
        return $this->cacheDir . $hash . '.cache';
    }

    public function cleanExpired(){
        $files = glob($this->cacheDir . '*.cache');
        $cleaned = 0;
        
        foreach($files as $file){
            $data = unserialize(file_get_contents($file));
            
            if($data['expires'] < time()){
                unlink($file);
                $cleaned++;
            }
        }
        
        return $cleaned;
    }

    public function getStats(){
        $files = glob($this->cacheDir . '*.cache');
        $totalSize = 0;
        $expired = 0;
        $valid = 0;
        
        foreach($files as $file){
            $totalSize += filesize($file);
            $data = unserialize(file_get_contents($file));
            
            if($data['expires'] < time()){
                $expired++;
            } else {
                $valid++;
            }
        }
        
        return [
            'total_files' => count($files),
            'valid_files' => $valid,
            'expired_files' => $expired,
            'total_size' => $totalSize,
            'cache_dir' => $this->cacheDir
        ];
    }
}
?>
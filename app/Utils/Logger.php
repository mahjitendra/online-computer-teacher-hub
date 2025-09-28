<?php
class Logger {
    private $logPath;
    private $logLevel;
    
    const DEBUG = 1;
    const INFO = 2;
    const WARNING = 3;
    const ERROR = 4;
    const CRITICAL = 5;

    public function __construct($logPath = null, $logLevel = self::INFO){
        $this->logPath = $logPath ?: dirname(__DIR__, 2) . '/storage/logs/app.log';
        $this->logLevel = $logLevel;
        
        // Create log directory if it doesn't exist
        $logDir = dirname($this->logPath);
        if(!is_dir($logDir)){
            mkdir($logDir, 0755, true);
        }
    }

    public function debug($message, $context = []){
        $this->log(self::DEBUG, $message, $context);
    }

    public function info($message, $context = []){
        $this->log(self::INFO, $message, $context);
    }

    public function warning($message, $context = []){
        $this->log(self::WARNING, $message, $context);
    }

    public function error($message, $context = []){
        $this->log(self::ERROR, $message, $context);
    }

    public function critical($message, $context = []){
        $this->log(self::CRITICAL, $message, $context);
    }

    private function log($level, $message, $context = []){
        if($level < $this->logLevel){
            return;
        }

        $levelName = $this->getLevelName($level);
        $timestamp = date('Y-m-d H:i:s');
        $contextString = !empty($context) ? ' ' . json_encode($context) : '';
        
        $logEntry = "[{$timestamp}] {$levelName}: {$message}{$contextString}" . PHP_EOL;
        
        file_put_contents($this->logPath, $logEntry, FILE_APPEND | LOCK_EX);
    }

    private function getLevelName($level){
        $levels = [
            self::DEBUG => 'DEBUG',
            self::INFO => 'INFO',
            self::WARNING => 'WARNING',
            self::ERROR => 'ERROR',
            self::CRITICAL => 'CRITICAL'
        ];
        
        return $levels[$level] ?? 'UNKNOWN';
    }

    public function logUserActivity($userId, $action, $details = []){
        $this->info("User activity: {$action}", [
            'user_id' => $userId,
            'action' => $action,
            'details' => $details,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);
    }

    public function logSystemEvent($event, $details = []){
        $this->info("System event: {$event}", [
            'event' => $event,
            'details' => $details
        ]);
    }

    public function logError($error, $context = []){
        $this->error($error, array_merge($context, [
            'file' => debug_backtrace()[0]['file'] ?? 'unknown',
            'line' => debug_backtrace()[0]['line'] ?? 'unknown'
        ]));
    }
}
?>
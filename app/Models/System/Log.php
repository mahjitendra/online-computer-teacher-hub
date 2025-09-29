<?php
class Log extends BaseModel {
    protected $table = 'system_logs';
    protected $fillable = ['level', 'message', 'context', 'user_id', 'ip_address', 'user_agent'];

    public function createLog($level, $message, $context = [], $userId = null){
        $data = [
            'level' => $level,
            'message' => $message,
            'context' => json_encode($context),
            'user_id' => $userId,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
        ];
        
        return $this->create($data);
    }

    public function getLogsByLevel($level, $limit = 100){
        $this->db->query('SELECT l.*, u.name as user_name 
                         FROM system_logs l
                         LEFT JOIN users u ON l.user_id = u.id
                         WHERE l.level = :level
                         ORDER BY l.created_at DESC
                         LIMIT :limit');
        $this->db->bind(':level', $level);
        $this->db->bind(':limit', $limit);
        return $this->db->resultSet();
    }

    public function getLogsByUser($userId, $limit = 100){
        $this->db->query('SELECT * FROM system_logs 
                         WHERE user_id = :user_id
                         ORDER BY created_at DESC
                         LIMIT :limit');
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':limit', $limit);
        return $this->db->resultSet();
    }

    public function getLogsByDateRange($startDate, $endDate, $limit = 1000){
        $this->db->query('SELECT l.*, u.name as user_name 
                         FROM system_logs l
                         LEFT JOIN users u ON l.user_id = u.id
                         WHERE l.created_at BETWEEN :start_date AND :end_date
                         ORDER BY l.created_at DESC
                         LIMIT :limit');
        $this->db->bind(':start_date', $startDate);
        $this->db->bind(':end_date', $endDate);
        $this->db->bind(':limit', $limit);
        return $this->db->resultSet();
    }

    public function searchLogs($searchTerm, $level = null, $limit = 100){
        $whereClause = 'l.message LIKE :search';
        $params = [':search' => '%' . $searchTerm . '%'];
        
        if($level){
            $whereClause .= ' AND l.level = :level';
            $params[':level'] = $level;
        }
        
        $this->db->query("SELECT l.*, u.name as user_name 
                         FROM system_logs l
                         LEFT JOIN users u ON l.user_id = u.id
                         WHERE {$whereClause}
                         ORDER BY l.created_at DESC
                         LIMIT :limit");
        
        foreach($params as $key => $value){
            $this->db->bind($key, $value);
        }
        $this->db->bind(':limit', $limit);
        
        return $this->db->resultSet();
    }

    public function getLogStats($period = 'week'){
        $dateCondition = '';
        switch($period){
            case 'day':
                $dateCondition = 'WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)';
                break;
            case 'week':
                $dateCondition = 'WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 WEEK)';
                break;
            case 'month':
                $dateCondition = 'WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)';
                break;
        }
        
        $this->db->query("SELECT 
                         COUNT(*) as total_logs,
                         SUM(CASE WHEN level = 'error' THEN 1 ELSE 0 END) as error_count,
                         SUM(CASE WHEN level = 'warning' THEN 1 ELSE 0 END) as warning_count,
                         SUM(CASE WHEN level = 'info' THEN 1 ELSE 0 END) as info_count,
                         SUM(CASE WHEN level = 'debug' THEN 1 ELSE 0 END) as debug_count,
                         COUNT(DISTINCT user_id) as unique_users,
                         COUNT(DISTINCT ip_address) as unique_ips
                         FROM system_logs {$dateCondition}");
        return $this->db->single();
    }

    public function getErrorLogs($limit = 50){
        return $this->getLogsByLevel('error', $limit);
    }

    public function getWarningLogs($limit = 50){
        return $this->getLogsByLevel('warning', $limit);
    }

    public function getRecentLogs($limit = 100){
        $this->db->query('SELECT l.*, u.name as user_name 
                         FROM system_logs l
                         LEFT JOIN users u ON l.user_id = u.id
                         ORDER BY l.created_at DESC
                         LIMIT :limit');
        $this->db->bind(':limit', $limit);
        return $this->db->resultSet();
    }

    public function cleanOldLogs($days = 30){
        $this->db->query('DELETE FROM system_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL :days DAY)');
        $this->db->bind(':days', $days);
        return $this->db->execute();
    }

    public function getLogsByIpAddress($ipAddress, $limit = 100){
        $this->db->query('SELECT l.*, u.name as user_name 
                         FROM system_logs l
                         LEFT JOIN users u ON l.user_id = u.id
                         WHERE l.ip_address = :ip_address
                         ORDER BY l.created_at DESC
                         LIMIT :limit');
        $this->db->bind(':ip_address', $ipAddress);
        $this->db->bind(':limit', $limit);
        return $this->db->resultSet();
    }

    public function getTopIpAddresses($limit = 20){
        $this->db->query('SELECT ip_address, COUNT(*) as log_count
                         FROM system_logs
                         WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)
                         GROUP BY ip_address
                         ORDER BY log_count DESC
                         LIMIT :limit');
        $this->db->bind(':limit', $limit);
        return $this->db->resultSet();
    }

    public function exportLogs($startDate, $endDate, $format = 'json'){
        $logs = $this->getLogsByDateRange($startDate, $endDate, 10000);
        
        switch($format){
            case 'csv':
                return $this->exportToCsv($logs);
            case 'json':
            default:
                return json_encode($logs, JSON_PRETTY_PRINT);
        }
    }

    private function exportToCsv($logs){
        $csv = "ID,Level,Message,User,IP Address,Created At\n";
        
        foreach($logs as $log){
            $csv .= sprintf(
                "%d,%s,\"%s\",%s,%s,%s\n",
                $log->id,
                $log->level,
                str_replace('"', '""', $log->message),
                $log->user_name ?? 'N/A',
                $log->ip_address ?? 'N/A',
                $log->created_at
            );
        }
        
        return $csv;
    }

    // Convenience methods for different log levels
    public function debug($message, $context = [], $userId = null){
        return $this->createLog('debug', $message, $context, $userId);
    }

    public function info($message, $context = [], $userId = null){
        return $this->createLog('info', $message, $context, $userId);
    }

    public function warning($message, $context = [], $userId = null){
        return $this->createLog('warning', $message, $context, $userId);
    }

    public function error($message, $context = [], $userId = null){
        return $this->createLog('error', $message, $context, $userId);
    }

    public function critical($message, $context = [], $userId = null){
        return $this->createLog('critical', $message, $context, $userId);
    }
}
?>
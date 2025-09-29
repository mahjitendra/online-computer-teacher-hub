<?php
class Backup extends BaseModel {
    protected $table = 'backups';
    protected $fillable = ['backup_type', 'file_path', 'file_size', 'status', 'created_by'];

    public function createBackup($type = 'full', $createdBy = null){
        $backupId = uniqid('backup_');
        $timestamp = date('Y-m-d_H-i-s');
        $fileName = "{$type}_backup_{$timestamp}.sql";
        $filePath = "storage/backups/{$fileName}";
        
        $data = [
            'backup_type' => $type,
            'file_path' => $filePath,
            'status' => 'in_progress',
            'created_by' => $createdBy
        ];
        
        $backupRecordId = $this->create($data);
        
        if($backupRecordId){
            // Perform the actual backup
            $success = $this->performBackup($type, $filePath);
            
            if($success){
                $fileSize = file_exists($filePath) ? filesize($filePath) : 0;
                $this->update($backupRecordId, [
                    'status' => 'completed',
                    'file_size' => $fileSize
                ]);
                return $backupRecordId;
            } else {
                $this->update($backupRecordId, ['status' => 'failed']);
                return false;
            }
        }
        
        return false;
    }

    private function performBackup($type, $filePath){
        try {
            $db = new Database();
            
            // Create backup directory if it doesn't exist
            $backupDir = dirname($filePath);
            if(!is_dir($backupDir)){
                mkdir($backupDir, 0755, true);
            }
            
            $backupContent = "-- Database Backup\n";
            $backupContent .= "-- Generated on: " . date('Y-m-d H:i:s') . "\n";
            $backupContent .= "-- Backup Type: {$type}\n\n";
            
            // Get all tables
            $tables = $this->getAllTables();
            
            foreach($tables as $table){
                if($type === 'structure_only'){
                    $backupContent .= $this->getTableStructure($table);
                } else {
                    $backupContent .= $this->getTableStructure($table);
                    $backupContent .= $this->getTableData($table);
                }
            }
            
            return file_put_contents($filePath, $backupContent) !== false;
            
        } catch(Exception $e){
            error_log("Backup failed: " . $e->getMessage());
            return false;
        }
    }

    private function getAllTables(){
        $this->db->query("SHOW TABLES");
        $results = $this->db->resultSet();
        
        $tables = [];
        foreach($results as $result){
            $tables[] = array_values((array)$result)[0];
        }
        
        return $tables;
    }

    private function getTableStructure($tableName){
        $this->db->query("SHOW CREATE TABLE `{$tableName}`");
        $result = $this->db->single();
        
        $createTable = array_values((array)$result)[1];
        
        return "\n-- Table structure for `{$tableName}`\n" .
               "DROP TABLE IF EXISTS `{$tableName}`;\n" .
               $createTable . ";\n\n";
    }

    private function getTableData($tableName){
        $this->db->query("SELECT * FROM `{$tableName}`");
        $rows = $this->db->resultSet();
        
        if(empty($rows)){
            return "-- No data for table `{$tableName}`\n\n";
        }
        
        $data = "\n-- Data for table `{$tableName}`\n";
        $data .= "INSERT INTO `{$tableName}` VALUES\n";
        
        $values = [];
        foreach($rows as $row){
            $rowData = [];
            foreach((array)$row as $value){
                if($value === null){
                    $rowData[] = 'NULL';
                } else {
                    $rowData[] = "'" . addslashes($value) . "'";
                }
            }
            $values[] = '(' . implode(', ', $rowData) . ')';
        }
        
        $data .= implode(",\n", $values) . ";\n\n";
        
        return $data;
    }

    public function getAllBackups(){
        $this->db->query('SELECT b.*, u.name as created_by_name 
                         FROM backups b
                         LEFT JOIN users u ON b.created_by = u.id
                         ORDER BY b.created_at DESC');
        return $this->db->resultSet();
    }

    public function getBackupsByStatus($status){
        $this->db->query('SELECT b.*, u.name as created_by_name 
                         FROM backups b
                         LEFT JOIN users u ON b.created_by = u.id
                         WHERE b.status = :status
                         ORDER BY b.created_at DESC');
        $this->db->bind(':status', $status);
        return $this->db->resultSet();
    }

    public function deleteBackup($backupId){
        $backup = $this->find($backupId);
        if(!$backup){
            return false;
        }
        
        // Delete the file
        if(file_exists($backup->file_path)){
            unlink($backup->file_path);
        }
        
        // Delete the record
        return $this->delete($backupId);
    }

    public function downloadBackup($backupId){
        $backup = $this->find($backupId);
        if(!$backup || !file_exists($backup->file_path)){
            return false;
        }
        
        $fileName = basename($backup->file_path);
        
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Content-Length: ' . filesize($backup->file_path));
        
        readfile($backup->file_path);
        exit();
    }

    public function restoreBackup($backupId){
        $backup = $this->find($backupId);
        if(!$backup || !file_exists($backup->file_path)){
            return false;
        }
        
        try {
            $sql = file_get_contents($backup->file_path);
            
            // Split SQL into individual statements
            $statements = array_filter(array_map('trim', explode(';', $sql)));
            
            foreach($statements as $statement){
                if(!empty($statement)){
                    $this->db->query($statement);
                    $this->db->execute();
                }
            }
            
            return true;
            
        } catch(Exception $e){
            error_log("Restore failed: " . $e->getMessage());
            return false;
        }
    }

    public function getBackupStats(){
        $this->db->query('SELECT 
                         COUNT(*) as total_backups,
                         SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as successful_backups,
                         SUM(CASE WHEN status = "failed" THEN 1 ELSE 0 END) as failed_backups,
                         SUM(file_size) as total_size,
                         AVG(file_size) as average_size,
                         MAX(created_at) as last_backup
                         FROM backups');
        return $this->db->single();
    }

    public function cleanOldBackups($keepDays = 30){
        $this->db->query('SELECT * FROM backups 
                         WHERE created_at < DATE_SUB(NOW(), INTERVAL :days DAY)
                         AND status = "completed"');
        $this->db->bind(':days', $keepDays);
        $oldBackups = $this->db->resultSet();
        
        $deleted = 0;
        foreach($oldBackups as $backup){
            if($this->deleteBackup($backup->id)){
                $deleted++;
            }
        }
        
        return $deleted;
    }

    public function scheduleBackup($type = 'full', $frequency = 'daily'){
        // This would integrate with a cron job system
        // For now, we'll just create a scheduled backup record
        $data = [
            'backup_type' => $type,
            'status' => 'scheduled',
            'scheduled_for' => $this->getNextScheduledTime($frequency)
        ];
        
        return $this->create($data);
    }

    private function getNextScheduledTime($frequency){
        switch($frequency){
            case 'hourly':
                return date('Y-m-d H:i:s', strtotime('+1 hour'));
            case 'daily':
                return date('Y-m-d H:i:s', strtotime('+1 day'));
            case 'weekly':
                return date('Y-m-d H:i:s', strtotime('+1 week'));
            case 'monthly':
                return date('Y-m-d H:i:s', strtotime('+1 month'));
            default:
                return date('Y-m-d H:i:s', strtotime('+1 day'));
        }
    }

    public function getScheduledBackups(){
        $this->db->query('SELECT * FROM backups 
                         WHERE status = "scheduled" 
                         AND scheduled_for <= NOW()
                         ORDER BY scheduled_for ASC');
        return $this->db->resultSet();
    }

    public function processScheduledBackups(){
        $scheduledBackups = $this->getScheduledBackups();
        $processed = 0;
        
        foreach($scheduledBackups as $backup){
            if($this->createBackup($backup->backup_type)){
                $this->delete($backup->id);
                $processed++;
            }
        }
        
        return $processed;
    }
}
?>
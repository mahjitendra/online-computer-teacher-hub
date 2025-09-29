<?php
class Setting extends BaseModel {
    protected $table = 'settings';
    protected $fillable = ['setting_key', 'setting_value', 'setting_type', 'description', 'is_public'];

    public function getSetting($key, $default = null){
        $this->db->query('SELECT setting_value, setting_type FROM settings WHERE setting_key = :key');
        $this->db->bind(':key', $key);
        $result = $this->db->single();
        
        if(!$result){
            return $default;
        }
        
        return $this->castValue($result->setting_value, $result->setting_type);
    }

    public function setSetting($key, $value, $type = 'string', $description = ''){
        $existingSetting = $this->getSetting($key);
        
        $data = [
            'setting_key' => $key,
            'setting_value' => $this->prepareValue($value, $type),
            'setting_type' => $type,
            'description' => $description
        ];
        
        if($existingSetting !== null){
            $this->db->query('UPDATE settings SET setting_value = :value, setting_type = :type, description = :description WHERE setting_key = :key');
            $this->db->bind(':key', $key);
            $this->db->bind(':value', $data['setting_value']);
            $this->db->bind(':type', $type);
            $this->db->bind(':description', $description);
            return $this->db->execute();
        } else {
            return $this->create($data);
        }
    }

    public function getAllSettings(){
        $this->db->query('SELECT * FROM settings ORDER BY setting_key ASC');
        $settings = $this->db->resultSet();
        
        $result = [];
        foreach($settings as $setting){
            $result[$setting->setting_key] = [
                'value' => $this->castValue($setting->setting_value, $setting->setting_type),
                'type' => $setting->setting_type,
                'description' => $setting->description,
                'is_public' => $setting->is_public
            ];
        }
        
        return $result;
    }

    public function getPublicSettings(){
        $this->db->query('SELECT setting_key, setting_value, setting_type FROM settings WHERE is_public = 1');
        $settings = $this->db->resultSet();
        
        $result = [];
        foreach($settings as $setting){
            $result[$setting->setting_key] = $this->castValue($setting->setting_value, $setting->setting_type);
        }
        
        return $result;
    }

    public function getSettingsByCategory($category){
        $this->db->query('SELECT * FROM settings WHERE setting_key LIKE :category ORDER BY setting_key ASC');
        $this->db->bind(':category', $category . '%');
        $settings = $this->db->resultSet();
        
        $result = [];
        foreach($settings as $setting){
            $result[$setting->setting_key] = [
                'value' => $this->castValue($setting->setting_value, $setting->setting_type),
                'type' => $setting->setting_type,
                'description' => $setting->description
            ];
        }
        
        return $result;
    }

    public function updateSettings($settings){
        $updated = 0;
        
        foreach($settings as $key => $value){
            if($this->updateSetting($key, $value)){
                $updated++;
            }
        }
        
        return $updated;
    }

    public function updateSetting($key, $value){
        $this->db->query('SELECT setting_type FROM settings WHERE setting_key = :key');
        $this->db->bind(':key', $key);
        $result = $this->db->single();
        
        if(!$result){
            return false;
        }
        
        $this->db->query('UPDATE settings SET setting_value = :value WHERE setting_key = :key');
        $this->db->bind(':key', $key);
        $this->db->bind(':value', $this->prepareValue($value, $result->setting_type));
        return $this->db->execute();
    }

    public function deleteSetting($key){
        $this->db->query('DELETE FROM settings WHERE setting_key = :key');
        $this->db->bind(':key', $key);
        return $this->db->execute();
    }

    private function castValue($value, $type){
        switch($type){
            case 'boolean':
                return (bool) $value;
            case 'integer':
                return (int) $value;
            case 'json':
                return json_decode($value, true);
            default:
                return $value;
        }
    }

    private function prepareValue($value, $type){
        switch($type){
            case 'boolean':
                return $value ? '1' : '0';
            case 'integer':
                return (string) intval($value);
            case 'json':
                return json_encode($value);
            default:
                return (string) $value;
        }
    }

    public function initializeDefaultSettings(){
        $defaultSettings = [
            'site_name' => ['value' => 'Online Computer Teacher Hub', 'type' => 'string', 'description' => 'Site name', 'public' => true],
            'site_description' => ['value' => 'Your one-stop platform for computer science education', 'type' => 'string', 'description' => 'Site description', 'public' => true],
            'maintenance_mode' => ['value' => false, 'type' => 'boolean', 'description' => 'Enable maintenance mode', 'public' => false],
            'user_registration' => ['value' => true, 'type' => 'boolean', 'description' => 'Allow user registration', 'public' => true],
            'email_verification' => ['value' => false, 'type' => 'boolean', 'description' => 'Require email verification', 'public' => false],
            'max_file_upload_size' => ['value' => 10485760, 'type' => 'integer', 'description' => 'Maximum file upload size in bytes', 'public' => false],
            'course_approval_required' => ['value' => true, 'type' => 'boolean', 'description' => 'Require admin approval for courses', 'public' => false],
            'payment_gateway' => ['value' => 'stripe', 'type' => 'string', 'description' => 'Default payment gateway', 'public' => false],
            'currency' => ['value' => 'USD', 'type' => 'string', 'description' => 'Default currency', 'public' => true],
            'timezone' => ['value' => 'UTC', 'type' => 'string', 'description' => 'Default timezone', 'public' => true],
            'items_per_page' => ['value' => 20, 'type' => 'integer', 'description' => 'Items per page for pagination', 'public' => false],
            'session_timeout' => ['value' => 7200, 'type' => 'integer', 'description' => 'Session timeout in seconds', 'public' => false],
            'backup_frequency' => ['value' => 'daily', 'type' => 'string', 'description' => 'Backup frequency', 'public' => false],
            'log_level' => ['value' => 'info', 'type' => 'string', 'description' => 'Logging level', 'public' => false],
            'cache_enabled' => ['value' => true, 'type' => 'boolean', 'description' => 'Enable caching', 'public' => false]
        ];
        
        $initialized = 0;
        foreach($defaultSettings as $key => $setting){
            if($this->getSetting($key) === null){
                $data = [
                    'setting_key' => $key,
                    'setting_value' => $this->prepareValue($setting['value'], $setting['type']),
                    'setting_type' => $setting['type'],
                    'description' => $setting['description'],
                    'is_public' => $setting['public'] ? 1 : 0
                ];
                
                if($this->create($data)){
                    $initialized++;
                }
            }
        }
        
        return $initialized;
    }

    public function exportSettings(){
        $settings = $this->getAllSettings();
        return json_encode($settings, JSON_PRETTY_PRINT);
    }

    public function importSettings($jsonData){
        $settings = json_decode($jsonData, true);
        if(!$settings){
            return false;
        }
        
        $imported = 0;
        foreach($settings as $key => $setting){
            if($this->setSetting($key, $setting['value'], $setting['type'], $setting['description'])){
                $imported++;
            }
        }
        
        return $imported;
    }
}
?>
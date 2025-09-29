<?php
class JobAlert extends BaseModel {
    protected $table = 'job_alerts';
    protected $fillable = ['user_id', 'keywords', 'location', 'category_id', 'job_type', 'salary_min', 'salary_max', 'frequency', 'is_active'];

    public function createAlert($data){
        $data['is_active'] = 1;
        return $this->create($data);
    }

    public function getAlertsByUser($userId){
        $this->db->query('SELECT ja.*, jc.name as category_name 
                         FROM job_alerts ja
                         LEFT JOIN job_categories jc ON ja.category_id = jc.id
                         WHERE ja.user_id = :user_id AND ja.is_active = 1
                         ORDER BY ja.created_at DESC');
        $this->db->bind(':user_id', $userId);
        return $this->db->resultSet();
    }

    public function findMatchingJobs($alertId){
        $alert = $this->find($alertId);
        if(!$alert){
            return [];
        }

        $conditions = [];
        $params = [];

        // Build query conditions based on alert criteria
        if(!empty($alert->keywords)){
            $conditions[] = '(j.title LIKE :keywords OR j.description LIKE :keywords)';
            $params[':keywords'] = '%' . $alert->keywords . '%';
        }

        if(!empty($alert->location)){
            $conditions[] = 'j.location LIKE :location';
            $params[':location'] = '%' . $alert->location . '%';
        }

        if($alert->category_id){
            $conditions[] = 'j.category_id = :category_id';
            $params[':category_id'] = $alert->category_id;
        }

        if(!empty($alert->job_type)){
            $conditions[] = 'j.job_type = :job_type';
            $params[':job_type'] = $alert->job_type;
        }

        if($alert->salary_min){
            $conditions[] = 'j.salary_max >= :salary_min';
            $params[':salary_min'] = $alert->salary_min;
        }

        if($alert->salary_max){
            $conditions[] = 'j.salary_min <= :salary_max';
            $params[':salary_max'] = $alert->salary_max;
        }

        $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
        $whereClause .= !empty($conditions) ? ' AND j.is_active = 1' : 'WHERE j.is_active = 1';

        $this->db->query("SELECT j.*, jc.name as category_name 
                         FROM jobs j
                         LEFT JOIN job_categories jc ON j.category_id = jc.id
                         {$whereClause}
                         ORDER BY j.created_at DESC");

        foreach($params as $key => $value){
            $this->db->bind($key, $value);
        }

        return $this->db->resultSet();
    }

    public function processAlerts(){
        $alerts = $this->findWhere(['is_active' => 1]);
        $processedAlerts = 0;

        foreach($alerts as $alert){
            if($this->shouldProcessAlert($alert)){
                $matchingJobs = $this->findMatchingJobs($alert->id);
                
                if(!empty($matchingJobs)){
                    $this->sendAlertEmail($alert, $matchingJobs);
                    $this->updateLastProcessed($alert->id);
                    $processedAlerts++;
                }
            }
        }

        return $processedAlerts;
    }

    private function shouldProcessAlert($alert){
        $lastProcessed = $alert->last_processed ?? null;
        
        if(!$lastProcessed){
            return true; // Never processed before
        }

        $now = time();
        $lastProcessedTime = strtotime($lastProcessed);

        switch($alert->frequency){
            case 'daily':
                return ($now - $lastProcessedTime) >= 86400; // 24 hours
            case 'weekly':
                return ($now - $lastProcessedTime) >= 604800; // 7 days
            case 'monthly':
                return ($now - $lastProcessedTime) >= 2592000; // 30 days
            default:
                return false;
        }
    }

    private function sendAlertEmail($alert, $jobs){
        $emailService = new EmailService();
        $user = $this->model('User')->find($alert->user_id);
        
        $emailData = [
            'user' => $user,
            'alert' => $alert,
            'jobs' => $jobs,
            'job_count' => count($jobs)
        ];

        return $emailService->sendJobAlert($user->email, $emailData);
    }

    private function updateLastProcessed($alertId){
        return $this->update($alertId, ['last_processed' => date('Y-m-d H:i:s')]);
    }

    public function deactivateAlert($alertId){
        return $this->update($alertId, ['is_active' => 0]);
    }

    public function getAlertStats($userId = null){
        $whereClause = $userId ? 'WHERE user_id = :user_id' : '';
        
        $this->db->query("SELECT 
                         COUNT(*) as total_alerts,
                         SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_alerts,
                         SUM(CASE WHEN frequency = 'daily' THEN 1 ELSE 0 END) as daily_alerts,
                         SUM(CASE WHEN frequency = 'weekly' THEN 1 ELSE 0 END) as weekly_alerts,
                         SUM(CASE WHEN frequency = 'monthly' THEN 1 ELSE 0 END) as monthly_alerts
                         FROM job_alerts {$whereClause}");
        
        if($userId){
            $this->db->bind(':user_id', $userId);
        }
        
        return $this->db->single();
    }
}
?>
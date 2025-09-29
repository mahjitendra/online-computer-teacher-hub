<?php
class JobApplication extends BaseModel {
    protected $table = 'job_applications';
    protected $fillable = ['student_id', 'job_id', 'cover_letter', 'resume_path', 'status', 'applied_at'];

    public function createApplication($data){
        $data['applied_at'] = date('Y-m-d H:i:s');
        $data['status'] = 'submitted';
        return $this->create($data);
    }

    public function getApplicationsByStudent($studentId){
        $this->db->query('SELECT ja.*, j.title as job_title, j.company, j.location, jc.name as category_name
                         FROM job_applications ja
                         JOIN jobs j ON ja.job_id = j.id
                         JOIN job_categories jc ON j.category_id = jc.id
                         WHERE ja.student_id = :student_id
                         ORDER BY ja.applied_at DESC');
        $this->db->bind(':student_id', $studentId);
        return $this->db->resultSet();
    }

    public function getApplicationsByJob($jobId){
        $this->db->query('SELECT ja.*, u.name as student_name, u.email as student_email, up.phone, up.skills
                         FROM job_applications ja
                         JOIN users u ON ja.student_id = u.id
                         LEFT JOIN user_profiles up ON u.id = up.user_id
                         WHERE ja.job_id = :job_id
                         ORDER BY ja.applied_at DESC');
        $this->db->bind(':job_id', $jobId);
        return $this->db->resultSet();
    }

    public function getAllApplicationsForAdmin(){
        $this->db->query('SELECT ja.*, j.title as job_title, j.company, u.name as student_name, u.email as student_email
                         FROM job_applications ja
                         JOIN jobs j ON ja.job_id = j.id
                         JOIN users u ON ja.student_id = u.id
                         ORDER BY ja.applied_at DESC');
        return $this->db->resultSet();
    }

    public function updateApplicationStatus($applicationId, $status, $notes = ''){
        $data = [
            'status' => $status,
            'status_updated_at' => date('Y-m-d H:i:s'),
            'admin_notes' => $notes
        ];
        
        return $this->update($applicationId, $data);
    }

    public function withdrawApplication($applicationId){
        return $this->updateApplicationStatus($applicationId, 'withdrawn');
    }

    public function getApplicationStats($studentId = null){
        $whereClause = $studentId ? 'WHERE student_id = :student_id' : '';
        
        $this->db->query("SELECT 
                         COUNT(*) as total_applications,
                         SUM(CASE WHEN status = 'submitted' THEN 1 ELSE 0 END) as submitted,
                         SUM(CASE WHEN status = 'viewed' THEN 1 ELSE 0 END) as viewed,
                         SUM(CASE WHEN status = 'interviewing' THEN 1 ELSE 0 END) as interviewing,
                         SUM(CASE WHEN status = 'hired' THEN 1 ELSE 0 END) as hired,
                         SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
                         FROM job_applications {$whereClause}");
        
        if($studentId){
            $this->db->bind(':student_id', $studentId);
        }
        
        return $this->db->single();
    }

    public function hasApplied($studentId, $jobId){
        $this->db->query('SELECT id FROM job_applications WHERE student_id = :student_id AND job_id = :job_id');
        $this->db->bind(':student_id', $studentId);
        $this->db->bind(':job_id', $jobId);
        return $this->db->single() !== false;
    }

    public function getRecentApplications($limit = 10){
        $this->db->query('SELECT ja.*, j.title as job_title, j.company, u.name as student_name
                         FROM job_applications ja
                         JOIN jobs j ON ja.job_id = j.id
                         JOIN users u ON ja.student_id = u.id
                         ORDER BY ja.applied_at DESC
                         LIMIT :limit');
        $this->db->bind(':limit', $limit);
        return $this->db->resultSet();
    }
}
?>
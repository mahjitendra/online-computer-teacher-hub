<?php
class GovernmentExam extends BaseModel {
    protected $table = 'government_exams';
    protected $fillable = ['title', 'description', 'department', 'category', 'application_start_date', 'application_end_date', 'exam_date', 'eligibility_criteria', 'syllabus', 'notification_url', 'is_active'];

    public function getActiveExams(){
        $this->db->query('SELECT * FROM government_exams 
                         WHERE is_active = 1 AND application_end_date >= CURDATE() 
                         ORDER BY application_end_date ASC');
        return $this->db->resultSet();
    }

    public function getUpcomingExams($limit = 10){
        $this->db->query('SELECT * FROM government_exams 
                         WHERE is_active = 1 AND exam_date >= CURDATE() 
                         ORDER BY exam_date ASC 
                         LIMIT :limit');
        $this->db->bind(':limit', $limit);
        return $this->db->resultSet();
    }

    public function getExamsByCategory($category){
        $this->db->query('SELECT * FROM government_exams 
                         WHERE category = :category AND is_active = 1 
                         ORDER BY application_end_date ASC');
        $this->db->bind(':category', $category);
        return $this->db->resultSet();
    }

    public function getExamsByDepartment($department){
        $this->db->query('SELECT * FROM government_exams 
                         WHERE department = :department AND is_active = 1 
                         ORDER BY application_end_date ASC');
        $this->db->bind(':department', $department);
        return $this->db->resultSet();
    }

    public function searchExams($searchTerm){
        $this->db->query('SELECT * FROM government_exams 
                         WHERE (title LIKE :search OR description LIKE :search OR department LIKE :search) 
                         AND is_active = 1 
                         ORDER BY application_end_date ASC');
        $this->db->bind(':search', '%' . $searchTerm . '%');
        return $this->db->resultSet();
    }

    public function getExamCategories(){
        $this->db->query('SELECT DISTINCT category FROM government_exams WHERE is_active = 1 ORDER BY category');
        return $this->db->resultSet();
    }

    public function getDepartments(){
        $this->db->query('SELECT DISTINCT department FROM government_exams WHERE is_active = 1 ORDER BY department');
        return $this->db->resultSet();
    }

    public function getExamsClosingSoon($days = 7){
        $this->db->query('SELECT * FROM government_exams 
                         WHERE is_active = 1 
                         AND application_end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL :days DAY)
                         ORDER BY application_end_date ASC');
        $this->db->bind(':days', $days);
        return $this->db->resultSet();
    }

    public function addExamAlert($userId, $examId){
        $this->db->query('INSERT INTO exam_alerts (user_id, exam_id, created_at) VALUES (:user_id, :exam_id, NOW())');
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':exam_id', $examId);
        return $this->db->execute();
    }

    public function removeExamAlert($userId, $examId){
        $this->db->query('DELETE FROM exam_alerts WHERE user_id = :user_id AND exam_id = :exam_id');
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':exam_id', $examId);
        return $this->db->execute();
    }

    public function getUserAlerts($userId){
        $this->db->query('SELECT ge.*, ea.created_at as alert_created 
                         FROM government_exams ge
                         JOIN exam_alerts ea ON ge.id = ea.exam_id
                         WHERE ea.user_id = :user_id AND ge.is_active = 1
                         ORDER BY ge.application_end_date ASC');
        $this->db->bind(':user_id', $userId);
        return $this->db->resultSet();
    }

    public function hasAlert($userId, $examId){
        $this->db->query('SELECT id FROM exam_alerts WHERE user_id = :user_id AND exam_id = :exam_id');
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':exam_id', $examId);
        return $this->db->single() !== false;
    }
}
?>
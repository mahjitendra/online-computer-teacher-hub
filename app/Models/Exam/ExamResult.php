<?php
class ExamResult extends BaseModel {
    protected $table = 'exam_results';
    protected $fillable = ['attempt_id', 'student_id', 'exam_id', 'score', 'percentage', 'passed', 'grade', 'feedback'];

    public function createResult($data){
        // Calculate percentage and grade
        $data['percentage'] = ($data['score'] / $data['total_questions']) * 100;
        $data['passed'] = $data['percentage'] >= ($data['passing_score'] ?? 70);
        $data['grade'] = $this->calculateGrade($data['percentage']);
        
        return $this->create($data);
    }

    public function getResultsByStudent($studentId){
        $this->db->query('SELECT er.*, e.title as exam_title, c.title as course_title, ea.start_time, ea.end_time
                         FROM exam_results er
                         JOIN exams e ON er.exam_id = e.id
                         JOIN courses c ON e.course_id = c.id
                         JOIN exam_attempts ea ON er.attempt_id = ea.id
                         WHERE er.student_id = :student_id
                         ORDER BY er.created_at DESC');
        $this->db->bind(':student_id', $studentId);
        return $this->db->resultSet();
    }

    public function getResultsByExam($examId){
        $this->db->query('SELECT er.*, u.name as student_name, u.email as student_email, ea.start_time, ea.end_time
                         FROM exam_results er
                         JOIN users u ON er.student_id = u.id
                         JOIN exam_attempts ea ON er.attempt_id = ea.id
                         WHERE er.exam_id = :exam_id
                         ORDER BY er.score DESC');
        $this->db->bind(':exam_id', $examId);
        return $this->db->resultSet();
    }

    public function getResultWithDetails($resultId){
        $this->db->query('SELECT er.*, e.title as exam_title, e.duration, c.title as course_title,
                         u.name as student_name, u.email as student_email,
                         ea.start_time, ea.end_time, ea.answers
                         FROM exam_results er
                         JOIN exams e ON er.exam_id = e.id
                         JOIN courses c ON e.course_id = c.id
                         JOIN users u ON er.student_id = u.id
                         JOIN exam_attempts ea ON er.attempt_id = ea.id
                         WHERE er.id = :result_id');
        $this->db->bind(':result_id', $resultId);
        return $this->db->single();
    }

    public function getExamStatistics($examId){
        $this->db->query('SELECT 
                         COUNT(*) as total_attempts,
                         AVG(score) as average_score,
                         AVG(percentage) as average_percentage,
                         MAX(score) as highest_score,
                         MIN(score) as lowest_score,
                         SUM(CASE WHEN passed = 1 THEN 1 ELSE 0 END) as passed_count
                         FROM exam_results 
                         WHERE exam_id = :exam_id');
        $this->db->bind(':exam_id', $examId);
        return $this->db->single();
    }

    public function getGradeDistribution($examId){
        $this->db->query('SELECT grade, COUNT(*) as count 
                         FROM exam_results 
                         WHERE exam_id = :exam_id 
                         GROUP BY grade 
                         ORDER BY grade DESC');
        $this->db->bind(':exam_id', $examId);
        return $this->db->resultSet();
    }

    public function getTopPerformers($examId, $limit = 10){
        $this->db->query('SELECT er.*, u.name as student_name 
                         FROM exam_results er
                         JOIN users u ON er.student_id = u.id
                         WHERE er.exam_id = :exam_id
                         ORDER BY er.score DESC
                         LIMIT :limit');
        $this->db->bind(':exam_id', $examId);
        $this->db->bind(':limit', $limit);
        return $this->db->resultSet();
    }

    private function calculateGrade($percentage){
        if($percentage >= 90) return 'A+';
        if($percentage >= 85) return 'A';
        if($percentage >= 80) return 'A-';
        if($percentage >= 75) return 'B+';
        if($percentage >= 70) return 'B';
        if($percentage >= 65) return 'B-';
        if($percentage >= 60) return 'C+';
        if($percentage >= 55) return 'C';
        if($percentage >= 50) return 'C-';
        if($percentage >= 45) return 'D';
        return 'F';
    }

    public function generateCertificate($resultId){
        $result = $this->getResultWithDetails($resultId);
        
        if($result && $result->passed){
            $certificateModel = $this->model('Certificate');
            return $certificateModel->generateCertificate($result);
        }
        
        return false;
    }
}
?>
<?php
class ExamAttempt extends BaseModel {
    protected $table = 'exam_attempts';
    protected $fillable = ['student_id', 'exam_id', 'start_time', 'end_time', 'score', 'status', 'answers'];

    public function startAttempt($studentId, $examId){
        $data = [
            'student_id' => $studentId,
            'exam_id' => $examId,
            'start_time' => date('Y-m-d H:i:s'),
            'status' => 'in_progress'
        ];

        return $this->create($data);
    }

    public function submitAttempt($attemptId, $answers, $score){
        $data = [
            'end_time' => date('Y-m-d H:i:s'),
            'answers' => json_encode($answers),
            'score' => $score,
            'status' => 'completed'
        ];

        return $this->update($attemptId, $data);
    }

    public function getAttemptsByStudent($studentId, $examId = null){
        $conditions = ['student_id' => $studentId];
        if($examId){
            $conditions['exam_id'] = $examId;
        }

        return $this->findWhere($conditions);
    }

    public function getAttemptsByExam($examId){
        $this->db->query('SELECT ea.*, u.name as student_name, u.email as student_email 
                         FROM exam_attempts ea 
                         JOIN users u ON ea.student_id = u.id 
                         WHERE ea.exam_id = :exam_id 
                         ORDER BY ea.created_at DESC');
        $this->db->bind(':exam_id', $examId);
        return $this->db->resultSet();
    }

    public function getAttemptWithDetails($attemptId){
        $this->db->query('SELECT ea.*, e.title as exam_title, e.duration, u.name as student_name 
                         FROM exam_attempts ea 
                         JOIN exams e ON ea.exam_id = e.id 
                         JOIN users u ON ea.student_id = u.id 
                         WHERE ea.id = :attempt_id');
        $this->db->bind(':attempt_id', $attemptId);
        return $this->db->single();
    }

    public function getAverageScore($examId){
        $this->db->query('SELECT AVG(score) as average_score FROM exam_attempts WHERE exam_id = :exam_id AND status = "completed"');
        $this->db->bind(':exam_id', $examId);
        $result = $this->db->single();
        return $result ? $result->average_score : 0;
    }

    public function getPassRate($examId, $passingScore = 70){
        $this->db->query('SELECT 
                         COUNT(*) as total_attempts,
                         SUM(CASE WHEN score >= :passing_score THEN 1 ELSE 0 END) as passed_attempts
                         FROM exam_attempts 
                         WHERE exam_id = :exam_id AND status = "completed"');
        $this->db->bind(':exam_id', $examId);
        $this->db->bind(':passing_score', $passingScore);
        
        $result = $this->db->single();
        if($result && $result->total_attempts > 0){
            return ($result->passed_attempts / $result->total_attempts) * 100;
        }
        return 0;
    }

    public function hasActiveAttempt($studentId, $examId){
        $this->db->query('SELECT id FROM exam_attempts WHERE student_id = :student_id AND exam_id = :exam_id AND status = "in_progress"');
        $this->db->bind(':student_id', $studentId);
        $this->db->bind(':exam_id', $examId);
        return $this->db->single() !== false;
    }

    public function getAttemptCount($studentId, $examId){
        $this->db->query('SELECT COUNT(*) as attempt_count FROM exam_attempts WHERE student_id = :student_id AND exam_id = :exam_id');
        $this->db->bind(':student_id', $studentId);
        $this->db->bind(':exam_id', $examId);
        $result = $this->db->single();
        return $result ? $result->attempt_count : 0;
    }
}
?>
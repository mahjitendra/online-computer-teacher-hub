<?php
class Enrollment {
    private $db;

    public function __construct(){
        $this->db = new Database;
    }

    public function isStudentEnrolled($student_id, $course_id){
        $this->db->query('SELECT * FROM enrollments WHERE student_id = :student_id AND course_id = :course_id');
        $this->db->bind(':student_id', $student_id);
        $this->db->bind(':course_id', $course_id);
        $this->db->single();

        if($this->db->rowCount() > 0){
            return true;
        } else {
            return false;
        }
    }

    public function enrollStudent($data){
        $this->db->query('INSERT INTO enrollments (student_id, course_id) VALUES (:student_id, :course_id)');
        // Bind values
        $this->db->bind(':student_id', $data['student_id']);
        $this->db->bind(':course_id', $data['course_id']);

        // Execute
        if($this->db->execute()){
            return true;
        } else {
            return false;
        }
    }

    public function getEnrolledCourses($studentId){
        $this->db->query('SELECT c.*, e.enrolled_at, e.progress, e.completed_at
                         FROM enrollments e
                         JOIN courses c ON e.course_id = c.id
                         WHERE e.student_id = :student_id
                         ORDER BY e.enrolled_at DESC');
        $this->db->bind(':student_id', $studentId);
        return $this->db->resultSet();
    }

    public function getCourseProgress($studentId, $courseId){
        $this->db->query('SELECT progress FROM enrollments 
                         WHERE student_id = :student_id AND course_id = :course_id');
        $this->db->bind(':student_id', $studentId);
        $this->db->bind(':course_id', $courseId);
        $result = $this->db->single();
        return $result ? $result->progress : 0;
    }

    public function updateProgress($studentId, $courseId){
        // Calculate progress based on completed tutorials
        $this->db->query('SELECT 
                         COUNT(t.id) as total_tutorials,
                         COUNT(tp.tutorial_id) as completed_tutorials
                         FROM tutorials t
                         LEFT JOIN tutorial_progress tp ON t.id = tp.tutorial_id 
                         AND tp.student_id = :student_id AND tp.progress_percentage = 100
                         WHERE t.course_id = :course_id');
        $this->db->bind(':student_id', $studentId);
        $this->db->bind(':course_id', $courseId);
        $result = $this->db->single();
        
        $progress = 0;
        if($result && $result->total_tutorials > 0){
            $progress = ($result->completed_tutorials / $result->total_tutorials) * 100;
        }
        
        // Update enrollment progress
        $this->db->query('UPDATE enrollments SET progress = :progress WHERE student_id = :student_id AND course_id = :course_id');
        $this->db->bind(':progress', $progress);
        $this->db->bind(':student_id', $studentId);
        $this->db->bind(':course_id', $courseId);
        
        $updated = $this->db->execute();
        
        // Mark as completed if 100%
        if($progress >= 100){
            $this->db->query('UPDATE enrollments SET completed_at = NOW() WHERE student_id = :student_id AND course_id = :course_id AND completed_at IS NULL');
            $this->db->bind(':student_id', $studentId);
            $this->db->bind(':course_id', $courseId);
            $this->db->execute();
        }
        
        return $updated;
    }

    public function getEnrollmentStats($courseId = null){
        $whereClause = $courseId ? 'WHERE course_id = :course_id' : '';
        
        $this->db->query("SELECT 
                         COUNT(*) as total_enrollments,
                         COUNT(CASE WHEN completed_at IS NOT NULL THEN 1 END) as completed_enrollments,
                         AVG(progress) as average_progress,
                         COUNT(DISTINCT student_id) as unique_students
                         FROM enrollments {$whereClause}");
        
        if($courseId){
            $this->db->bind(':course_id', $courseId);
        }
        
        return $this->db->single();
    }

    public function getRecentEnrollments($limit = 10){
        $this->db->query('SELECT e.*, u.name as student_name, c.title as course_title
                         FROM enrollments e
                         JOIN users u ON e.student_id = u.id
                         JOIN courses c ON e.course_id = c.id
                         ORDER BY e.enrolled_at DESC
                         LIMIT :limit');
        $this->db->bind(':limit', $limit);
        return $this->db->resultSet();
    }
}
?>
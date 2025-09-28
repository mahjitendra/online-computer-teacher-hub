<?php
class Student {
    private $db;

    public function __construct(){
        $this->db = new Database;
    }

    public function getStudentById($id){
        $this->db->query('SELECT u.*, s.* FROM users u 
                         LEFT JOIN students s ON u.id = s.user_id 
                         WHERE u.id = :id AND u.user_type = "student"');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function createStudentProfile($data){
        $this->db->query('INSERT INTO students (user_id, student_id, enrollment_date, status) 
                         VALUES (:user_id, :student_id, :enrollment_date, :status)');
        $this->db->bind(':user_id', $data['user_id']);
        $this->db->bind(':student_id', $data['student_id']);
        $this->db->bind(':enrollment_date', $data['enrollment_date']);
        $this->db->bind(':status', $data['status']);

        return $this->db->execute();
    }

    public function updateStudentProfile($data){
        $this->db->query('UPDATE students SET 
                         phone = :phone, 
                         address = :address, 
                         emergency_contact = :emergency_contact,
                         updated_at = NOW()
                         WHERE user_id = :user_id');
        $this->db->bind(':user_id', $data['user_id']);
        $this->db->bind(':phone', $data['phone']);
        $this->db->bind(':address', $data['address']);
        $this->db->bind(':emergency_contact', $data['emergency_contact']);

        return $this->db->execute();
    }

    public function getStudentStats($student_id){
        $this->db->query('SELECT 
                         COUNT(DISTINCT e.course_id) as enrolled_courses,
                         COUNT(DISTINCT er.exam_id) as exams_taken,
                         COUNT(DISTINCT c.id) as certificates_earned,
                         COUNT(DISTINCT ja.id) as job_applications
                         FROM users u
                         LEFT JOIN enrollments e ON u.id = e.student_id
                         LEFT JOIN exam_results er ON u.id = er.student_id
                         LEFT JOIN certificates c ON u.id = c.student_id
                         LEFT JOIN job_applications ja ON u.id = ja.student_id
                         WHERE u.id = :student_id');
        $this->db->bind(':student_id', $student_id);
        return $this->db->single();
    }

    public function getStudentProgress($student_id){
        $this->db->query('SELECT c.title, e.progress, e.enrolled_at, e.completed_at
                         FROM enrollments e
                         JOIN courses c ON e.course_id = c.id
                         WHERE e.student_id = :student_id
                         ORDER BY e.enrolled_at DESC');
        $this->db->bind(':student_id', $student_id);
        return $this->db->resultSet();
    }

    public function getRecentActivity($student_id, $limit = 10){
        $this->db->query('SELECT "enrollment" as type, c.title as title, e.enrolled_at as date
                         FROM enrollments e
                         JOIN courses c ON e.course_id = c.id
                         WHERE e.student_id = :student_id
                         UNION ALL
                         SELECT "exam" as type, ex.title as title, er.created_at as date
                         FROM exam_results er
                         JOIN exams ex ON er.exam_id = ex.id
                         WHERE er.student_id = :student_id
                         UNION ALL
                         SELECT "certificate" as type, c.title as title, cert.issued_at as date
                         FROM certificates cert
                         JOIN courses c ON cert.course_id = c.id
                         WHERE cert.student_id = :student_id
                         ORDER BY date DESC
                         LIMIT :limit');
        $this->db->bind(':student_id', $student_id);
        $this->db->bind(':limit', $limit);
        return $this->db->resultSet();
    }
}
?>
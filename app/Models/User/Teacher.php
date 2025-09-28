<?php
class Teacher {
    private $db;

    public function __construct(){
        $this->db = new Database;
    }

    public function getTeacherById($id){
        $this->db->query('SELECT u.*, t.* FROM users u 
                         LEFT JOIN teachers t ON u.id = t.user_id 
                         WHERE u.id = :id AND u.user_type = "teacher"');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function createTeacherProfile($data){
        $this->db->query('INSERT INTO teachers (user_id, teacher_id, specialization, experience, qualification, status) 
                         VALUES (:user_id, :teacher_id, :specialization, :experience, :qualification, :status)');
        $this->db->bind(':user_id', $data['user_id']);
        $this->db->bind(':teacher_id', $data['teacher_id']);
        $this->db->bind(':specialization', $data['specialization']);
        $this->db->bind(':experience', $data['experience']);
        $this->db->bind(':qualification', $data['qualification']);
        $this->db->bind(':status', $data['status']);

        return $this->db->execute();
    }

    public function updateTeacherProfile($data){
        $this->db->query('UPDATE teachers SET 
                         specialization = :specialization,
                         experience = :experience,
                         qualification = :qualification,
                         bio = :bio,
                         website = :website,
                         linkedin = :linkedin,
                         updated_at = NOW()
                         WHERE user_id = :user_id');
        $this->db->bind(':user_id', $data['user_id']);
        $this->db->bind(':specialization', $data['specialization']);
        $this->db->bind(':experience', $data['experience']);
        $this->db->bind(':qualification', $data['qualification']);
        $this->db->bind(':bio', $data['bio']);
        $this->db->bind(':website', $data['website']);
        $this->db->bind(':linkedin', $data['linkedin']);

        return $this->db->execute();
    }

    public function getTeacherStats($teacher_id){
        $this->db->query('SELECT 
                         COUNT(DISTINCT c.id) as total_courses,
                         COUNT(DISTINCT e.student_id) as total_students,
                         COUNT(DISTINCT ex.id) as total_exams,
                         COALESCE(SUM(p.amount), 0) as total_earnings
                         FROM users u
                         LEFT JOIN courses c ON u.id = c.teacher_id
                         LEFT JOIN enrollments e ON c.id = e.course_id
                         LEFT JOIN exams ex ON c.id = ex.course_id
                         LEFT JOIN payments p ON c.id = p.course_id
                         WHERE u.id = :teacher_id');
        $this->db->bind(':teacher_id', $teacher_id);
        return $this->db->single();
    }

    public function getTeacherCourses($teacher_id){
        $this->db->query('SELECT c.*, 
                         COUNT(DISTINCT e.student_id) as enrolled_students,
                         COUNT(DISTINCT ex.id) as total_exams
                         FROM courses c
                         LEFT JOIN enrollments e ON c.id = e.course_id
                         LEFT JOIN exams ex ON c.id = ex.course_id
                         WHERE c.teacher_id = :teacher_id
                         GROUP BY c.id
                         ORDER BY c.created_at DESC');
        $this->db->bind(':teacher_id', $teacher_id);
        return $this->db->resultSet();
    }

    public function getTeacherEarnings($teacher_id, $period = 'month'){
        $dateCondition = '';
        switch($period){
            case 'week':
                $dateCondition = 'AND p.created_at >= DATE_SUB(NOW(), INTERVAL 1 WEEK)';
                break;
            case 'month':
                $dateCondition = 'AND p.created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)';
                break;
            case 'year':
                $dateCondition = 'AND p.created_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR)';
                break;
        }

        $this->db->query("SELECT 
                         DATE(p.created_at) as date,
                         SUM(p.amount) as daily_earnings,
                         COUNT(p.id) as transactions
                         FROM payments p
                         JOIN courses c ON p.course_id = c.id
                         WHERE c.teacher_id = :teacher_id 
                         AND p.payment_status = 'completed'
                         $dateCondition
                         GROUP BY DATE(p.created_at)
                         ORDER BY date DESC");
        $this->db->bind(':teacher_id', $teacher_id);
        return $this->db->resultSet();
    }

    public function getStudentsByTeacher($teacher_id){
        $this->db->query('SELECT DISTINCT u.id, u.name, u.email, e.enrolled_at, e.progress
                         FROM users u
                         JOIN enrollments e ON u.id = e.student_id
                         JOIN courses c ON e.course_id = c.id
                         WHERE c.teacher_id = :teacher_id
                         ORDER BY e.enrolled_at DESC');
        $this->db->bind(':teacher_id', $teacher_id);
        return $this->db->resultSet();
    }
}
?>
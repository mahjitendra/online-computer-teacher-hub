<?php
class Admin {
    private $db;

    public function __construct(){
        $this->db = new Database;
    }

    public function getAdminById($id){
        $this->db->query('SELECT u.*, a.* FROM users u 
                         LEFT JOIN admins a ON u.id = a.user_id 
                         WHERE u.id = :id AND u.user_type = "admin"');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function createAdminProfile($data){
        $this->db->query('INSERT INTO admins (user_id, admin_level, permissions, department) 
                         VALUES (:user_id, :admin_level, :permissions, :department)');
        $this->db->bind(':user_id', $data['user_id']);
        $this->db->bind(':admin_level', $data['admin_level']);
        $this->db->bind(':permissions', $data['permissions']);
        $this->db->bind(':department', $data['department']);

        return $this->db->execute();
    }

    public function getSystemStats(){
        $this->db->query('SELECT 
                         (SELECT COUNT(*) FROM users WHERE user_type = "student") as total_students,
                         (SELECT COUNT(*) FROM users WHERE user_type = "teacher") as total_teachers,
                         (SELECT COUNT(*) FROM courses WHERE status = "approved") as active_courses,
                         (SELECT COUNT(*) FROM jobs WHERE is_active = 1) as active_jobs,
                         (SELECT COUNT(*) FROM enrollments) as total_enrollments,
                         (SELECT COUNT(*) FROM exam_attempts) as total_exam_attempts,
                         (SELECT COUNT(*) FROM certificates) as total_certificates,
                         (SELECT COALESCE(SUM(amount), 0) FROM payments WHERE payment_status = "completed") as total_revenue');
        return $this->db->single();
    }

    public function getRecentActivity($limit = 20){
        $this->db->query('SELECT "user_registration" as type, u.name as title, u.created_at as date, u.user_type as meta
                         FROM users u
                         WHERE u.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                         UNION ALL
                         SELECT "course_creation" as type, c.title as title, c.created_at as date, "course" as meta
                         FROM courses c
                         WHERE c.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                         UNION ALL
                         SELECT "enrollment" as type, CONCAT(u.name, " enrolled in ", c.title) as title, e.enrolled_at as date, "enrollment" as meta
                         FROM enrollments e
                         JOIN users u ON e.student_id = u.id
                         JOIN courses c ON e.course_id = c.id
                         WHERE e.enrolled_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                         UNION ALL
                         SELECT "payment" as type, CONCAT("Payment of $", p.amount) as title, p.created_at as date, "payment" as meta
                         FROM payments p
                         WHERE p.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                         ORDER BY date DESC
                         LIMIT :limit');
        $this->db->bind(':limit', $limit);
        return $this->db->resultSet();
    }

    public function getUserGrowthData($period = 'month'){
        $dateFormat = '';
        $dateCondition = '';
        
        switch($period){
            case 'week':
                $dateFormat = '%Y-%m-%d';
                $dateCondition = 'WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 WEEK)';
                break;
            case 'month':
                $dateFormat = '%Y-%m-%d';
                $dateCondition = 'WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)';
                break;
            case 'year':
                $dateFormat = '%Y-%m';
                $dateCondition = 'WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR)';
                break;
        }

        $this->db->query("SELECT 
                         DATE_FORMAT(created_at, '$dateFormat') as period,
                         COUNT(*) as total_users,
                         SUM(CASE WHEN user_type = 'student' THEN 1 ELSE 0 END) as students,
                         SUM(CASE WHEN user_type = 'teacher' THEN 1 ELSE 0 END) as teachers
                         FROM users 
                         $dateCondition
                         GROUP BY DATE_FORMAT(created_at, '$dateFormat')
                         ORDER BY period");
        return $this->db->resultSet();
    }

    public function getRevenueData($period = 'month'){
        $dateFormat = '';
        $dateCondition = '';
        
        switch($period){
            case 'week':
                $dateFormat = '%Y-%m-%d';
                $dateCondition = 'WHERE p.created_at >= DATE_SUB(NOW(), INTERVAL 1 WEEK)';
                break;
            case 'month':
                $dateFormat = '%Y-%m-%d';
                $dateCondition = 'WHERE p.created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)';
                break;
            case 'year':
                $dateFormat = '%Y-%m';
                $dateCondition = 'WHERE p.created_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR)';
                break;
        }

        $this->db->query("SELECT 
                         DATE_FORMAT(p.created_at, '$dateFormat') as period,
                         SUM(p.amount) as revenue,
                         COUNT(p.id) as transactions
                         FROM payments p
                         $dateCondition
                         AND p.payment_status = 'completed'
                         GROUP BY DATE_FORMAT(p.created_at, '$dateFormat')
                         ORDER BY period");
        return $this->db->resultSet();
    }

    public function getPendingApprovals(){
        $this->db->query('SELECT 
                         "course" as type, 
                         c.id, 
                         c.title, 
                         u.name as teacher_name,
                         c.created_at
                         FROM courses c
                         JOIN users u ON c.teacher_id = u.id
                         WHERE c.status = "pending"
                         ORDER BY c.created_at ASC');
        return $this->db->resultSet();
    }
}
?>
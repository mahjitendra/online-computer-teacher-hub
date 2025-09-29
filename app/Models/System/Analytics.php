<?php
class Analytics extends BaseModel {
    protected $table = 'analytics_data';
    protected $fillable = ['metric_name', 'metric_value', 'metric_type', 'date', 'metadata'];

    public function recordMetric($name, $value, $type = 'counter', $metadata = []){
        $data = [
            'metric_name' => $name,
            'metric_value' => $value,
            'metric_type' => $type,
            'date' => date('Y-m-d'),
            'metadata' => json_encode($metadata)
        ];
        
        return $this->create($data);
    }

    public function getUserGrowthData($period = 'month'){
        $dateFormat = '';
        $dateCondition = '';
        
        switch($period){
            case 'week':
                $dateFormat = '%Y-%m-%d';
                $dateCondition = 'WHERE u.created_at >= DATE_SUB(NOW(), INTERVAL 1 WEEK)';
                break;
            case 'month':
                $dateFormat = '%Y-%m-%d';
                $dateCondition = 'WHERE u.created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)';
                break;
            case 'year':
                $dateFormat = '%Y-%m';
                $dateCondition = 'WHERE u.created_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR)';
                break;
        }

        $this->db->query("SELECT 
                         DATE_FORMAT(u.created_at, '$dateFormat') as period,
                         COUNT(*) as total_users,
                         SUM(CASE WHEN u.user_type = 'student' THEN 1 ELSE 0 END) as students,
                         SUM(CASE WHEN u.user_type = 'teacher' THEN 1 ELSE 0 END) as teachers
                         FROM users u
                         $dateCondition
                         GROUP BY DATE_FORMAT(u.created_at, '$dateFormat')
                         ORDER BY period");
        return $this->db->resultSet();
    }

    public function getCourseAnalytics($teacherId = null){
        $whereClause = $teacherId ? 'WHERE c.teacher_id = :teacher_id' : '';
        
        $this->db->query("SELECT 
                         COUNT(DISTINCT c.id) as total_courses,
                         COUNT(DISTINCT e.student_id) as total_enrollments,
                         AVG(e.progress) as average_progress,
                         COUNT(DISTINCT ex.id) as total_exams,
                         COUNT(DISTINCT ea.id) as total_exam_attempts,
                         AVG(ea.score) as average_exam_score
                         FROM courses c
                         LEFT JOIN enrollments e ON c.id = e.course_id
                         LEFT JOIN exams ex ON c.id = ex.course_id
                         LEFT JOIN exam_attempts ea ON ex.id = ea.exam_id
                         {$whereClause}");
        
        if($teacherId){
            $this->db->bind(':teacher_id', $teacherId);
        }
        
        return $this->db->single();
    }

    public function getRevenueAnalytics($period = 'month'){
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
                         COUNT(p.id) as transactions,
                         AVG(p.amount) as average_transaction
                         FROM payments p
                         {$dateCondition}
                         AND p.payment_status = 'completed'
                         GROUP BY DATE_FORMAT(p.created_at, '$dateFormat')
                         ORDER BY period");
        return $this->db->resultSet();
    }

    public function getJobAnalytics(){
        $this->db->query('SELECT 
                         COUNT(DISTINCT j.id) as total_jobs,
                         COUNT(DISTINCT ja.id) as total_applications,
                         COUNT(DISTINCT ja.student_id) as unique_applicants,
                         AVG(CASE WHEN ja.id IS NOT NULL THEN 1 ELSE 0 END) as application_rate,
                         COUNT(DISTINCT jc.id) as job_categories
                         FROM jobs j
                         LEFT JOIN job_applications ja ON j.id = ja.job_id
                         LEFT JOIN job_categories jc ON j.category_id = jc.id
                         WHERE j.is_active = 1');
        return $this->db->single();
    }

    public function getEngagementMetrics($userId = null, $period = 'month'){
        $userCondition = $userId ? 'AND user_id = :user_id' : '';
        $dateCondition = '';
        
        switch($period){
            case 'week':
                $dateCondition = 'WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 WEEK)';
                break;
            case 'month':
                $dateCondition = 'WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)';
                break;
            case 'year':
                $dateCondition = 'WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR)';
                break;
        }

        // Course engagement
        $this->db->query("SELECT 
                         'course_views' as metric,
                         COUNT(*) as value
                         FROM course_views cv
                         {$dateCondition} {$userCondition}
                         UNION ALL
                         SELECT 
                         'tutorial_completions' as metric,
                         COUNT(*) as value
                         FROM tutorial_progress tp
                         WHERE tp.progress_percentage = 100 {$userCondition}
                         UNION ALL
                         SELECT 
                         'exam_attempts' as metric,
                         COUNT(*) as value
                         FROM exam_attempts ea
                         {$dateCondition} {$userCondition}");
        
        if($userId){
            $this->db->bind(':user_id', $userId);
        }
        
        return $this->db->resultSet();
    }

    public function getPopularCourses($limit = 10){
        $this->db->query('SELECT 
                         c.id,
                         c.title,
                         COUNT(DISTINCT e.student_id) as enrollment_count,
                         AVG(e.progress) as average_progress,
                         COUNT(DISTINCT ex.id) as exam_count,
                         AVG(ea.score) as average_score
                         FROM courses c
                         LEFT JOIN enrollments e ON c.id = e.course_id
                         LEFT JOIN exams ex ON c.id = ex.course_id
                         LEFT JOIN exam_attempts ea ON ex.id = ea.exam_id
                         WHERE c.status = "approved"
                         GROUP BY c.id
                         ORDER BY enrollment_count DESC
                         LIMIT :limit');
        $this->db->bind(':limit', $limit);
        return $this->db->resultSet();
    }

    public function getTopPerformingStudents($limit = 10){
        $this->db->query('SELECT 
                         u.id,
                         u.name,
                         COUNT(DISTINCT e.course_id) as courses_enrolled,
                         AVG(e.progress) as average_progress,
                         COUNT(DISTINCT ea.id) as exams_taken,
                         AVG(ea.score) as average_score,
                         COUNT(DISTINCT c.id) as certificates_earned
                         FROM users u
                         LEFT JOIN enrollments e ON u.id = e.student_id
                         LEFT JOIN exam_attempts ea ON u.id = ea.student_id
                         LEFT JOIN certificates c ON u.id = c.student_id
                         WHERE u.user_type = "student"
                         GROUP BY u.id
                         HAVING average_score IS NOT NULL
                         ORDER BY average_score DESC, certificates_earned DESC
                         LIMIT :limit');
        $this->db->bind(':limit', $limit);
        return $this->db->resultSet();
    }

    public function getSystemUsageStats(){
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

    public function getActivityHeatmap($userId = null, $days = 30){
        $userCondition = $userId ? 'AND user_id = :user_id' : '';
        
        $this->db->query("SELECT 
                         DATE(created_at) as date,
                         HOUR(created_at) as hour,
                         COUNT(*) as activity_count
                         FROM (
                             SELECT created_at, user_id FROM course_views WHERE created_at >= DATE_SUB(NOW(), INTERVAL :days DAY) {$userCondition}
                             UNION ALL
                             SELECT created_at, student_id as user_id FROM exam_attempts WHERE created_at >= DATE_SUB(NOW(), INTERVAL :days DAY) {$userCondition}
                             UNION ALL
                             SELECT enrolled_at as created_at, student_id as user_id FROM enrollments WHERE enrolled_at >= DATE_SUB(NOW(), INTERVAL :days DAY) {$userCondition}
                         ) as activities
                         GROUP BY DATE(created_at), HOUR(created_at)
                         ORDER BY date, hour");
        
        $this->db->bind(':days', $days);
        if($userId){
            $this->db->bind(':user_id', $userId);
        }
        
        return $this->db->resultSet();
    }

    public function getConversionFunnelData(){
        $this->db->query('SELECT 
                         "visitors" as stage,
                         COUNT(DISTINCT ip_address) as count
                         FROM page_views
                         WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)
                         UNION ALL
                         SELECT 
                         "registrations" as stage,
                         COUNT(*) as count
                         FROM users
                         WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)
                         UNION ALL
                         SELECT 
                         "course_views" as stage,
                         COUNT(DISTINCT user_id) as count
                         FROM course_views
                         WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)
                         UNION ALL
                         SELECT 
                         "enrollments" as stage,
                         COUNT(DISTINCT student_id) as count
                         FROM enrollments
                         WHERE enrolled_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)
                         UNION ALL
                         SELECT 
                         "payments" as stage,
                         COUNT(DISTINCT student_id) as count
                         FROM payments
                         WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)
                         AND payment_status = "completed"');
        return $this->db->resultSet();
    }

    public function getRetentionData($cohortMonth){
        $this->db->query('SELECT 
                         DATEDIFF(l.login_date, u.created_at) DIV 30 as months_after_signup,
                         COUNT(DISTINCT u.id) as active_users,
                         COUNT(DISTINCT signup_cohort.total_users) as cohort_size
                         FROM users u
                         JOIN (
                             SELECT COUNT(*) as total_users
                             FROM users
                             WHERE DATE_FORMAT(created_at, "%Y-%m") = :cohort_month
                         ) as signup_cohort
                         LEFT JOIN user_logins l ON u.id = l.user_id
                         WHERE DATE_FORMAT(u.created_at, "%Y-%m") = :cohort_month
                         AND l.login_date IS NOT NULL
                         GROUP BY months_after_signup
                         ORDER BY months_after_signup');
        $this->db->bind(':cohort_month', $cohortMonth);
        return $this->db->resultSet();
    }

    public function exportAnalyticsData($startDate, $endDate, $metrics = []){
        $data = [
            'period' => ['start' => $startDate, 'end' => $endDate],
            'generated_at' => date('Y-m-d H:i:s'),
            'metrics' => []
        ];
        
        if(empty($metrics) || in_array('users', $metrics)){
            $data['metrics']['user_growth'] = $this->getUserGrowthData('month');
        }
        
        if(empty($metrics) || in_array('courses', $metrics)){
            $data['metrics']['course_analytics'] = $this->getCourseAnalytics();
        }
        
        if(empty($metrics) || in_array('revenue', $metrics)){
            $data['metrics']['revenue_analytics'] = $this->getRevenueAnalytics('month');
        }
        
        if(empty($metrics) || in_array('engagement', $metrics)){
            $data['metrics']['engagement'] = $this->getEngagementMetrics(null, 'month');
        }
        
        return json_encode($data, JSON_PRETTY_PRINT);
    }
}
?>
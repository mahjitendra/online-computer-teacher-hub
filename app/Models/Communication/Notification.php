<?php
class Notification extends BaseModel {
    protected $table = 'notifications';
    protected $fillable = ['user_id', 'type', 'title', 'message', 'data', 'read_at'];

    public function createNotification($data){
        $data['data'] = is_array($data['data']) ? json_encode($data['data']) : $data['data'];
        return $this->create($data);
    }

    public function getNotificationsByUser($userId, $limit = 50){
        $this->db->query('SELECT * FROM notifications 
                         WHERE user_id = :user_id 
                         ORDER BY created_at DESC 
                         LIMIT :limit');
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':limit', $limit);
        return $this->db->resultSet();
    }

    public function getUnreadNotifications($userId){
        $this->db->query('SELECT * FROM notifications 
                         WHERE user_id = :user_id AND read_at IS NULL 
                         ORDER BY created_at DESC');
        $this->db->bind(':user_id', $userId);
        return $this->db->resultSet();
    }

    public function getUnreadCount($userId){
        $this->db->query('SELECT COUNT(*) as count FROM notifications 
                         WHERE user_id = :user_id AND read_at IS NULL');
        $this->db->bind(':user_id', $userId);
        $result = $this->db->single();
        return $result ? $result->count : 0;
    }

    public function markAsRead($notificationId){
        return $this->update($notificationId, ['read_at' => date('Y-m-d H:i:s')]);
    }

    public function markAllAsRead($userId){
        $this->db->query('UPDATE notifications SET read_at = NOW() 
                         WHERE user_id = :user_id AND read_at IS NULL');
        $this->db->bind(':user_id', $userId);
        return $this->db->execute();
    }

    public function deleteNotification($notificationId){
        return $this->delete($notificationId);
    }

    public function deleteOldNotifications($days = 30){
        $this->db->query('DELETE FROM notifications 
                         WHERE created_at < DATE_SUB(NOW(), INTERVAL :days DAY)');
        $this->db->bind(':days', $days);
        return $this->db->execute();
    }

    public function sendToUser($userId, $type, $title, $message, $data = []){
        return $this->createNotification([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data
        ]);
    }

    public function sendToMultipleUsers($userIds, $type, $title, $message, $data = []){
        $sent = 0;
        foreach($userIds as $userId){
            if($this->sendToUser($userId, $type, $title, $message, $data)){
                $sent++;
            }
        }
        return $sent;
    }

    public function sendToAllUsers($type, $title, $message, $data = []){
        $this->db->query('SELECT id FROM users WHERE user_type != "admin"');
        $users = $this->db->resultSet();
        
        $userIds = array_map(function($user) { return $user->id; }, $users);
        return $this->sendToMultipleUsers($userIds, $type, $title, $message, $data);
    }

    public function getNotificationsByType($type, $limit = 100){
        $this->db->query('SELECT n.*, u.name as user_name 
                         FROM notifications n
                         JOIN users u ON n.user_id = u.id
                         WHERE n.type = :type 
                         ORDER BY n.created_at DESC 
                         LIMIT :limit');
        $this->db->bind(':type', $type);
        $this->db->bind(':limit', $limit);
        return $this->db->resultSet();
    }

    public function getNotificationStats($userId = null){
        $whereClause = $userId ? 'WHERE user_id = :user_id' : '';
        
        $this->db->query("SELECT 
                         COUNT(*) as total_notifications,
                         SUM(CASE WHEN read_at IS NULL THEN 1 ELSE 0 END) as unread_count,
                         SUM(CASE WHEN type = 'info' THEN 1 ELSE 0 END) as info_count,
                         SUM(CASE WHEN type = 'success' THEN 1 ELSE 0 END) as success_count,
                         SUM(CASE WHEN type = 'warning' THEN 1 ELSE 0 END) as warning_count,
                         SUM(CASE WHEN type = 'error' THEN 1 ELSE 0 END) as error_count
                         FROM notifications {$whereClause}");
        
        if($userId){
            $this->db->bind(':user_id', $userId);
        }
        
        return $this->db->single();
    }

    // Predefined notification types and templates
    public function sendCourseEnrollmentNotification($userId, $courseTitle){
        return $this->sendToUser(
            $userId,
            'success',
            'Course Enrollment Successful',
            "You have successfully enrolled in '{$courseTitle}'. Start learning now!",
            ['type' => 'course_enrollment', 'course_title' => $courseTitle]
        );
    }

    public function sendExamResultNotification($userId, $examTitle, $score, $passed){
        $type = $passed ? 'success' : 'warning';
        $message = $passed 
            ? "Congratulations! You passed '{$examTitle}' with a score of {$score}%."
            : "You scored {$score}% in '{$examTitle}'. Keep practicing and try again!";
            
        return $this->sendToUser(
            $userId,
            $type,
            'Exam Result Available',
            $message,
            ['type' => 'exam_result', 'exam_title' => $examTitle, 'score' => $score, 'passed' => $passed]
        );
    }

    public function sendCertificateIssuedNotification($userId, $courseTitle, $certificateCode){
        return $this->sendToUser(
            $userId,
            'success',
            'Certificate Issued',
            "Your certificate for '{$courseTitle}' has been issued. Certificate ID: {$certificateCode}",
            ['type' => 'certificate_issued', 'course_title' => $courseTitle, 'certificate_code' => $certificateCode]
        );
    }

    public function sendJobApplicationNotification($userId, $jobTitle, $company){
        return $this->sendToUser(
            $userId,
            'info',
            'Job Application Submitted',
            "Your application for '{$jobTitle}' at {$company} has been submitted successfully.",
            ['type' => 'job_application', 'job_title' => $jobTitle, 'company' => $company]
        );
    }

    public function sendPaymentConfirmationNotification($userId, $amount, $courseTitle){
        return $this->sendToUser(
            $userId,
            'success',
            'Payment Confirmed',
            "Your payment of \${$amount} for '{$courseTitle}' has been processed successfully.",
            ['type' => 'payment_confirmation', 'amount' => $amount, 'course_title' => $courseTitle]
        );
    }
}
?>
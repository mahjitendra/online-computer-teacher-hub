<?php
class Tutorial {
    private $db;

    public function __construct(){
        $this->db = new Database;
    }

    public function getTutorialsByCourse($course_id){
        $this->db->query('SELECT t.*, cm.title as module_title 
                         FROM tutorials t
                         LEFT JOIN course_modules cm ON t.module_id = cm.id
                         WHERE t.course_id = :course_id 
                         ORDER BY t.order_index ASC');
        $this->db->bind(':course_id', $course_id);
        return $this->db->resultSet();
    }

    public function getTutorialById($id){
        $this->db->query('SELECT t.*, cm.title as module_title, c.title as course_title
                         FROM tutorials t
                         LEFT JOIN course_modules cm ON t.module_id = cm.id
                         LEFT JOIN courses c ON t.course_id = c.id
                         WHERE t.id = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function createTutorial($data){
        $this->db->query('INSERT INTO tutorials (
                         course_id, module_id, title, description, video_url, 
                         video_duration, order_index, is_free, content_type
                         ) VALUES (
                         :course_id, :module_id, :title, :description, :video_url,
                         :video_duration, :order_index, :is_free, :content_type
                         )');
        
        $this->db->bind(':course_id', $data['course_id']);
        $this->db->bind(':module_id', $data['module_id']);
        $this->db->bind(':title', $data['title']);
        $this->db->bind(':description', $data['description']);
        $this->db->bind(':video_url', $data['video_url']);
        $this->db->bind(':video_duration', $data['video_duration']);
        $this->db->bind(':order_index', $data['order_index']);
        $this->db->bind(':is_free', $data['is_free']);
        $this->db->bind(':content_type', $data['content_type']);

        if($this->db->execute()){
            return $this->db->lastInsertId();
        }
        return false;
    }

    public function updateTutorial($data){
        $this->db->query('UPDATE tutorials SET 
                         title = :title,
                         description = :description,
                         video_url = :video_url,
                         video_duration = :video_duration,
                         order_index = :order_index,
                         is_free = :is_free,
                         content_type = :content_type,
                         updated_at = NOW()
                         WHERE id = :id');
        
        $this->db->bind(':id', $data['id']);
        $this->db->bind(':title', $data['title']);
        $this->db->bind(':description', $data['description']);
        $this->db->bind(':video_url', $data['video_url']);
        $this->db->bind(':video_duration', $data['video_duration']);
        $this->db->bind(':order_index', $data['order_index']);
        $this->db->bind(':is_free', $data['is_free']);
        $this->db->bind(':content_type', $data['content_type']);

        return $this->db->execute();
    }

    public function deleteTutorial($id){
        $this->db->query('DELETE FROM tutorials WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function getMaterialsByTutorial($tutorial_id){
        $this->db->query('SELECT * FROM course_materials 
                         WHERE tutorial_id = :tutorial_id 
                         ORDER BY created_at ASC');
        $this->db->bind(':tutorial_id', $tutorial_id);
        return $this->db->resultSet();
    }

    public function markTutorialComplete($student_id, $tutorial_id){
        // Check if already completed
        $this->db->query('SELECT id FROM tutorial_progress 
                         WHERE student_id = :student_id AND tutorial_id = :tutorial_id');
        $this->db->bind(':student_id', $student_id);
        $this->db->bind(':tutorial_id', $tutorial_id);
        
        if($this->db->single()){
            // Update completion time
            $this->db->query('UPDATE tutorial_progress SET 
                             completed_at = NOW(), 
                             progress_percentage = 100
                             WHERE student_id = :student_id AND tutorial_id = :tutorial_id');
        } else {
            // Insert new completion record
            $this->db->query('INSERT INTO tutorial_progress (
                             student_id, tutorial_id, progress_percentage, completed_at
                             ) VALUES (
                             :student_id, :tutorial_id, 100, NOW()
                             )');
        }
        
        $this->db->bind(':student_id', $student_id);
        $this->db->bind(':tutorial_id', $tutorial_id);
        return $this->db->execute();
    }

    public function updateTutorialProgress($student_id, $tutorial_id, $progress_percentage){
        $this->db->query('INSERT INTO tutorial_progress (
                         student_id, tutorial_id, progress_percentage, last_position
                         ) VALUES (
                         :student_id, :tutorial_id, :progress_percentage, :last_position
                         ) ON DUPLICATE KEY UPDATE
                         progress_percentage = :progress_percentage,
                         last_position = :last_position,
                         updated_at = NOW()');
        
        $this->db->bind(':student_id', $student_id);
        $this->db->bind(':tutorial_id', $tutorial_id);
        $this->db->bind(':progress_percentage', $progress_percentage);
        $this->db->bind(':last_position', $progress_percentage);
        
        return $this->db->execute();
    }

    public function getTutorialProgress($student_id, $tutorial_id){
        $this->db->query('SELECT * FROM tutorial_progress 
                         WHERE student_id = :student_id AND tutorial_id = :tutorial_id');
        $this->db->bind(':student_id', $student_id);
        $this->db->bind(':tutorial_id', $tutorial_id);
        return $this->db->single();
    }

    public function getNextTutorial($course_id, $current_order){
        $this->db->query('SELECT * FROM tutorials 
                         WHERE course_id = :course_id AND order_index > :current_order
                         ORDER BY order_index ASC
                         LIMIT 1');
        $this->db->bind(':course_id', $course_id);
        $this->db->bind(':current_order', $current_order);
        return $this->db->single();
    }

    public function getPreviousTutorial($course_id, $current_order){
        $this->db->query('SELECT * FROM tutorials 
                         WHERE course_id = :course_id AND order_index < :current_order
                         ORDER BY order_index DESC
                         LIMIT 1');
        $this->db->bind(':course_id', $course_id);
        $this->db->bind(':current_order', $current_order);
        return $this->db->single();
    }
}
?>
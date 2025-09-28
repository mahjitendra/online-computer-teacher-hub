<?php
class CourseModule {
    private $db;

    public function __construct(){
        $this->db = new Database;
    }

    public function getModulesByCourse($course_id){
        $this->db->query('SELECT * FROM course_modules 
                         WHERE course_id = :course_id 
                         ORDER BY order_index ASC');
        $this->db->bind(':course_id', $course_id);
        return $this->db->resultSet();
    }

    public function getModuleById($id){
        $this->db->query('SELECT * FROM course_modules WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function createModule($data){
        $this->db->query('INSERT INTO course_modules (
                         course_id, title, description, order_index, duration, is_free
                         ) VALUES (
                         :course_id, :title, :description, :order_index, :duration, :is_free
                         )');
        
        $this->db->bind(':course_id', $data['course_id']);
        $this->db->bind(':title', $data['title']);
        $this->db->bind(':description', $data['description']);
        $this->db->bind(':order_index', $data['order_index']);
        $this->db->bind(':duration', $data['duration']);
        $this->db->bind(':is_free', $data['is_free']);

        if($this->db->execute()){
            return $this->db->lastInsertId();
        }
        return false;
    }

    public function updateModule($data){
        $this->db->query('UPDATE course_modules SET 
                         title = :title,
                         description = :description,
                         order_index = :order_index,
                         duration = :duration,
                         is_free = :is_free,
                         updated_at = NOW()
                         WHERE id = :id');
        
        $this->db->bind(':id', $data['id']);
        $this->db->bind(':title', $data['title']);
        $this->db->bind(':description', $data['description']);
        $this->db->bind(':order_index', $data['order_index']);
        $this->db->bind(':duration', $data['duration']);
        $this->db->bind(':is_free', $data['is_free']);

        return $this->db->execute();
    }

    public function deleteModule($id){
        $this->db->query('DELETE FROM course_modules WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function getModuleWithTutorials($id){
        $module = $this->getModuleById($id);
        if($module){
            $this->db->query('SELECT * FROM tutorials 
                             WHERE module_id = :module_id 
                             ORDER BY order_index ASC');
            $this->db->bind(':module_id', $id);
            $module->tutorials = $this->db->resultSet();
        }
        return $module;
    }

    public function reorderModules($course_id, $module_orders){
        foreach($module_orders as $module_id => $order){
            $this->db->query('UPDATE course_modules SET order_index = :order WHERE id = :id AND course_id = :course_id');
            $this->db->bind(':id', $module_id);
            $this->db->bind(':order', $order);
            $this->db->bind(':course_id', $course_id);
            $this->db->execute();
        }
        return true;
    }

    public function getModuleProgress($student_id, $module_id){
        $this->db->query('SELECT 
                         COUNT(t.id) as total_tutorials,
                         COUNT(tp.tutorial_id) as completed_tutorials,
                         CASE 
                             WHEN COUNT(t.id) = 0 THEN 0
                             ELSE (COUNT(tp.tutorial_id) / COUNT(t.id)) * 100
                         END as progress_percentage
                         FROM course_modules cm
                         LEFT JOIN tutorials t ON cm.id = t.module_id
                         LEFT JOIN tutorial_progress tp ON t.id = tp.tutorial_id AND tp.student_id = :student_id
                         WHERE cm.id = :module_id');
        
        $this->db->bind(':student_id', $student_id);
        $this->db->bind(':module_id', $module_id);
        return $this->db->single();
    }
}
?>
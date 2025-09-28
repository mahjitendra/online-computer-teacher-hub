<?php
class Course {
    private $db;

    public function __construct(){
        $this->db = new Database;
    }

    public function getAllCourses(){
        // We only want to show approved courses to students
        $this->db->query("SELECT * FROM courses WHERE status = 'approved' ORDER BY created_at DESC");
        return $this->db->resultSet();
    }

    public function getCoursesByTeacher($teacher_id){
        $this->db->query('SELECT * FROM courses WHERE teacher_id = :teacher_id ORDER BY created_at DESC');
        $this->db->bind(':teacher_id', $teacher_id);
        return $this->db->resultSet();
    }

    public function addCourse($data){
        $this->db->query('INSERT INTO courses (teacher_id, category_id, title, description, price) VALUES (:teacher_id, :category_id, :title, :description, :price)');
        // Bind values
        $this->db->bind(':teacher_id', $data['teacher_id']);
        $this->db->bind(':category_id', $data['category_id']);
        $this->db->bind(':title', $data['title']);
        $this->db->bind(':description', $data['description']);
        $this->db->bind(':price', $data['price']);

        // Execute
        if($this->db->execute()){
            return true;
        } else {
            return false;
        }
    }

    public function getCourseById($id){
        $this->db->query('SELECT * FROM courses WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function updateCourse($data){
        $this->db->query('UPDATE courses SET category_id = :category_id, title = :title, description = :description, price = :price WHERE id = :id');
        // Bind values
        $this->db->bind(':id', $data['id']);
        $this->db->bind(':category_id', $data['category_id']);
        $this->db->bind(':title', $data['title']);
        $this->db->bind(':description', $data['description']);
        $this->db-bind(':price', $data['price']);

        // Execute
        if($this->db->execute()){
            return true;
        } else {
            return false;
        }
    }

    public function deleteCourse($id){
        $this->db->query('DELETE FROM courses WHERE id = :id');
        // Bind values
        $this->db->bind(':id', $id);

        // Execute
        if($this->db->execute()){
            return true;
        } else {
            return false;
        }
    }

    // This would be expanded with other models for categories, etc.
    public function getCategories(){
        $this->db->query('SELECT * FROM course_categories ORDER BY name ASC');
        return $this->db->resultSet();
    }
}
?>
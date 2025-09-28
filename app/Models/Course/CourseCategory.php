<?php
class CourseCategory {
    private $db;

    public function __construct(){
        $this->db = new Database;
    }

    public function getAllCategories(){
        $this->db->query('SELECT * FROM course_categories ORDER BY name ASC');
        return $this->db->resultSet();
    }

    public function getCategoryById($id){
        $this->db->query('SELECT * FROM course_categories WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function createCategory($data){
        $this->db->query('INSERT INTO course_categories (name, description, icon, color) 
                         VALUES (:name, :description, :icon, :color)');
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':description', $data['description']);
        $this->db->bind(':icon', $data['icon']);
        $this->db->bind(':color', $data['color']);

        return $this->db->execute();
    }

    public function updateCategory($data){
        $this->db->query('UPDATE course_categories SET 
                         name = :name,
                         description = :description,
                         icon = :icon,
                         color = :color,
                         updated_at = NOW()
                         WHERE id = :id');
        
        $this->db->bind(':id', $data['id']);
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':description', $data['description']);
        $this->db->bind(':icon', $data['icon']);
        $this->db->bind(':color', $data['color']);

        return $this->db->execute();
    }

    public function deleteCategory($id){
        $this->db->query('DELETE FROM course_categories WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function getCategoriesWithCourseCount(){
        $this->db->query('SELECT cc.*, COUNT(c.id) as course_count
                         FROM course_categories cc
                         LEFT JOIN courses c ON cc.id = c.category_id AND c.status = "approved"
                         GROUP BY cc.id
                         ORDER BY cc.name ASC');
        return $this->db->resultSet();
    }

    public function getPopularCategories($limit = 10){
        $this->db->query('SELECT cc.*, COUNT(c.id) as course_count
                         FROM course_categories cc
                         JOIN courses c ON cc.id = c.category_id
                         WHERE c.status = "approved"
                         GROUP BY cc.id
                         ORDER BY course_count DESC
                         LIMIT :limit');
        $this->db->bind(':limit', $limit);
        return $this->db->resultSet();
    }

    public function searchCategories($search){
        $this->db->query('SELECT * FROM course_categories 
                         WHERE name LIKE :search OR description LIKE :search
                         ORDER BY name ASC');
        $this->db->bind(':search', '%' . $search . '%');
        return $this->db->resultSet();
    }
}
?>
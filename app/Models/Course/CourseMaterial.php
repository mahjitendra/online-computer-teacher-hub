<?php
class CourseMaterial {
    private $db;

    public function __construct(){
        $this->db = new Database;
    }

    public function getMaterialsByTutorial($tutorial_id){
        $this->db->query('SELECT * FROM course_materials 
                         WHERE tutorial_id = :tutorial_id 
                         ORDER BY created_at ASC');
        $this->db->bind(':tutorial_id', $tutorial_id);
        return $this->db->resultSet();
    }

    public function getMaterialById($id){
        $this->db->query('SELECT * FROM course_materials WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function createMaterial($data){
        $this->db->query('INSERT INTO course_materials (
                         tutorial_id, title, description, file_path, file_type,
                         file_size, download_count, is_downloadable
                         ) VALUES (
                         :tutorial_id, :title, :description, :file_path, :file_type,
                         :file_size, :download_count, :is_downloadable
                         )');
        
        $this->db->bind(':tutorial_id', $data['tutorial_id']);
        $this->db->bind(':title', $data['title']);
        $this->db->bind(':description', $data['description']);
        $this->db->bind(':file_path', $data['file_path']);
        $this->db->bind(':file_type', $data['file_type']);
        $this->db->bind(':file_size', $data['file_size']);
        $this->db->bind(':download_count', 0);
        $this->db->bind(':is_downloadable', $data['is_downloadable']);

        if($this->db->execute()){
            return $this->db->lastInsertId();
        }
        return false;
    }

    public function updateMaterial($data){
        $this->db->query('UPDATE course_materials SET 
                         title = :title,
                         description = :description,
                         file_path = :file_path,
                         file_type = :file_type,
                         file_size = :file_size,
                         is_downloadable = :is_downloadable,
                         updated_at = NOW()
                         WHERE id = :id');
        
        $this->db->bind(':id', $data['id']);
        $this->db->bind(':title', $data['title']);
        $this->db->bind(':description', $data['description']);
        $this->db->bind(':file_path', $data['file_path']);
        $this->db->bind(':file_type', $data['file_type']);
        $this->db->bind(':file_size', $data['file_size']);
        $this->db->bind(':is_downloadable', $data['is_downloadable']);

        return $this->db->execute();
    }

    public function deleteMaterial($id){
        $this->db->query('DELETE FROM course_materials WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function incrementDownloadCount($id){
        $this->db->query('UPDATE course_materials SET 
                         download_count = download_count + 1,
                         last_downloaded = NOW()
                         WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function getMaterialsByType($tutorial_id, $file_type){
        $this->db->query('SELECT * FROM course_materials 
                         WHERE tutorial_id = :tutorial_id AND file_type = :file_type
                         ORDER BY created_at ASC');
        $this->db->bind(':tutorial_id', $tutorial_id);
        $this->db->bind(':file_type', $file_type);
        return $this->db->resultSet();
    }

    public function getMaterialsByCourse($course_id){
        $this->db->query('SELECT cm.*, t.title as tutorial_title
                         FROM course_materials cm
                         JOIN tutorials t ON cm.tutorial_id = t.id
                         WHERE t.course_id = :course_id
                         ORDER BY t.order_index ASC, cm.created_at ASC');
        $this->db->bind(':course_id', $course_id);
        return $this->db->resultSet();
    }

    public function getPopularMaterials($limit = 10){
        $this->db->query('SELECT cm.*, t.title as tutorial_title, c.title as course_title
                         FROM course_materials cm
                         JOIN tutorials t ON cm.tutorial_id = t.id
                         JOIN courses c ON t.course_id = c.id
                         ORDER BY cm.download_count DESC
                         LIMIT :limit');
        $this->db->bind(':limit', $limit);
        return $this->db->resultSet();
    }

    public function searchMaterials($search, $course_id = null){
        $query = 'SELECT cm.*, t.title as tutorial_title
                  FROM course_materials cm
                  JOIN tutorials t ON cm.tutorial_id = t.id
                  WHERE (cm.title LIKE :search OR cm.description LIKE :search)';
        
        if($course_id){
            $query .= ' AND t.course_id = :course_id';
        }
        
        $query .= ' ORDER BY cm.created_at DESC';
        
        $this->db->query($query);
        $this->db->bind(':search', '%' . $search . '%');
        
        if($course_id){
            $this->db->bind(':course_id', $course_id);
        }
        
        return $this->db->resultSet();
    }

    public function getMaterialAnalytics($material_id){
        $this->db->query('SELECT 
                         download_count,
                         last_downloaded,
                         created_at,
                         file_size,
                         file_type
                         FROM course_materials
                         WHERE id = :material_id');
        $this->db->bind(':material_id', $material_id);
        return $this->db->single();
    }
}
?>
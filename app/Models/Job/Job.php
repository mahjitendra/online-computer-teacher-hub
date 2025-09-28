<?php
class Job {
    private $db;

    public function __construct(){
        $this->db = new Database;
    }

    public function getAllActiveJobs(){
        $this->db->query("SELECT j.*, c.name as category_name FROM jobs j
                          JOIN job_categories c ON j.category_id = c.id
                          WHERE j.is_active = 1
                          ORDER BY j.created_at DESC");
        return $this->db->resultSet();
    }

    public function getJobById($id){
        $this->db->query("SELECT j.*, c.name as category_name FROM jobs j
                          JOIN job_categories c ON j.category_id = c.id
                          WHERE j.id = :id");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function getCategories(){
        $this->db->query('SELECT * FROM job_categories ORDER BY name ASC');
        return $this->db->resultSet();
    }

    public function applyForJob($data){
        $this->db->query('INSERT INTO job_applications (student_id, job_id, resume_path, cover_letter) VALUES (:student_id, :job_id, :resume_path, :cover_letter)');
        $this->db->bind(':student_id', $data['student_id']);
        $this->db->bind(':job_id', $data['job_id']);
        $this->db->bind(':resume_path', $data['resume_path']);
        $this->db->bind(':cover_letter', $data['cover_letter']);

        if($this->db->execute()){
            return true;
        } else {
            return false;
        }
    }
}
?>
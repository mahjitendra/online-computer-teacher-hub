<?php
class JobCategory extends BaseModel {
    protected $table = 'job_categories';
    protected $fillable = ['name', 'description', 'icon', 'color', 'parent_id'];

    public function getAllCategories(){
        $this->db->query('SELECT * FROM job_categories ORDER BY name ASC');
        return $this->db->resultSet();
    }

    public function getCategoriesWithJobCount(){
        $this->db->query('SELECT jc.*, COUNT(j.id) as job_count
                         FROM job_categories jc
                         LEFT JOIN jobs j ON jc.id = j.category_id AND j.is_active = 1
                         GROUP BY jc.id
                         ORDER BY jc.name ASC');
        return $this->db->resultSet();
    }

    public function getPopularCategories($limit = 10){
        $this->db->query('SELECT jc.*, COUNT(j.id) as job_count
                         FROM job_categories jc
                         JOIN jobs j ON jc.id = j.category_id
                         WHERE j.is_active = 1
                         GROUP BY jc.id
                         ORDER BY job_count DESC
                         LIMIT :limit');
        $this->db->bind(':limit', $limit);
        return $this->db->resultSet();
    }

    public function getSubcategories($parentId){
        $this->db->query('SELECT * FROM job_categories WHERE parent_id = :parent_id ORDER BY name ASC');
        $this->db->bind(':parent_id', $parentId);
        return $this->db->resultSet();
    }

    public function getCategoryHierarchy(){
        $this->db->query('SELECT jc.*, parent.name as parent_name
                         FROM job_categories jc
                         LEFT JOIN job_categories parent ON jc.parent_id = parent.id
                         ORDER BY COALESCE(jc.parent_id, jc.id), jc.name');
        return $this->db->resultSet();
    }
}
?>
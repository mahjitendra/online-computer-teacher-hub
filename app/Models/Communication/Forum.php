<?php
class Forum extends BaseModel {
    protected $table = 'forums';
    protected $fillable = ['name', 'description', 'category', 'is_active', 'order_index'];

    public function getAllForums(){
        $this->db->query('SELECT f.*, 
                         COUNT(DISTINCT fp.id) as post_count,
                         COUNT(DISTINCT fr.id) as reply_count,
                         MAX(COALESCE(fr.created_at, fp.created_at)) as last_activity
                         FROM forums f
                         LEFT JOIN forum_posts fp ON f.id = fp.forum_id
                         LEFT JOIN forum_replies fr ON fp.id = fr.post_id
                         WHERE f.is_active = 1
                         GROUP BY f.id
                         ORDER BY f.order_index ASC, f.name ASC');
        return $this->db->resultSet();
    }

    public function getForumsByCategory($category){
        $this->db->query('SELECT f.*, 
                         COUNT(DISTINCT fp.id) as post_count,
                         COUNT(DISTINCT fr.id) as reply_count
                         FROM forums f
                         LEFT JOIN forum_posts fp ON f.id = fp.forum_id
                         LEFT JOIN forum_replies fr ON fp.id = fr.post_id
                         WHERE f.category = :category AND f.is_active = 1
                         GROUP BY f.id
                         ORDER BY f.order_index ASC');
        $this->db->bind(':category', $category);
        return $this->db->resultSet();
    }

    public function getForumStats($forumId){
        $this->db->query('SELECT 
                         COUNT(DISTINCT fp.id) as total_posts,
                         COUNT(DISTINCT fr.id) as total_replies,
                         COUNT(DISTINCT fp.user_id) as unique_posters,
                         MAX(COALESCE(fr.created_at, fp.created_at)) as last_activity
                         FROM forum_posts fp
                         LEFT JOIN forum_replies fr ON fp.id = fr.post_id
                         WHERE fp.forum_id = :forum_id');
        $this->db->bind(':forum_id', $forumId);
        return $this->db->single();
    }

    public function getPopularForums($limit = 10){
        $this->db->query('SELECT f.*, 
                         COUNT(DISTINCT fp.id) as post_count,
                         COUNT(DISTINCT fr.id) as reply_count,
                         (COUNT(DISTINCT fp.id) + COUNT(DISTINCT fr.id)) as activity_score
                         FROM forums f
                         LEFT JOIN forum_posts fp ON f.id = fp.forum_id
                         LEFT JOIN forum_replies fr ON fp.id = fr.post_id
                         WHERE f.is_active = 1
                         GROUP BY f.id
                         ORDER BY activity_score DESC
                         LIMIT :limit');
        $this->db->bind(':limit', $limit);
        return $this->db->resultSet();
    }

    public function searchForums($searchTerm){
        $this->db->query('SELECT * FROM forums 
                         WHERE (name LIKE :search OR description LIKE :search) 
                         AND is_active = 1
                         ORDER BY name ASC');
        $this->db->bind(':search', '%' . $searchTerm . '%');
        return $this->db->resultSet();
    }

    public function getForumCategories(){
        $this->db->query('SELECT DISTINCT category FROM forums WHERE is_active = 1 ORDER BY category');
        return $this->db->resultSet();
    }

    public function createForum($data){
        $data['is_active'] = 1;
        $data['order_index'] = $this->getNextOrderIndex();
        return $this->create($data);
    }

    private function getNextOrderIndex(){
        $this->db->query('SELECT MAX(order_index) as max_order FROM forums');
        $result = $this->db->single();
        return ($result && $result->max_order) ? $result->max_order + 1 : 1;
    }

    public function updateForumOrder($forumId, $newOrder){
        return $this->update($forumId, ['order_index' => $newOrder]);
    }

    public function getRecentActivity($forumId, $limit = 10){
        $this->db->query('SELECT 
                         "post" as activity_type,
                         fp.id as activity_id,
                         fp.title as activity_title,
                         fp.created_at as activity_date,
                         u.name as user_name
                         FROM forum_posts fp
                         JOIN users u ON fp.user_id = u.id
                         WHERE fp.forum_id = :forum_id
                         UNION ALL
                         SELECT 
                         "reply" as activity_type,
                         fr.id as activity_id,
                         CONCAT("Reply to: ", fp.title) as activity_title,
                         fr.created_at as activity_date,
                         u.name as user_name
                         FROM forum_replies fr
                         JOIN forum_posts fp ON fr.post_id = fp.id
                         JOIN users u ON fr.user_id = u.id
                         WHERE fp.forum_id = :forum_id
                         ORDER BY activity_date DESC
                         LIMIT :limit');
        $this->db->bind(':forum_id', $forumId);
        $this->db->bind(':limit', $limit);
        return $this->db->resultSet();
    }
}
?>
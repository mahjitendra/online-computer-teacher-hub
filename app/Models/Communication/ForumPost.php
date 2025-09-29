<?php
class ForumPost extends BaseModel {
    protected $table = 'forum_posts';
    protected $fillable = ['forum_id', 'user_id', 'title', 'content', 'is_pinned', 'is_locked', 'view_count'];

    public function createPost($data){
        $data['view_count'] = 0;
        $data['is_pinned'] = $data['is_pinned'] ?? 0;
        $data['is_locked'] = $data['is_locked'] ?? 0;
        return $this->create($data);
    }

    public function getPostsByForum($forumId, $page = 1, $limit = 20){
        $offset = ($page - 1) * $limit;
        
        $this->db->query('SELECT fp.*, u.name as author_name, u.email as author_email,
                         COUNT(fr.id) as reply_count,
                         MAX(COALESCE(fr.created_at, fp.created_at)) as last_activity
                         FROM forum_posts fp
                         JOIN users u ON fp.user_id = u.id
                         LEFT JOIN forum_replies fr ON fp.id = fr.post_id
                         WHERE fp.forum_id = :forum_id
                         GROUP BY fp.id
                         ORDER BY fp.is_pinned DESC, last_activity DESC
                         LIMIT :limit OFFSET :offset');
        $this->db->bind(':forum_id', $forumId);
        $this->db->bind(':limit', $limit);
        $this->db->bind(':offset', $offset);
        return $this->db->resultSet();
    }

    public function getPostById($postId){
        $this->db->query('SELECT fp.*, u.name as author_name, u.email as author_email, f.name as forum_name
                         FROM forum_posts fp
                         JOIN users u ON fp.user_id = u.id
                         JOIN forums f ON fp.forum_id = f.id
                         WHERE fp.id = :post_id');
        $this->db->bind(':post_id', $postId);
        return $this->db->single();
    }

    public function incrementViewCount($postId){
        $this->db->query('UPDATE forum_posts SET view_count = view_count + 1 WHERE id = :post_id');
        $this->db->bind(':post_id', $postId);
        return $this->db->execute();
    }

    public function getPostsByUser($userId, $limit = 20){
        $this->db->query('SELECT fp.*, f.name as forum_name,
                         COUNT(fr.id) as reply_count
                         FROM forum_posts fp
                         JOIN forums f ON fp.forum_id = f.id
                         LEFT JOIN forum_replies fr ON fp.id = fr.post_id
                         WHERE fp.user_id = :user_id
                         GROUP BY fp.id
                         ORDER BY fp.created_at DESC
                         LIMIT :limit');
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':limit', $limit);
        return $this->db->resultSet();
    }

    public function searchPosts($searchTerm, $forumId = null, $limit = 50){
        $whereClause = '(fp.title LIKE :search OR fp.content LIKE :search)';
        $params = [':search' => '%' . $searchTerm . '%'];
        
        if($forumId){
            $whereClause .= ' AND fp.forum_id = :forum_id';
            $params[':forum_id'] = $forumId;
        }
        
        $this->db->query("SELECT fp.*, u.name as author_name, f.name as forum_name,
                         COUNT(fr.id) as reply_count
                         FROM forum_posts fp
                         JOIN users u ON fp.user_id = u.id
                         JOIN forums f ON fp.forum_id = f.id
                         LEFT JOIN forum_replies fr ON fp.id = fr.post_id
                         WHERE {$whereClause}
                         GROUP BY fp.id
                         ORDER BY fp.created_at DESC
                         LIMIT :limit");
        
        foreach($params as $key => $value){
            $this->db->bind($key, $value);
        }
        $this->db->bind(':limit', $limit);
        
        return $this->db->resultSet();
    }

    public function getPopularPosts($limit = 10, $period = 'week'){
        $dateCondition = '';
        switch($period){
            case 'day':
                $dateCondition = 'WHERE fp.created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)';
                break;
            case 'week':
                $dateCondition = 'WHERE fp.created_at >= DATE_SUB(NOW(), INTERVAL 1 WEEK)';
                break;
            case 'month':
                $dateCondition = 'WHERE fp.created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)';
                break;
        }
        
        $this->db->query("SELECT fp.*, u.name as author_name, f.name as forum_name,
                         COUNT(fr.id) as reply_count,
                         (fp.view_count + COUNT(fr.id) * 2) as popularity_score
                         FROM forum_posts fp
                         JOIN users u ON fp.user_id = u.id
                         JOIN forums f ON fp.forum_id = f.id
                         LEFT JOIN forum_replies fr ON fp.id = fr.post_id
                         {$dateCondition}
                         GROUP BY fp.id
                         ORDER BY popularity_score DESC
                         LIMIT :limit");
        $this->db->bind(':limit', $limit);
        return $this->db->resultSet();
    }

    public function pinPost($postId){
        return $this->update($postId, ['is_pinned' => 1]);
    }

    public function unpinPost($postId){
        return $this->update($postId, ['is_pinned' => 0]);
    }

    public function lockPost($postId){
        return $this->update($postId, ['is_locked' => 1]);
    }

    public function unlockPost($postId){
        return $this->update($postId, ['is_locked' => 0]);
    }

    public function getRecentPosts($limit = 10){
        $this->db->query('SELECT fp.*, u.name as author_name, f.name as forum_name
                         FROM forum_posts fp
                         JOIN users u ON fp.user_id = u.id
                         JOIN forums f ON fp.forum_id = f.id
                         ORDER BY fp.created_at DESC
                         LIMIT :limit');
        $this->db->bind(':limit', $limit);
        return $this->db->resultSet();
    }

    public function getPostStats($postId){
        $this->db->query('SELECT 
                         fp.view_count,
                         COUNT(fr.id) as reply_count,
                         COUNT(DISTINCT fr.user_id) as unique_repliers,
                         MIN(fr.created_at) as first_reply,
                         MAX(fr.created_at) as last_reply
                         FROM forum_posts fp
                         LEFT JOIN forum_replies fr ON fp.id = fr.post_id
                         WHERE fp.id = :post_id
                         GROUP BY fp.id');
        $this->db->bind(':post_id', $postId);
        return $this->db->single();
    }

    public function getReplies($postId, $page = 1, $limit = 20){
        $offset = ($page - 1) * $limit;
        
        $this->db->query('SELECT fr.*, u.name as author_name, u.email as author_email
                         FROM forum_replies fr
                         JOIN users u ON fr.user_id = u.id
                         WHERE fr.post_id = :post_id
                         ORDER BY fr.created_at ASC
                         LIMIT :limit OFFSET :offset');
        $this->db->bind(':post_id', $postId);
        $this->db->bind(':limit', $limit);
        $this->db->bind(':offset', $offset);
        return $this->db->resultSet();
    }

    public function createReply($data){
        $this->db->query('INSERT INTO forum_replies (post_id, user_id, content) VALUES (:post_id, :user_id, :content)');
        $this->db->bind(':post_id', $data['post_id']);
        $this->db->bind(':user_id', $data['user_id']);
        $this->db->bind(':content', $data['content']);
        return $this->db->execute();
    }
}
?>
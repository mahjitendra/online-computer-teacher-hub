<?php
class Discussion extends BaseModel {
    protected $table = 'discussions';
    protected $fillable = ['course_id', 'user_id', 'title', 'content', 'discussion_type', 'is_resolved'];

    public function createDiscussion($data){
        $data['discussion_type'] = $data['discussion_type'] ?? 'general';
        $data['is_resolved'] = 0;
        return $this->create($data);
    }

    public function getDiscussionsByCourse($courseId, $limit = 50){
        $this->db->query('SELECT d.*, u.name as author_name, u.email as author_email,
                         COUNT(dc.id) as comment_count
                         FROM discussions d
                         JOIN users u ON d.user_id = u.id
                         LEFT JOIN discussion_comments dc ON d.id = dc.discussion_id
                         WHERE d.course_id = :course_id
                         GROUP BY d.id
                         ORDER BY d.created_at DESC
                         LIMIT :limit');
        $this->db->bind(':course_id', $courseId);
        $this->db->bind(':limit', $limit);
        return $this->db->resultSet();
    }

    public function getDiscussionById($discussionId){
        $this->db->query('SELECT d.*, u.name as author_name, u.email as author_email, c.title as course_title
                         FROM discussions d
                         JOIN users u ON d.user_id = u.id
                         JOIN courses c ON d.course_id = c.id
                         WHERE d.id = :discussion_id');
        $this->db->bind(':discussion_id', $discussionId);
        return $this->db->single();
    }

    public function getComments($discussionId, $limit = 50){
        $this->db->query('SELECT dc.*, u.name as author_name, u.email as author_email
                         FROM discussion_comments dc
                         JOIN users u ON dc.user_id = u.id
                         WHERE dc.discussion_id = :discussion_id
                         ORDER BY dc.created_at ASC
                         LIMIT :limit');
        $this->db->bind(':discussion_id', $discussionId);
        $this->db->bind(':limit', $limit);
        return $this->db->resultSet();
    }

    public function addComment($data){
        $this->db->query('INSERT INTO discussion_comments (discussion_id, user_id, content) VALUES (:discussion_id, :user_id, :content)');
        $this->db->bind(':discussion_id', $data['discussion_id']);
        $this->db->bind(':user_id', $data['user_id']);
        $this->db->bind(':content', $data['content']);
        return $this->db->execute();
    }

    public function markAsResolved($discussionId){
        return $this->update($discussionId, ['is_resolved' => 1, 'resolved_at' => date('Y-m-d H:i:s')]);
    }

    public function markAsUnresolved($discussionId){
        return $this->update($discussionId, ['is_resolved' => 0, 'resolved_at' => null]);
    }

    public function getDiscussionsByUser($userId, $limit = 20){
        $this->db->query('SELECT d.*, c.title as course_title,
                         COUNT(dc.id) as comment_count
                         FROM discussions d
                         JOIN courses c ON d.course_id = c.id
                         LEFT JOIN discussion_comments dc ON d.id = dc.discussion_id
                         WHERE d.user_id = :user_id
                         GROUP BY d.id
                         ORDER BY d.created_at DESC
                         LIMIT :limit');
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':limit', $limit);
        return $this->db->resultSet();
    }

    public function searchDiscussions($searchTerm, $courseId = null, $limit = 50){
        $whereClause = '(d.title LIKE :search OR d.content LIKE :search)';
        $params = [':search' => '%' . $searchTerm . '%'];
        
        if($courseId){
            $whereClause .= ' AND d.course_id = :course_id';
            $params[':course_id'] = $courseId;
        }
        
        $this->db->query("SELECT d.*, u.name as author_name, c.title as course_title,
                         COUNT(dc.id) as comment_count
                         FROM discussions d
                         JOIN users u ON d.user_id = u.id
                         JOIN courses c ON d.course_id = c.id
                         LEFT JOIN discussion_comments dc ON d.id = dc.discussion_id
                         WHERE {$whereClause}
                         GROUP BY d.id
                         ORDER BY d.created_at DESC
                         LIMIT :limit");
        
        foreach($params as $key => $value){
            $this->db->bind($key, $value);
        }
        $this->db->bind(':limit', $limit);
        
        return $this->db->resultSet();
    }

    public function getDiscussionsByType($type, $courseId = null, $limit = 50){
        $whereClause = 'd.discussion_type = :type';
        $params = [':type' => $type];
        
        if($courseId){
            $whereClause .= ' AND d.course_id = :course_id';
            $params[':course_id'] = $courseId;
        }
        
        $this->db->query("SELECT d.*, u.name as author_name, c.title as course_title,
                         COUNT(dc.id) as comment_count
                         FROM discussions d
                         JOIN users u ON d.user_id = u.id
                         JOIN courses c ON d.course_id = c.id
                         LEFT JOIN discussion_comments dc ON d.id = dc.discussion_id
                         WHERE {$whereClause}
                         GROUP BY d.id
                         ORDER BY d.created_at DESC
                         LIMIT :limit");
        
        foreach($params as $key => $value){
            $this->db->bind($key, $value);
        }
        $this->db->bind(':limit', $limit);
        
        return $this->db->resultSet();
    }

    public function getUnresolvedDiscussions($courseId = null, $limit = 50){
        $whereClause = 'd.is_resolved = 0';
        $params = [];
        
        if($courseId){
            $whereClause .= ' AND d.course_id = :course_id';
            $params[':course_id'] = $courseId;
        }
        
        $this->db->query("SELECT d.*, u.name as author_name, c.title as course_title,
                         COUNT(dc.id) as comment_count
                         FROM discussions d
                         JOIN users u ON d.user_id = u.id
                         JOIN courses c ON d.course_id = c.id
                         LEFT JOIN discussion_comments dc ON d.id = dc.discussion_id
                         WHERE {$whereClause}
                         GROUP BY d.id
                         ORDER BY d.created_at DESC
                         LIMIT :limit");
        
        foreach($params as $key => $value){
            $this->db->bind($key, $value);
        }
        $this->db->bind(':limit', $limit);
        
        return $this->db->resultSet();
    }

    public function getDiscussionStats($courseId = null){
        $whereClause = $courseId ? 'WHERE d.course_id = :course_id' : '';
        
        $this->db->query("SELECT 
                         COUNT(*) as total_discussions,
                         SUM(CASE WHEN d.is_resolved = 1 THEN 1 ELSE 0 END) as resolved_discussions,
                         SUM(CASE WHEN d.discussion_type = 'question' THEN 1 ELSE 0 END) as questions,
                         SUM(CASE WHEN d.discussion_type = 'general' THEN 1 ELSE 0 END) as general_discussions,
                         COUNT(DISTINCT d.user_id) as unique_participants
                         FROM discussions d {$whereClause}");
        
        if($courseId){
            $this->db->bind(':course_id', $courseId);
        }
        
        return $this->db->single();
    }

    public function getActiveDiscussions($limit = 10){
        $this->db->query('SELECT d.*, u.name as author_name, c.title as course_title,
                         COUNT(dc.id) as comment_count,
                         MAX(COALESCE(dc.created_at, d.created_at)) as last_activity
                         FROM discussions d
                         JOIN users u ON d.user_id = u.id
                         JOIN courses c ON d.course_id = c.id
                         LEFT JOIN discussion_comments dc ON d.id = dc.discussion_id
                         WHERE d.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                         GROUP BY d.id
                         ORDER BY last_activity DESC
                         LIMIT :limit');
        $this->db->bind(':limit', $limit);
        return $this->db->resultSet();
    }
}
?>
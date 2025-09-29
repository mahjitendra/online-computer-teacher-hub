<?php
class QuestionBank extends BaseModel {
    protected $table = 'question_bank';
    protected $fillable = ['category_id', 'subject', 'difficulty_level', 'question_text', 'question_type', 'correct_answer', 'options', 'explanation'];

    public function getQuestionsByCategory($categoryId, $limit = null){
        $query = 'SELECT * FROM question_bank WHERE category_id = :category_id ORDER BY created_at DESC';
        if($limit){
            $query .= ' LIMIT :limit';
        }
        
        $this->db->query($query);
        $this->db->bind(':category_id', $categoryId);
        if($limit){
            $this->db->bind(':limit', $limit);
        }
        
        return $this->db->resultSet();
    }

    public function getQuestionsByDifficulty($difficulty, $limit = null){
        $query = 'SELECT * FROM question_bank WHERE difficulty_level = :difficulty ORDER BY RAND()';
        if($limit){
            $query .= ' LIMIT :limit';
        }
        
        $this->db->query($query);
        $this->db->bind(':difficulty', $difficulty);
        if($limit){
            $this->db->bind(':limit', $limit);
        }
        
        return $this->db->resultSet();
    }

    public function getRandomQuestions($count, $categoryId = null, $difficulty = null){
        $conditions = [];
        $params = [];
        
        if($categoryId){
            $conditions[] = 'category_id = :category_id';
            $params[':category_id'] = $categoryId;
        }
        
        if($difficulty){
            $conditions[] = 'difficulty_level = :difficulty';
            $params[':difficulty'] = $difficulty;
        }
        
        $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
        
        $this->db->query("SELECT * FROM question_bank {$whereClause} ORDER BY RAND() LIMIT :count");
        
        foreach($params as $key => $value){
            $this->db->bind($key, $value);
        }
        $this->db->bind(':count', $count);
        
        return $this->db->resultSet();
    }

    public function importQuestions($questions){
        $imported = 0;
        
        foreach($questions as $question){
            if($this->create($question)){
                $imported++;
            }
        }
        
        return $imported;
    }

    public function searchQuestions($searchTerm, $categoryId = null){
        $conditions = ['(question_text LIKE :search OR explanation LIKE :search)'];
        $params = [':search' => '%' . $searchTerm . '%'];
        
        if($categoryId){
            $conditions[] = 'category_id = :category_id';
            $params[':category_id'] = $categoryId;
        }
        
        $whereClause = 'WHERE ' . implode(' AND ', $conditions);
        
        $this->db->query("SELECT * FROM question_bank {$whereClause} ORDER BY created_at DESC");
        
        foreach($params as $key => $value){
            $this->db->bind($key, $value);
        }
        
        return $this->db->resultSet();
    }
}
?>
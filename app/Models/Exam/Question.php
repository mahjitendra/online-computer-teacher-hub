<?php
class Question extends BaseModel {
    protected $table = 'questions';
    protected $fillable = ['exam_id', 'question_text', 'question_type', 'points', 'order_index'];

    public function getQuestionsByExam($examId){
        $this->db->query('SELECT * FROM questions WHERE exam_id = :exam_id ORDER BY order_index ASC');
        $this->db->bind(':exam_id', $examId);
        $questions = $this->db->resultSet();

        // Get options for each question
        foreach($questions as &$question){
            if($question->question_type == 'multiple_choice'){
                $this->db->query('SELECT * FROM question_options WHERE question_id = :question_id ORDER BY id ASC');
                $this->db->bind(':question_id', $question->id);
                $question->options = $this->db->resultSet();
            }
        }

        return $questions;
    }

    public function createQuestion($data){
        $questionId = $this->create($data);
        
        if($questionId && isset($data['options']) && !empty($data['options'])){
            foreach($data['options'] as $option){
                $this->db->query('INSERT INTO question_options (question_id, option_text, is_correct) VALUES (:question_id, :option_text, :is_correct)');
                $this->db->bind(':question_id', $questionId);
                $this->db->bind(':option_text', $option['text']);
                $this->db->bind(':is_correct', $option['is_correct']);
                $this->db->execute();
            }
        }

        return $questionId;
    }

    public function updateQuestion($id, $data){
        $result = $this->update($id, $data);
        
        if($result && isset($data['options'])){
            // Delete existing options
            $this->db->query('DELETE FROM question_options WHERE question_id = :question_id');
            $this->db->bind(':question_id', $id);
            $this->db->execute();
            
            // Add new options
            foreach($data['options'] as $option){
                $this->db->query('INSERT INTO question_options (question_id, option_text, is_correct) VALUES (:question_id, :option_text, :is_correct)');
                $this->db->bind(':question_id', $id);
                $this->db->bind(':option_text', $option['text']);
                $this->db->bind(':is_correct', $option['is_correct']);
                $this->db->execute();
            }
        }

        return $result;
    }

    public function getCorrectAnswer($questionId){
        $this->db->query('SELECT * FROM question_options WHERE question_id = :question_id AND is_correct = 1');
        $this->db->bind(':question_id', $questionId);
        return $this->db->single();
    }

    public function validateAnswer($questionId, $answer){
        $question = $this->find($questionId);
        
        switch($question->question_type){
            case 'multiple_choice':
                $correctOption = $this->getCorrectAnswer($questionId);
                return $correctOption && $correctOption->id == $answer;
                
            case 'true_false':
                $correctOption = $this->getCorrectAnswer($questionId);
                return $correctOption && $correctOption->option_text == $answer;
                
            case 'short_answer':
                // For short answers, you might want to implement fuzzy matching
                $correctOption = $this->getCorrectAnswer($questionId);
                return $correctOption && strtolower(trim($correctOption->option_text)) == strtolower(trim($answer));
                
            default:
                return false;
        }
    }
}
?>
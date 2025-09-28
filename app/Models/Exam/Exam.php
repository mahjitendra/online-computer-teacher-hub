<?php
class Exam {
    private $db;

    public function __construct(){
        $this->db = new Database;
    }

    public function createExam($data){
        $this->db->query('INSERT INTO exams (course_id, title, description, duration) VALUES (:course_id, :title, :description, :duration)');
        $this->db->bind(':course_id', $data['course_id']);
        $this->db->bind(':title', $data['title']);
        $this->db->bind(':description', $data['description']);
        $this->db->bind(':duration', $data['duration']);

        if($this->db->execute()){
            return $this->db->lastInsertId(); // Return the new exam's ID
        } else {
            return false;
        }
    }

    public function getExamsByCourse($course_id){
        $this->db->query('SELECT * FROM exams WHERE course_id = :course_id');
        $this->db->bind(':course_id', $course_id);
        return $this->db->resultSet();
    }

    public function getExamById($id){
        $this->db->query('SELECT * FROM exams WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function addQuestionToExam($data){
        $this->db->query('INSERT INTO questions (exam_id, question_text, question_type) VALUES (:exam_id, :question_text, :question_type)');
        $this->db->bind(':exam_id', $data['exam_id']);
        $this->db->bind(':question_text', $data['question_text']);
        $this->db->bind(':question_type', $data['question_type']);

        if($this->db->execute()){
            $question_id = $this->db->lastInsertId();
            // Now add options if it's a multiple choice question
            if($data['question_type'] == 'multiple_choice' && !empty($data['options'])){
                foreach($data['options'] as $option){
                    $this->db->query('INSERT INTO question_options (question_id, option_text, is_correct) VALUES (:question_id, :option_text, :is_correct)');
                    $this->db->bind(':question_id', $question_id);
                    $this->db->bind(':option_text', $option['text']);
                    $this->db->bind(':is_correct', $option['is_correct']);
                    $this->db->execute();
                }
            }
            return true;
        } else {
            return false;
        }
    }

    public function getQuestionsForExam($exam_id){
        $this->db->query('SELECT * FROM questions WHERE exam_id = :exam_id');
        $this->db->bind(':exam_id', $exam_id);
        $questions = $this->db->resultSet();

        // Get options for each question
        foreach($questions as &$question){
            if($question->question_type == 'multiple_choice'){
                $this->db->query('SELECT * FROM question_options WHERE question_id = :question_id');
                $this->db->bind(':question_id', $question->id);
                $question->options = $this->db->resultSet();
            }
        }
        return $questions;
    }
}
?>
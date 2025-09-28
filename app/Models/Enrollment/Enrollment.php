<?php
class Enrollment {
    private $db;

    public function __construct(){
        $this->db = new Database;
    }

    public function isStudentEnrolled($student_id, $course_id){
        $this->db->query('SELECT * FROM enrollments WHERE student_id = :student_id AND course_id = :course_id');
        $this->db->bind(':student_id', $student_id);
        $this->db->bind(':course_id', $course_id);
        $this->db->single();

        if($this->db->rowCount() > 0){
            return true;
        } else {
            return false;
        }
    }

    public function enrollStudent($data){
        $this->db->query('INSERT INTO enrollments (student_id, course_id) VALUES (:student_id, :course_id)');
        // Bind values
        $this->db->bind(':student_id', $data['student_id']);
        $this->db->bind(':course_id', $data['course_id']);

        // Execute
        if($this->db->execute()){
            return true;
        } else {
            return false;
        }
    }
}
?>
<?php
class CreateQuestionsTable {
    public function up() {
        $db = new Database;
        $sql = "CREATE TABLE IF NOT EXISTS questions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            exam_id INT NOT NULL,
            question_text TEXT NOT NULL,
            question_type ENUM('multiple_choice', 'true_false', 'short_answer') NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (exam_id) REFERENCES exams(id) ON DELETE CASCADE
        )";
        $db->query($sql);
        $db->execute();
    }

    public function down() {
        $db = new Database;
        $sql = "DROP TABLE IF EXISTS questions";
        $db->query($sql);
        $db->execute();
    }
}
?>
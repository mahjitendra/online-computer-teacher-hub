<?php
class CreateExamsTable {
    public function up() {
        $db = new Database;
        $sql = "CREATE TABLE IF NOT EXISTS exams (
            id INT AUTO_INCREMENT PRIMARY KEY,
            course_id INT NOT NULL,
            title VARCHAR(255) NOT NULL,
            description TEXT,
            duration INT, -- Duration in minutes
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
        )";
        $db->query($sql);
        $db->execute();
    }

    public function down() {
        $db = new Database;
        $sql = "DROP TABLE IF EXISTS exams";
        $db->query($sql);
        $db->execute();
    }
}
?>
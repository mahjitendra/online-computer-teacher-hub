<?php
class CreateCourseCategoriesTable {
    public function up() {
        $db = new Database;
        $sql = "CREATE TABLE IF NOT EXISTS course_categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        $db->query($sql);
        $db->execute();
    }

    public function down() {
        $db = new Database;
        $sql = "DROP TABLE IF EXISTS course_categories";
        $db->query($sql);
        $db->execute();
    }
}
?>